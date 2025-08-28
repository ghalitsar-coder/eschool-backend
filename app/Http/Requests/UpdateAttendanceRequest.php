<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAttendanceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Only koordinator can update attendance
        return auth()->user() && auth()->user()->role === 'koordinator';
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'is_present' => ['sometimes', 'boolean'],
            'notes' => ['nullable', 'string', 'max:500'],
            'date' => ['sometimes', 'date'],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            'is_present.boolean' => 'Attendance status must be true or false.',
            'notes.string' => 'Notes must be a string.',
            'notes.max' => 'Notes cannot exceed 500 characters.',
            'date.date' => 'Date must be a valid date.',
        ];
    }
}