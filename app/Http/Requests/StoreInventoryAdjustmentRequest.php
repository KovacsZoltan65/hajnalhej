<?php

namespace App\Http\Requests;

use App\Models\InventoryMovement;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreInventoryAdjustmentRequest extends FormRequest
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
            'ingredient_id' => ['required', 'integer', 'exists:ingredients,id'],
            'difference' => ['required', 'numeric', 'not_in:0', 'max:999999999.999'],
            'unit_cost' => ['nullable', 'numeric', 'min:0', 'max:999999999.9999'],
            'occurred_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:4000'],
        ];
    }
}

