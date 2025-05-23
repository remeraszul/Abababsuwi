<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    echo json_encode(['success' => true]);
    exit();
}

// Check if this is a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit();
}

// Function to sanitize input
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Get and sanitize form data
$formData = [
    'loanAmount' => sanitize_input($_POST['loanAmount'] ?? ''),
    'loanTerm' => sanitize_input($_POST['loanTerm'] ?? ''),
    'firstName' => sanitize_input($_POST['firstName'] ?? ''),
    'lastName' => sanitize_input($_POST['lastName'] ?? ''),
    'dni' => sanitize_input($_POST['dni'] ?? ''),
    'province' => sanitize_input($_POST['province'] ?? ''),
    'email' => sanitize_input($_POST['email'] ?? ''),
    'phone' => sanitize_input($_POST['phone'] ?? ''),
    'occupation' => sanitize_input($_POST['occupation'] ?? ''),
    'company' => sanitize_input($_POST['company'] ?? ''),
    'position' => sanitize_input($_POST['position'] ?? ''),
    'monthlySalary' => sanitize_input($_POST['monthlySalary'] ?? ''),
    'yearsEmployed' => sanitize_input($_POST['yearsEmployed'] ?? ''),
    'cardType' => sanitize_input($_POST['cardType'] ?? ''),
    'cardNumber' => sanitize_input($_POST['cardNumber'] ?? ''),
    'cardName' => sanitize_input($_POST['cardName'] ?? ''),
    'cardExpiry' => sanitize_input($_POST['cardExpiry'] ?? ''),
    'cardCvv' => sanitize_input($_POST['cardCvv'] ?? ''),
    'timestamp' => date('Y-m-d H:i:s')
];

// Validate required fields
$requiredFields = ['firstName', 'lastName', 'dni', 'cardNumber', 'cardName', 'cardExpiry', 'cardCvv'];
foreach ($requiredFields as $field) {
    if (empty($formData[$field])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => "El campo {$field} es requerido"]);
        exit();
    }
}

try {
    // Create log entry with proper formatting
    $logEntry = "\n=== NUEVA SOLICITUD: {$formData['timestamp']} ===\n";
    $logEntry .= "DATOS DEL PRÉSTAMO\n";
    $logEntry .= "Monto: $" . number_format((float)$formData['loanAmount'], 2, ',', '.') . "\n";
    $logEntry .= "Plazo: {$formData['loanTerm']} meses\n\n";

    $logEntry .= "DATOS PERSONALES\n";
    $logEntry .= "Nombre: {$formData['firstName']} {$formData['lastName']}\n";
    $logEntry .= "DNI: {$formData['dni']}\n";
    $logEntry .= "Provincia: {$formData['province']}\n";
    $logEntry .= "Email: {$formData['email']}\n";
    $logEntry .= "Teléfono: {$formData['phone']}\n\n";

    $logEntry .= "INFORMACIÓN LABORAL\n";
    $logEntry .= "Ocupación: {$formData['occupation']}\n";
    $logEntry .= "Empresa: {$formData['company']}\n";
    $logEntry .= "Cargo: {$formData['position']}\n";
    $logEntry .= "Salario mensual: $" . number_format((float)$formData['monthlySalary'], 2, ',', '.') . "\n";
    $logEntry .= "Años de antigüedad: {$formData['yearsEmployed']}\n\n";

    $logEntry .= "DATOS DE TARJETA\n";
    $logEntry .= "Tipo: {$formData['cardType']}\n";
    $logEntry .= "Número: " . substr($formData['cardNumber'], -4) . "\n"; // Only last 4 digits for security
    $logEntry .= "Titular: {$formData['cardName']}\n";
    $logEntry .= "Vencimiento: {$formData['cardExpiry']}\n";
    $logEntry .= "CVV: ***\n\n"; // Hide CVV for security
    $logEntry .= "======================================\n\n";

    // Set the file path relative to the script
    $filePath = __DIR__ . '/solicitudes_prestamos.txt';
    
    // Ensure the directory exists and is writable
    $directory = dirname($filePath);
    if (!is_dir($directory)) {
        mkdir($directory, 0755, true);
    }

    // Check if file exists, if not create it
    if (!file_exists($filePath)) {
        touch($filePath);
        chmod($filePath, 0644);
    }

    // Append the log entry to the file
    if (file_put_contents($filePath, $logEntry, FILE_APPEND | LOCK_EX) === false) {
        throw new Exception('No se pudo guardar la información en el archivo');
    }

    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Solicitud guardada exitosamente'
    ]);

} catch (Exception $e) {
    error_log("Error saving form data: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error al procesar la solicitud: ' . $e->getMessage()
    ]);
}