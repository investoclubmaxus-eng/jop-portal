<?php

namespace App\Http\Requests\Public;

use Illuminate\Foundation\Http\FormRequest;

class SubmitJobApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('phone')) {
            $this->merge([
                'phone' => preg_replace('/\D/', '', (string) $this->phone),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'phone' => ['required', 'string', 'digits:10'],
            'current_location' => ['nullable', 'string', 'max:255'],
            'highest_qualification' => ['nullable', 'string', 'max:255'],
            'total_experience_years' => ['nullable', 'numeric', 'min:0', 'max:60'],
            'current_ctc' => ['nullable', 'numeric', 'min:0'],
            'expected_ctc' => ['nullable', 'numeric', 'min:0'],
            'source' => ['nullable', 'string', 'max:50'],
            'resume' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:5120'], // 5MB max
            'additional_documents' => ['nullable', 'array', 'max:5'],
            'additional_documents.*' => ['file', 'mimes:pdf,doc,docx,jpg,jpeg,png', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'first_name.required' => 'Your first name is required.',
            'last_name.required' => 'Your last name is required.',
            'email.required' => 'A valid contact email is required.',
            'email.email' => 'Please provide a valid email address format.',
            'phone.required' => 'Your contact phone number is required.',
            'phone.digits' => 'Phone number must be exactly 10 numeric digits.',
            'resume.mimes' => 'Resume must be in PDF, DOC, or DOCX format.',
            'resume.max' => 'Resume file size cannot exceed 5MB.',
            'additional_documents.*.max' => 'Each additional document file size cannot exceed 5MB.',
        ];
    }
}
