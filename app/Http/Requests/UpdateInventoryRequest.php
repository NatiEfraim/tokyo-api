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
            'item_type' => 'nullable|string|max:255',
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
            'item_type.max' => 'אורך שדה סוג הפריט חייב להיות לכל היותר 255 תווים.',
        ];
    }
}
