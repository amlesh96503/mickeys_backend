<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserAuthRequest extends FormRequest
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
                'name'     => 'required|string|max:255',
                'email'    => 'required|email|unique:users,email',
                'password' => 'required|string|min:6|confirmed',
                'phone'    => 'nullable|string|max:20',
                'avatar'   => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
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
            default => [],
        };
    }
}
