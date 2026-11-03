<?php

namespace App\Http\Requests\Admin;

use App\Models\JobApplication;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateApplicationStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
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
            'screening_remarks' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'Please select an application status.',
            'status.in' => 'Selected status is invalid.',
        ];
    }
}
