<?php

namespace App\Http\Requests;

use App\Models\Ingredient;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateIngredientRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        /** @var Ingredient $ingredient */
        $ingredient = $this->route('ingredient');

        return $this->user()?->can('update', $ingredient) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var Ingredient $ingredient */
        $ingredient = $this->route('ingredient');

        return [
            'name' => ['required', 'string', 'max:160', Rule::unique('ingredients', 'name')->ignore($ingredient->id)],
            'slug' => ['nullable', 'string', 'max:180', 'alpha_dash', Rule::unique('ingredients', 'slug')->ignore($ingredient->id)],
            'sku' => ['nullable', 'string', 'max:80', Rule::unique('ingredients', 'sku')->ignore($ingredient->id)],
            'unit' => ['required', 'string', Rule::in(Ingredient::allowedUnits())],
            'estimated_unit_cost' => ['nullable', 'numeric', 'min:0', 'max:999999999.9999'],
            'current_stock' => ['nullable', 'numeric', 'min:0', 'max:999999999.999'],
            'minimum_stock' => ['nullable', 'numeric', 'min:0', 'max:999999999.999'],
            'is_active' => ['required', 'boolean'],
            'notes' => ['nullable', 'string', 'max:4000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'alapanyag neve',
            'slug' => 'slug',
            'sku' => 'sku',
            'unit' => 'mertekegyseg',
            'estimated_unit_cost' => 'becsult egysegkoltseg',
            'current_stock' => 'aktualis keszlet',
            'minimum_stock' => 'minimum keszlet',
            'is_active' => 'aktiv statusz',
            'notes' => 'megjegyzes',
        ];
    }
}
