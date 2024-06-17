<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AllocationDistributionRequest extends FormRequest
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
            
            'status' => 'required|integer|between:2,3',

            'admin_comment' => 'nullable|string|min:2|max:255',

            'inventory_items' => 'nullable|array',

            'inventory_items.*.type_id' => 'required|integer|exists:item_types,id,is_deleted,0',

            'inventory_items.*.admin_comment' => 'nullable|string|min:2|max:255',

            'inventory_items.*.items' => 'nullable|array',

            'inventory_items.*.items.*.inventory_id' => 'required|integer|exists:inventories,id,is_deleted,0',

            'inventory_items.*.items.*.quantity' => 'required|integer|min:0',

            'order_number' => 'required|integer|exists:distributions,order_number,is_deleted,0',

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

            'status.required' => 'שדה הסטטוס נדרש.',
            'status.integer' => 'שדה הסטטוס חייב להיות מספר שלם.',
            'status.between' => 'שדה הסטטוס חייב להיות בין 2 ל-3.',

            'admin_comment.string' => 'שדה תגובת המנהל חייב להיות מחרוזת.',
            'admin_comment.min' => 'שדה תגובת המנהל חייב להיות לפחות 2 תווים.',
            'admin_comment.max' => 'שדה תגובת המנהל חייב להיות עד 255 תווים.',

            'inventory_items.array' => 'שדה פריטי המלאי אינו תקין.',

            'inventory_items.*.type_id.required' => 'שדה מזהה סוג הפריט נדרש.',
            'inventory_items.*.type_id.integer' => 'שדה מזהה סוג הפריט חייב להיות מספר שלם.',
            'inventory_items.*.type_id.exists' => 'שדה מזהה סוג הפריט לא קיים או נמחק.',

            'inventory_items.*.admin_comment.string' => 'שדה תגובת המנהל בפריטי המלאי אינו תקין.',
            'inventory_items.*.admin_comment.min' => 'שדה תגובת המנהל בפריטי המלאי אינו תקין.',
            'inventory_items.*.admin_comment.max' => 'שדה תגובת המנהל בפריטי המלאי אינו תקין.',

            'inventory_items.*.items.array' => 'שדה הפריטים אינו תקין.',

            'inventory_items.*.items.*.inventory_id.required' => 'שדה מזהה המלאי נדרש.',
            'inventory_items.*.items.*.inventory_id.integer' => 'שדה מזהה המלאי חייב להיות מספר שלם.',
            'inventory_items.*.items.*.inventory_id.exists' => 'שדה מזהה המלאי לא קיים או נמחק.',

            'inventory_items.*.items.*.quantity.required' => 'שדה הכמות נדרש.',
            'inventory_items.*.items.*.quantity.integer' => 'שדה הכמות חייב להיות מספר שלם.',
            'inventory_items.*.items.*.quantity.min' => 'שדה הכמות חייב להיות לפחות 0.',

            'order_number.required' => 'שדה מספר ההזמנה נדרש.',
            'order_number.integer' => 'שדה מספר ההזמנה חייב להיות מספר שלם.',
            'order_number.exists' => 'שדה מספר ההזמנה לא קיים או נמחק.',

        ];
    }



}
