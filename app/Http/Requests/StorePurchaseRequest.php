<?php

namespace App\Http\Requests;

use App\Models\Purchase;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePurchaseRequest extends FormRequest
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
            'supplier_id' => ['nullable', 'integer', 'exists:suppliers,id'],
            'reference_number' => ['nullable', 'string', 'max:120'],
            'purchase_date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:4000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.ingredient_id' => ['required', 'integer', 'exists:ingredients,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.001', 'max:999999999.999'],
            'items.*.unit' => ['required', 'string', Rule::in(['g', 'kg', 'ml', 'l', 'db'])],
            'items.*.unit_cost' => ['required', 'numeric', 'min:0', 'max:999999999.9999'],
        ];
    }
}

