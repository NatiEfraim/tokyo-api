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

            'inventory_items.*.type_id' => 'required|exists:item_types,id,is_deleted,0',

            'inventory_items.*.items' => 'required|array',

            'inventory_items.*.items.*.inventory_id' => 'required|exists:inventories,id,is_deleted,0',

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


            'status.required' => 'חובה לשלוח שדה סטטוס לעידכון.',
            'status.integer' => 'שדה סטטוס שנשלח אינו בפורמט תקין.',
            'status.between' => 'ערך הסטטוס שנשלח אינו תקין.',

            'admin_comment.string' => 'אחת מהשדות שנשלחו אינם תקינים.',
            'admin_comment.required' => '.',
            'admin_comment.min' => 'אחת מהשדות שנשלחו אינם תקינים.',
            'admin_comment.max' => 'אחת מהשדות שנשלחו אינם תקינים.',

            'inventory_items.array' => 'נתון שנשלח אינו תקין.',
            'inventory_items.*.inventory_id.required' => 'חובה לשלוח מזהה פריט במערך הפריטים.',
            'inventory_items.*.inventory_id.exists' => 'מזהה הפריט שנשלח במערך אינו קיים או נמחק.',
            'inventory_items.*.quantity.required' => 'חובה לשלוח כמות לכל פריט במערך.',
            'inventory_items.*.quantity.integer' => 'הכמות שנשלחה עבור פריט במערך אינה בפורמט תקין.',
            'inventory_items.*.quantity.min' => 'הכמות שנשלחה עבור פריט במערך חייבת להיות גדולה או שווה ל-0.',

            'order_number.required' => 'חובה לשלוח מספר הזמנה.',
            'order_number.integer' => 'אחת מהשדות שנשלחו אינם תקינים.',
            'order_number.exists' => 'מספר הזמנה אינה קיימת במערכת.',

        ];
    }



}
