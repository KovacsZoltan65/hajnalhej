<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserTemporaryPermissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manageTemporaryPermissions', User::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'permission_name' => ['required', 'string', Rule::exists('permissions', 'name')->where('guard_name', 'web')],
            'starts_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date', 'after:starts_at'],
            'reason' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
