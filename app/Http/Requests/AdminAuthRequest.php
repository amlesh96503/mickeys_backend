<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdminAuthRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $action = $this->route()->getActionMethod();

        return match ($action) {
            'register' => [
                'name'        => 'required|string|max:255',
                'email'       => 'required|email|unique:admin_users,email',
                'password'    => 'required|string|min:8|confirmed',
                'phone'       => 'nullable|string|max:20',
                'avatar'      => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
                'role'        => ['sometimes', Rule::in(['super_admin', 'admin', 'manager', 'editor', 'viewer'])],
                'permissions' => 'nullable|array',
            ],
            'login' => [
                'email'    => 'required|email',
                'password' => 'required|string',
            ],
            'updateProfile' => [
                'name'   => 'sometimes|string|max:255',
                'phone'  => 'nullable|string|max:20',
                'avatar' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            ],
            'update' => [
                'name'        => 'sometimes|string|max:255',
                'phone'       => 'nullable|string|max:20',
                'avatar'      => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
                'role'        => ['sometimes', Rule::in(['super_admin', 'admin', 'manager', 'editor', 'viewer'])],
                'status'      => ['sometimes', Rule::in(['active', 'inactive', 'suspended'])],
                'permissions' => 'nullable|array',
                'password'    => 'sometimes|string|min:8|confirmed',
            ],
            default => [],
        };
    }
}
