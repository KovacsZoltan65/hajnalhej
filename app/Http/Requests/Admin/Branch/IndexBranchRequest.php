<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Branch;

use App\Data\Branches\BranchType;
use App\Models\Branch;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexBranchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('viewAny', Branch::class) ?? false;
    }

    /**
     * @return array<string, array<int, mixed>|string>
     */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:120'],
            'type' => ['nullable', Rule::in(BranchType::values())],
            'active' => ['nullable', 'boolean'],
            'sort_field' => ['nullable', Rule::in(['name', 'code', 'type', 'email', 'phone', 'address', 'active', 'updated_at'])],
            'sort_direction' => ['nullable', Rule::in(['asc', 'desc'])],
            'per_page' => ['nullable', 'integer', 'min:5', 'max:50'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
