<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChangeStatusDistributionRequest extends FormRequest
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
            'status' => 'required|integer|between:1,4',
            'quartermaster_comment' => 'required|string|min:2|max:255',
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

            'quartermaster_comment.string' => 'אחת מהשדות שנשלחו אינם תקינים.',
            'quartermaster_comment.required' => 'אחת מהשדות שנשלחו אינם תקינים.',
            'quartermaster_comment.min' => 'אחת מהשדות שנשלחו אינם תקינים.',
            'quartermaster_comment.max' => 'אחת מהשדות שנשלחו אינם תקינים.',

            'order_number.required' => 'חובה לשלוח מספר הזמנה.',
            'order_number.integer' => 'אחת מהשדות שנשלחו אינם תקינים.',
            'order_number.exists' => 'מספר הזמנה אינה קיימת במערכת.',
            

        ];
    }





}
