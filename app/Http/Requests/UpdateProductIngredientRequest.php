<?php

namespace App\Http\Requests;

use App\Models\Product;
use App\Models\ProductIngredient;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductIngredientRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        /** @var Product $product */
        $product = $this->route('product');

        return $this->user()?->can('update', $product) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var Product $product */
        $product = $this->route('product');
        /** @var ProductIngredient $productIngredient */
        $productIngredient = $this->route('productIngredient');

        return [
            'ingredient_id' => [
                'required',
                'integer',
                Rule::exists('ingredients', 'id')->where(fn ($query) => $query
                    ->where('is_active', true)
                    ->whereNull('deleted_at')),
                Rule::unique('product_ingredients', 'ingredient_id')
                    ->where(fn ($query) => $query->where('product_id', $product->id))
                    ->ignore($productIngredient->id),
            ],
            'quantity' => ['required', 'numeric', 'gt:0', 'max:999999999.999'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999999'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
