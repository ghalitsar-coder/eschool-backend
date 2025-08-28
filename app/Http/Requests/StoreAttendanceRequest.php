<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAttendanceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Only koordinator can record attendance
        return auth()->user() && auth()->user()->role === 'koordinator';
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'eschool_id' => ['required',  'exists:eschools,id'],
            'date' => ['required', 'date'],
            'members' => ['required', 'array', 'min:1'],
            'members.*.member_id' => ['required',  'exists:members,id'],
            'members.*.is_present' => ['required', 'boolean'],
            'members.*.notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            'eschool_id.required' => 'Eschool ID is required.',
            'eschool_id.exists' => 'The selected eschool does not exist.',
            'date.required' => 'Date is required.',
            'date.date' => 'Date must be a valid date.',
            'members.required' => 'At least one member is required.',
            'members.array' => 'Members must be an array.',
            'members.min' => 'At least one member must be provided.',
            'members.*.member_id.required' => 'Member ID is required for each member.',
            'members.*.member_id.exists' => 'One or more selected members do not exist.',
            'members.*.is_present.required' => 'Attendance status is required for each member.',
            'members.*.is_present.boolean' => 'Attendance status must be true or false.',
            'members.*.notes.string' => 'Notes must be a string.',
            'members.*.notes.max' => 'Notes cannot exceed 500 characters.',
        ];
    }
}