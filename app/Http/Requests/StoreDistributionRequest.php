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
            'comment' => 'required|string',
            'status' => 'nullable|integer|between:0,2',
            'quantity' => 'required|integer',
            'inventory_id' => 'required|exists:inventories,id,is_deleted,0',
            'department_id' => 'required|exists:departments,id,is_deleted,0',
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
            // 'status.required' => 'שדה הסטטוס הוא חובה.',
            'status.between' => 'שדה הסטטוס אינו תקין.',
            'quantity.required' => 'שדה הכמות הוא חובה.',
            'inventory_id.required' => 'שדה מזהה המלאי הוא חובה.',
            'department_id.required' => 'שדה מזהה המחלקה הוא חובה.',
        ];
    }
}
