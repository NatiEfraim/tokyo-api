<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CanceledDistributionRequest extends FormRequest
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
            'order_number' => 'required|integer|exists:distributions,order_number,is_deleted,0',
            'inventory_items' => 'required|array',
            'inventory_items.*.id' => 'required|integer|exists:distributions,id,is_deleted,0',
            'inventory_items.*.admin_comment' => 'required|string',
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


            'order_number.required' => 'חובה לשלוח מספר הזמנה.',
            'order_number.integer' => 'אחת מהשדות שנשלחו אינם תקינים.',
            'order_number.exists' => 'מספר הזמנה אינה קיימת במערכת.',

            'inventory_items.required' => 'חובה לשלוח פרטי מלאי.',
            'inventory_items.array' => 'שדה פרטי המלאי חייב להיות מערך.',
            'inventory_items.*.id.required' => 'חובה לשלוח מזהה פריט מלאי.',
            'inventory_items.*.id.integer' => 'מזהה פריט מלאי חייב להיות מספר שלם.',
            'inventory_items.*.id.exists' => 'מזהה פריט מלאי אינו תקין.',
            'inventory_items.*.admin_comment.required' => 'חובה לשלוח תגובת מנהל.',
            'inventory_items.*.admin_comment.string' => 'תגובת מנהל חייבת להיות מחרוזת.',
            

        ];
    }



}
