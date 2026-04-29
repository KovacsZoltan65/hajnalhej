<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use App\Support\PermissionRegistry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAdminUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', User::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190', Rule::unique('users', 'email')],
            'phone' => ['nullable', 'string', 'max:40'],
            'status' => ['required', Rule::in(User::statuses())],
            'password' => ['required', 'string', 'min:8', 'max:190'],
            'roles' => ['array'],
            'roles.*' => [Rule::exists('roles', 'name')->where('guard_name', 'web')],
        ];
    }
}
