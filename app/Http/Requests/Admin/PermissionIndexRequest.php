<?php

namespace App\Http\Requests\Admin;

use App\Support\PermissionRegistry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PermissionIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(PermissionRegistry::PERMISSIONS_VIEW) ?? false;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:120'],
            'module' => ['nullable', 'string', 'max:80'],
            'dangerous_only' => ['nullable', 'boolean'],
            'usage_state' => ['nullable', Rule::in(['used', 'unused'])],
            'registry_state' => ['nullable', Rule::in(['synced', 'missing_in_db', 'orphan_db_only'])],
            'sort_field' => ['nullable', Rule::in(['name', 'module', 'roles_count', 'users_count', 'registry_state'])],
            'sort_direction' => ['nullable', Rule::in(['asc', 'desc'])],
            'per_page' => ['nullable', 'integer', 'min:10', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
