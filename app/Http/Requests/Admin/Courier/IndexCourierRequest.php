<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Courier;

use App\Models\Courier;
use App\Support\PermissionRegistry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexCourierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(PermissionRegistry::COURIERS_VIEW_ANY) ?? false;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::in(Courier::statuses())],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:5', 'max:50'],
            'sort_field' => ['nullable', Rule::in(['name', 'email', 'phone', 'status', 'created_at'])],
            'sort_direction' => ['nullable', Rule::in(['asc', 'desc'])],
        ];
    }
}
