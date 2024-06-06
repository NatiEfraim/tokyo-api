<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDistributionRequest extends FormRequest
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
            'quartermaster_comment' => 'nullable|string',
            'status' => 'nullable|integer|between:1,4',
            'quantity' => 'nullable|integer|min:0',
            'inventory_id' => 'nullable|exists:inventories,id,is_deleted,0',
            'department_id' => 'nullable|exists:departments,id,is_deleted,0',
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
            'comment.string' => 'שדה ההערה הוא אינו בפורמט תקין.',
            'status.between' => 'שדה הסטטוס אינו תקין.',
            'quantity.integer' => 'שדה הכמות אינו בפורמט תקין.',
            'quantity.min' => 'ערך שדה כמות אינו תקין.',
            'inventory_id.exists' => 'שדה מזהה המלאי אינו קיים במערכת.',
            'department_id.exists' => 'שדה מזהה המחלקה אינו קיים במערכת.',
        ];
    }




}