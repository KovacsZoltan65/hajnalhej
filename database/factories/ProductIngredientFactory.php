<?php

namespace Database\Factories;

use App\Models\Ingredient;
use App\Models\Product;
use App\Models\ProductIngredient;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductIngredient>
 */
class ProductIngredientFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'ingredient_id' => Ingredient::factory(),
            'quantity' => fake()->randomFloat(3, 0.001, 10),
            'sort_order' => fake()->numberBetween(0, 30),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
