<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Branch;

use App\Data\Branches\BranchType;
use App\Models\Branch;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBranchRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Branch $branch */
        $branch = $this->route('branch');

        return $this->user()?->can('update', $branch) ?? false;
    }

    /**
     * @return array<string, array<int, mixed>|string>
     */
    public function rules(): array
    {
        /** @var Branch $branch */
        $branch = $this->route('branch');

        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', Rule::unique('branches', 'code')->ignore($branch->id)],
            'type' => ['required', Rule::in(BranchType::values())],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:500'],
            'active' => ['required', 'boolean'],
            'meta' => ['nullable', 'array'],
        ];
    }
}
