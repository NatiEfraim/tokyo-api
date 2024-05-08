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
            // 'comment' => 'required|string',

            // 'status' => 'nullable|integer|between:0,2',

            // 'quantity' => 'required|integer|min:0',

            // 'inventory_id' => 'required|exists:inventories,id,is_deleted,0',

            'department_id' => 'required|exists:departments,id,is_deleted,0',

            'created_for' => 'required|exists:users,id,is_deleted,0',

            'items' => 'required|array',
            'items.*.item_type' => 'required|string|exists:inventories,item_type,is_deleted,0',
            'items.*.quantity' => 'required|integer|min:1', // Adjust min value as needed
            'items.*.comment' => 'nullable|string|max:255', // Nullable string with max length 255

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

            'comment.required' => 'שדה ההערה הוא חובה.',

            'status.between' => 'שדה הסטטוס אינו תקין.',

            'quantity.required' => 'שדה הכמות הוא חובה.',
            'quantity.min' => 'ערך שדה כמות אינו תקין.',

            'inventory_id.required' => 'שדה מזהה המלאי הוא חובה.',
            'department_id.required' => 'שדה מזהה המחלקה הוא חובה.',
            'inventory_id.exists' => 'שדה מזהה המלאי אינו קיים במערכת.',
            'department_id.exists' => 'שדה מזהה המחלקה אינו קיים במערכת.',

            'created_for.required' => 'יש לשלוח משתמש קיים במערכת שעבורו נופק הבקשה.',
            'created_for.exists' => 'משתמש שעבורו נופק הבקשה אינו קיים במערכת.',

            'items.required' => 'יש לציין פריטים להפצה.',
            'items.array' => 'הפריטים חייבים להיות בפורמט של מערך.',

            'items.*.item_type.required' => 'שדה סוג הפריט הוא שדה חובה.',
            'items.*.item_type.string' => 'סוג הפריט חייב להיות מחרוזת.',
            'items.*.item_type.exists' => 'הפריט :value לא קיים או נמחק.',

            'items.*.quantity.required' => 'שדה כמות הפריט הוא שדה חובה.',
            'items.*.quantity.integer' => 'כמות הפריט חייבת להיות מספר שלם.',
            'items.*.quantity.min' => 'כמות הפריט חייבת להיות לא נגיטיבית.',

            'items.*.comment.string' => 'הערה עבור הפריט חייבת להיות מחרוזת.',
            'items.*.comment.max' => 'הערה עבור הפריט חייבת להיות לא יותר מ-255 תווים.',

        ];
    }
}
