<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Eschool;

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
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {
        // Convert string boolean values to actual boolean for validation
        if ($this->has('members')) {
            $members = $this->input('members');
            
            foreach ($members as $index => $member) {
                if (isset($member['is_present'])) {
                    // Convert "1", "0", "true", "false" strings to boolean
                    $isPresent = $member['is_present'];
                    
                    if (is_string($isPresent)) {
                        $members[$index]['is_present'] = in_array(strtolower($isPresent), ['1', 'true', 'on', 'yes'], true);
                    }
                }
            }
            
            $this->merge(['members' => $members]);
        }
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
            'members.*.member_id' => [
                'required', 
                'exists:members,id',
                function ($attribute, $value, $fail) {
                    // Custom validation to check if member belongs to the given eschool
                    $eschoolId = $this->input('eschool_id');
                    $eschool = Eschool::find($eschoolId);
                    
                    if ($eschool && !$eschool->members()->where('members.id', $value)->exists()) {
                        $fail('The selected member is not associated with the given eschool.');
                    }
                }
            ],
            'members.*.is_present' => ['required', 'boolean'],
            'members.*.notes' => ['nullable', 'string', 'max:500'],
            'members.*.proof_document' => [
                'nullable',
                'file',
                'mimes:pdf,jpg,jpeg,png',
                'max:5120', // 5MB in kilobytes
                function ($attribute, $value, $fail) {
                    // Only allow proof document when member is absent
                    $index = explode('.', str_replace('members.', '', $attribute))[0];
                    $isPresent = $this->input("members.{$index}.is_present");
                    
                    if ($value && $isPresent) {
                        $fail('Proof document can only be uploaded for absent members.');
                    }
                }
            ],
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
            'members.*.proof_document.file' => 'Proof document must be a file.',
            'members.*.proof_document.mimes' => 'Proof document must be a file of type: pdf, jpg, jpeg, png.',
            'members.*.proof_document.max' => 'Proof document may not be greater than 5MB.',
        ];
    }
}