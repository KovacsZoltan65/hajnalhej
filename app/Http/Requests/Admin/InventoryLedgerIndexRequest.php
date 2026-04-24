<?php

namespace App\Http\Requests\Admin;

use App\Models\InventoryMovement;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InventoryLedgerIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'days' => ['nullable', 'integer', Rule::in([7, 14, 30, 90])],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
            'ingredient_id' => ['nullable', 'integer', 'exists:ingredients,id'],
            'movement_type' => ['nullable', Rule::in(InventoryMovement::movementTypes())],
            'search' => ['nullable', 'string', 'max:160'],
            'per_page' => ['nullable', 'integer', 'min:5', 'max:100'],
        ];
    }
}

