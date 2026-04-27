<?php

namespace App\Http\Requests\Admin;

use App\Models\IngredientSupplierTerm;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreIngredientSupplierTermRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', IngredientSupplierTerm::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'ingredient_id' => ['required', 'integer', 'exists:ingredients,id'],
            'supplier_id' => [
                'required',
                'integer',
                'exists:suppliers,id',
                Rule::unique('ingredient_supplier_terms', 'supplier_id')
                    ->where(fn ($query) => $query
                        ->where('ingredient_id', $this->integer('ingredient_id'))
                        ->whereNull('deleted_at')),
            ],
            'lead_time_days' => ['nullable', 'integer', 'min:0', 'max:365'],
            'minimum_order_quantity' => ['nullable', 'numeric', 'min:0', 'max:999999999.999'],
            'pack_size' => ['nullable', 'numeric', 'min:0', 'max:999999999.999'],
            'unit_cost_override' => ['nullable', 'numeric', 'min:0', 'max:9999999999.99'],
            'preferred' => ['required', 'boolean', 'prohibited_if:active,false,0'],
            'active' => ['required', 'boolean'],
            'meta' => ['nullable', 'json'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'supplier_id.unique' => 'Ehhez az alapanyaghoz ez a beszállító már rögzítve van.',
            'preferred.prohibited_if' => 'Inaktív beszállítói feltétel nem lehet preferált.',
        ];
    }
}
