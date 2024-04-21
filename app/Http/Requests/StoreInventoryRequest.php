<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInventoryRequest extends FormRequest
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
            'quantity' => 'required|string|min:0',
            'reserved' => 'nullable|string|min:0',
            'sku' => 'required|string|max:255',
            'item_type' => 'required|string|max:255',
            'detailed_description' => 'required|string',
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

            'quantity.required' => 'יש להזין כמות',
            'quantity.string' => 'שדה כמות אינו תקין.',
            'quantity.min' => 'שדה כמות חייב להיות לפחות 0',

            'reserved.min' => 'שדה שמור חייב להיות לפחות 0.',
            'reserved.string' => 'שדה שמור אינו תקין.',

            'sku.required' => 'יש להזין שדה מק"ט',
            'sku.max' => 'אורך שדה מק"ט חייב להכיל לכל היותר 255 תווים',

            'item_type.required' => 'יש להזין סוג פריט',
            'item_type.max' => 'אורך שדה סוג פריט חייב להיות לכל היותר 255 תווים',

            'detailed_description.required' => 'יש להזין תיאור מפורט',
        ];
    }
}
