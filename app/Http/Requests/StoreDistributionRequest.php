<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDistributionRequest extends FormRequest
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
            ],

            'phone' => 'required|string|unique:users|regex:/^05\d{8}$/',

            'employee_type' => 'required|exists:employee_types,id,is_deleted,0',

            'user_comment' => 'nullable|string|min:2|max:255',

            'department_id' => 'required|exists:departments,id,is_deleted,0',


            'items' => 'required|array',

            'items.*.type_id' => 'required|exists:item_types,id,is_deleted,0',

            'items.*.quantity' => 'required|integer|min:1', 
            'items.*.comment' => 'nullable|string|max:255', 

        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages()
    {
        return [

            //? clients comments
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


            //? items comments
            // 'general_comment.required' => 'שדה ההערה הוא חובה.',

            'user_comment.string' => 'שדה הערה כללית אינה תקינה.',
            'user_comment.min' => 'שדה הערה כללית אינה תקינה.',
            'user_comment.max' => 'שדה הערה כללית אינה תקינה.',

            'status.between' => 'שדה הסטטוס אינו תקין.',


            
            'department_id.required' => 'שדה מזהה המחלקה הוא חובה.',
            'department_id.exists' => 'שדה מזהה המחלקה אינו קיים במערכת.',

            'created_for.required' => 'יש לשלוח משתמש קיים במערכת שעבורו נופק הבקשה.',
            'created_for.exists' => 'משתמש שעבורו נופק הבקשה אינו קיים במערכת.',

            'items.required' => 'יש לציין פריטים להפצה.',
            'items.array' => 'הפריטים חייבים להיות בפורמט של מערך.',


            'items.*.type_id.required' => 'שדה סוג הפריט הוא שדה חובה.',
            'items.*.type_id.exists' => 'הפריט :value לא קיים או נמחק.',

            'items.*.quantity.required' => 'שדה כמות הפריט הוא שדה חובה.',
            'items.*.quantity.integer' => 'כמות הפריט חייבת להיות מספר שלם.',
            'items.*.quantity.min' => 'כמות הפריט חייבת להיות לא נגיטיבית.',

            'items.*.comment.string' => 'הערה עבור הפריט חייבת להיות מחרוזת.',
            'items.*.comment.max' => 'הערה עבור הפריט חייבת להיות לא יותר מ-255 תווים.',

        ];
    }
}