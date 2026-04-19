<?php

namespace App\Http\Requests;

use App\Models\Ingredient;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreIngredientRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create', Ingredient::class) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:160', Rule::unique('ingredients', 'name')],
            'slug' => ['nullable', 'string', 'max:180', 'alpha_dash', Rule::unique('ingredients', 'slug')],
            'sku' => ['nullable', 'string', 'max:80', Rule::unique('ingredients', 'sku')],
            'unit' => ['required', 'string', Rule::in(Ingredient::allowedUnits())],
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
            'current_stock' => 'aktualis keszlet',
            'minimum_stock' => 'minimum keszlet',
            'is_active' => 'aktiv statusz',
            'notes' => 'megjegyzes',
        ];
    }
}
