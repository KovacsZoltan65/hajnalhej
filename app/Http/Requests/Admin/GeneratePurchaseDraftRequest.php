<?php

namespace App\Http\Requests\Admin;

use App\Models\Purchase;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GeneratePurchaseDraftRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Purchase::class) ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'days' => ['nullable', 'integer', Rule::in([7, 30, 90, 180])],
            'ingredient_id' => ['nullable', 'integer', 'exists:ingredients,id'],
            'supplier_id' => ['nullable', 'integer', 'exists:suppliers,id'],
            'urgency' => ['nullable', 'string', Rule::in(['critical', 'high', 'medium', 'low'])],
            'ingredient_ids' => ['nullable', 'array'],
            'ingredient_ids.*' => ['integer', 'distinct', 'exists:ingredients,id'],
        ];
    }
}
