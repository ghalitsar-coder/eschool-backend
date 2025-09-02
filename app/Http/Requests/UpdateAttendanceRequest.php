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
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {
        // Convert string boolean values to actual boolean for validation
        if ($this->has('is_present')) {
            $isPresent = $this->input('is_present');
            
            if (is_string($isPresent)) {
                $this->merge([
                    'is_present' => in_array(strtolower($isPresent), ['1', 'true', 'on', 'yes'], true)
                ]);
            }
        }
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'member_id' => [
                'sometimes', 
                'exists:members,id',
                function ($attribute, $value, $fail) {
                    // Custom validation to check if member belongs to the eschool of the attendance record
                    $attendance = $this->route('attendance');
                    if ($attendance && $value) {
                        // If member_id is being updated, check if it belongs to the eschool
                        $eschool = $attendance->eschool;
                        if ($eschool && !$eschool->members()->where('members.id', $value)->exists()) {
                            $fail('The selected member is not associated with the given eschool.');
                        }
                    }
                }
            ],
            'is_present' => ['sometimes', 'boolean'],
            'notes' => ['nullable', 'string', 'max:500'],
            'date' => ['sometimes', 'date'],
            'proof_document' => [
                'nullable',
                'file',
                'mimes:pdf,jpg,jpeg,png',
                'max:5120', // 5MB in kilobytes
            ],
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
            'proof_document.file' => 'Proof document must be a file.',
            'proof_document.mimes' => 'Proof document must be a file of type: pdf, jpg, jpeg, png.',
            'proof_document.max' => 'Proof document may not be greater than 5MB.',
        ];
    }
}