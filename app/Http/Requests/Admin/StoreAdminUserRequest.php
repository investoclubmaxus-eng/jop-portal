<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StoreAdminUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        return $user !== null && ($user->isSuperAdmin() || $user->isAdmin());
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('password') && !$this->filled('password_confirmation')) {
            $this->merge([
                'password_confirmation' => $this->password,
            ]);
        }

        if ($this->role === 'hr_manager') {
            $this->merge(['role' => User::ROLE_HR]);
        }
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', Rule::in([
                User::ROLE_SUPER_ADMIN,
                User::ROLE_ADMIN,
                User::ROLE_RECRUITER,
                User::ROLE_HR,
                User::ROLE_HR_MANAGER,
            ])],
            'status' => ['required', Rule::in([User::STATUS_ACTIVE, User::STATUS_INACTIVE])],
            'password' => ['required', 'confirmed', Password::min(8)],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Staff full name is required.',
            'email.required' => 'Email address is required.',
            'email.unique' => 'This email is already in use by another admin.',
            'role.required' => 'Please select an administrative role.',
            'status.required' => 'Account status must be specified (active or inactive).',
            'password.required' => 'A secure password of at least 8 characters is required.',
            'password.confirmed' => 'Password confirmation does not match.',
        ];
    }
}
