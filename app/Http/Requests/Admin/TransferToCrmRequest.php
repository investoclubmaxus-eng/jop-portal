<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class TransferToCrmRequest extends FormRequest
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
        ];
    }

    public function messages(): array
    {
        return [
            'application_ids.required' => 'Please select at least one candidate application to transfer.',
            'application_ids.min' => 'Please select at least one application to transfer to CRM.',
        ];
    }
}
