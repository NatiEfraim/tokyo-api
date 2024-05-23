<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInventoryRequest extends FormRequest
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
            'quantity' => 'nullable|integer|min:0',
            
            'sku' => 'nullable|string|max:255',

            'type_id' => 'required|string|exists:item_types,id,is_deleted,0',


            'detailed_description' => 'nullable|string',
        ];
    }


    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */

    public function messages(): array
    {
        return [

            'quantity.integer' => 'שדה כמות חייבת להיות מספר שלם.',

            'quantity.min' => 'שדה כמות חייב להיות לפחות 0',

            'sku.max' => 'שדה מק"ט יכול להכיל עד 255 תווים',

            'type_id.required' => 'יש לבחור סוג פריט.',
            'type_id.string' => 'סוג פריט אינו בפורמט תקין',
            'type_id.exists' => 'סוג פריט אינו קיים.',

        ];
    }
}
