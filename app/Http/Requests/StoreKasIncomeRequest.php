<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreKasIncomeRequest extends FormRequest
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
            'eschool_id' => 'required|exists:eschools,id',
            'description' => 'required|string|max:255',
            'date' => 'required|date',
            'payments' => 'required|array|min:1',
            'payments.*.member_id' => 'required|exists:user_eschool_roles,id',
            'payments.*.amount' => 'required|numeric|min:0',
            'payments.*.month' => 'required|numeric|max:20',
            'payments.*.year' => 'required|integer',
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
            'eschool_id.required' => 'Eschool ID is required',
            'eschool_id.exists' => 'The selected eschool does not exist',
            'description.required' => 'Description is required',
            'description.string' => 'Description must be a string',
            'description.max' => 'Description may not be greater than 255 characters',
            'date.required' => 'Date is required',
            'date.date' => 'Date must be a valid date',
            'payments.required' => 'At least one payment is required',
            'payments.array' => 'Payments must be an array',
            'payments.*.member_id.required' => 'Member ID is required for each payment',
            'payments.*.member_id.exists' => 'The selected member does not exist',
            'payments.*.amount.required' => 'Amount is required for each payment',
            'payments.*.amount.numeric' => 'Amount must be a number',
            'payments.*.amount.min' => 'Amount must be at least 0',
            'payments.*.month.required' => 'Month is required for each payment',
            'payments.*.month.numeric' => 'Month must be a numeric',
            'payments.*.month.max' => 'Month may not be greater than 20 characters',
            'payments.*.year.required' => 'Year is required for each payment',
            'payments.*.year.integer' => 'Year must be an integer',
        ];
    }
}