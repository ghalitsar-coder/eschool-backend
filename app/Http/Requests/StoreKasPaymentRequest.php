<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreKasPaymentRequest extends FormRequest
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
            'kas_record_id' => 'required|exists:kas_records,id',
            'member_id' => 'required|exists:user_eschool_roles,id',
            'amount' => 'required|numeric|min:0',
            'month' => 'required|string|max:20',
            'year' => 'required|integer',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'kas_record_id.required' => 'Kas record ID is required',
            'kas_record_id.exists' => 'The selected kas record does not exist',
            'member_id.required' => 'Member ID is required',
            'member_id.exists' => 'The selected member does not exist',
            'amount.required' => 'Amount is required',
            'amount.numeric' => 'Amount must be a number',
            'amount.min' => 'Amount must be at least 0',
            'month.required' => 'Month is required',
            'month.string' => 'Month must be a string',
            'month.max' => 'Month may not be greater than 20 characters',
            'year.required' => 'Year is required',
            'year.integer' => 'Year must be an integer',
        ];
    }
}