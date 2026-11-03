<?php

namespace App\Http\Requests\Admin;

use App\Models\JobOpening;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreJobOpeningRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        if (empty($this->status) && $this->filled('action_type')) {
            $this->merge([
                'status' => $this->action_type === 'publish' ? JobOpening::STATUS_PUBLISHED : JobOpening::STATUS_DRAFT,
            ]);
        } elseif (empty($this->status)) {
            $this->merge([
                'status' => JobOpening::STATUS_DRAFT,
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'job_title' => ['required', 'string', 'max:255'],
            'job_code' => ['nullable', 'string', 'max:50', 'unique:job_openings,job_code'],
            'department' => ['nullable', 'string', 'max:100'],
            'designation' => ['nullable', 'string', 'max:100'],
            'vacancies' => ['required', 'integer', 'min:1', 'max:999'],
            'employment_type' => ['nullable', 'string', 'max:50'],
            'experience_min' => ['nullable', 'numeric', 'min:0', 'max:50'],
            'experience_max' => ['nullable', 'numeric', 'min:0', 'max:50', 'gte:experience_min'],
            'salary_min' => ['nullable', 'numeric', 'min:0'],
            'salary_max' => ['nullable', 'numeric', 'min:0', 'gte:salary_min'],
            'job_description' => ['required', 'string'],
            'required_qualifications' => ['nullable', 'string'],
            'required_skills' => ['nullable'],
            'published_at' => ['nullable', 'date'],
            'application_deadline' => ['nullable', 'date'],
            'status' => ['nullable', 'string', Rule::in([
                JobOpening::STATUS_DRAFT,
                JobOpening::STATUS_PUBLISHED,
                JobOpening::STATUS_ACTIVE,
                JobOpening::STATUS_CLOSED,
                JobOpening::STATUS_EXPIRED,
            ])],
            'action_type' => ['nullable', 'string', Rule::in(['draft', 'publish', 'save'])],
        ];
    }

    public function messages(): array
    {
        return [
            'job_title.required' => 'The job title is required.',
            'vacancies.required' => 'Please specify the number of open vacancies.',
            'vacancies.min' => 'Number of vacancies must be at least 1.',
            'experience_max.gte' => 'Maximum experience cannot be less than minimum experience.',
            'salary_max.gte' => 'Maximum salary cannot be less than minimum salary.',
            'job_description.required' => 'A comprehensive job description is required.',
        ];
    }
}
