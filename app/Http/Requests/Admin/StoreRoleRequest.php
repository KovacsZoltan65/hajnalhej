<?php

namespace App\Http\Requests\Admin;

use App\Support\PermissionRegistry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(PermissionRegistry::ROLES_CREATE) ?? false;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'min:2',
                'max:60',
                'regex:/^[a-z0-9._-]+$/',
                Rule::unique('roles', 'name')->where('guard_name', 'web'),
            ],
        ];
    }
}
