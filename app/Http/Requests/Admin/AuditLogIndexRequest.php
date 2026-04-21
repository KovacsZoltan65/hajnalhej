<?php

namespace App\Http\Requests\Admin;

use App\Support\AuditEventRegistry;
use App\Support\PermissionRegistry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AuditLogIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(PermissionRegistry::AUDIT_LOGS_VIEW) ?? false;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:120'],
            'log_name' => ['nullable', 'string', Rule::in(array_keys(AuditEventRegistry::logNameLabels()))],
            'event_key' => ['nullable', 'string', Rule::in(AuditEventRegistry::eventKeys())],
            'subject_type' => ['nullable', 'string', Rule::in(['role', 'user', 'order'])],
            'per_page' => ['nullable', 'integer', 'min:10', 'max:100'],
        ];
    }
}
