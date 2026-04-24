<?php

namespace App\Http\Requests;

use App\Models\InventoryMovement;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreWasteEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', InventoryMovement::class) ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'waste_type' => ['required', Rule::in(['ingredient', 'product'])],
            'ingredient_id' => ['nullable', 'integer', 'exists:ingredients,id', 'required_if:waste_type,ingredient'],
            'product_id' => ['nullable', 'integer', 'exists:products,id', 'required_if:waste_type,product'],
            'quantity' => ['required', 'numeric', 'min:0.001', 'max:999999999.999'],
            'reason' => ['required', Rule::in(['lejárt', 'sérült', 'gyártási hiba', 'romlott', 'ismeretlen'])],
            'occurred_at' => ['nullable', 'date'],
        ];
    }
}
