<?php

namespace App\Http\Requests\Admin;

use App\Models\JobApplication;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulkUpdateApplicationStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'application_ids' => ['required', 'array', 'min:1'],
            'application_ids.*' => ['required', 'integer', 'exists:job_applications,id'],
            'status' => ['required', 'string', Rule::in([
                JobApplication::STATUS_PENDING,
                JobApplication::STATUS_SUBMITTED,
                JobApplication::STATUS_UNDER_REVIEW,
                JobApplication::STATUS_SHORTLISTED,
                JobApplication::STATUS_REJECTED,
                'on_hold',
                'interviewed',
                'hired',
            ])],
            'remarks' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'application_ids.required' => 'Please select at least one application.',
            'application_ids.min' => 'Please select at least one candidate application.',
            'status.required' => 'Please select an application status to apply.',
        ];
    }
}
