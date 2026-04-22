<?php

namespace App\Http\Requests\Admin;

use App\Support\PermissionRegistry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SecurityDashboardRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(PermissionRegistry::SECURITY_DASHBOARD_VIEW) ?? false;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'window' => ['nullable', Rule::in(['24h', '7d', '30d'])],
            'risk_level' => ['nullable', Rule::in(['all', 'critical', 'high', 'medium', 'low', 'info'])],
            'log_name' => ['nullable', Rule::in(['all', 'authorization', 'orders', 'user-activity'])],
            'dangerous_only' => ['nullable', 'boolean'],
            'orphan_limit' => ['nullable', 'integer', 'min:5', 'max:100'],
            'users_limit' => ['nullable', 'integer', 'min:5', 'max:100'],
            'events_limit' => ['nullable', 'integer', 'min:5', 'max:100'],
        ];
    }
}

