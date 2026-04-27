<?php

namespace App\Http\Requests\Admin;

use App\Models\IngredientSupplierTerm;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IngredientSupplierTermIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('viewAny', IngredientSupplierTerm::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:120'],
            'active' => ['nullable', Rule::in(['', '0', '1'])],
            'sort_field' => ['nullable', Rule::in(['ingredient', 'supplier', 'lead_time_days', 'minimum_order_quantity', 'pack_size', 'unit_cost_override', 'active', 'preferred'])],
            'sort_direction' => ['nullable', Rule::in(['asc', 'desc'])],
            'per_page' => ['nullable', 'integer', 'min:5', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
