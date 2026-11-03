<?php

namespace App\Http\Requests\Admin;

use App\Models\JobOpening;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateJobStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in([
                JobOpening::STATUS_DRAFT,
                JobOpening::STATUS_PUBLISHED,
                JobOpening::STATUS_ACTIVE,
                JobOpening::STATUS_CLOSED,
                JobOpening::STATUS_EXPIRED,
            ])],
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'Please select a valid vacancy status.',
            'status.in' => 'Invalid status option selected.',
        ];
    }
}
