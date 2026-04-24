<?php

namespace App\Http\Requests;

use App\Models\StockCount;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateStockCountRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var StockCount $stockCount */
        $stockCount = $this->route('stockCount');

        return $this->user()?->can('update', $stockCount) ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'count_date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:4000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.ingredient_id' => ['required', 'integer', 'exists:ingredients,id'],
            'items.*.expected_quantity' => ['required', 'numeric', 'min:0', 'max:999999999.999'],
            'items.*.counted_quantity' => ['required', 'numeric', 'min:0', 'max:999999999.999'],
        ];
    }
}

