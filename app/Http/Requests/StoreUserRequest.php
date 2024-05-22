<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            //
            'name' => 'required|string|min:2|max:255',
            'personal_number' => [
                'required',
                'regex:/^\d{7}$/', // Ensure the value is exactly 7 digits
                'unique:users,personal_number,NULL,id,is_deleted,0', // Custom unique rule
            ],
            'phone' => 'required|string|unique:users|regex:/^05\d{8}$/',
            'employee_type' => 'required|exists:employee_types,id,is_deleted,0',
        ];
    }



    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'name.required' => 'שם הוא שדה חובה',
            'name.string' => 'שדה שם אינו בפורמט תקין.',
            'name.min' => 'השם חייב להיות לפחות 2 תווים',
            'name.max' => 'השם יכול להכיל מקסימום 255 תווים',

            'personal_number.required' => 'מספר אישי הוא שדה חובה',

            'personal_number.regex' => 'מספר אישי חייב להיות בפורמט תקין',
            'personal_number.unique' => 'מספר אישי קיים במערכת.',



            'phone.required' => 'מספר הטלפון הוא שדה חובה',
            'phone.string' => 'מספר הטלפון אינו בפורמט תקין',
            'phone.unique' => 'מספר הטלפון כבר קיים במערכת',
            'phone.regex' => 'מספר הטלפון חייב להיות בפורמט תקין של מספר ישראלי',
            'employee_type.required' => 'סוג העובד הוא שדה חובה',
            'employee_type.exists' => 'סוג העובד אינו קיים במערכת',
        ];
    }
}
