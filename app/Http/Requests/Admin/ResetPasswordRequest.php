<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class ResetPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'token' => ['required', 'string'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ];
    }

    public function messages(): array
    {
        return [
            'token.required' => 'A valid password reset token is required.',
            'email.required' => 'Your email address is required.',
            'password.required' => 'A new password is required.',
            'password.confirmed' => 'Password confirmation does not match.',
            'password.min' => 'Password must contain at least 8 characters with letters and numbers.',
        ];
    }
}
