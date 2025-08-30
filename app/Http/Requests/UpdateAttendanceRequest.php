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