<?php

namespace App\Http\Requests\Admin;

use App\Support\PermissionRegistry;
use Illuminate\Foundation\Http\FormRequest;

class SyncPermissionsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(PermissionRegistry::PERMISSIONS_SYNC) ?? false;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'dry_run' => ['nullable', 'boolean'],
        ];
    }
}
