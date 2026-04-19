<?php

namespace Database\Factories;

use App\Models\Ingredient;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Ingredient>
 */
class IngredientFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->randomElement([
            'Buzaliszt',
            'Rozs liszt',
            'So',
            'Cukor',
            'Eleszto',
            'Vaj',
            'Tej',
            'Tojas',
            'Olivaolaj',
            'Viz',
        ]).' '.fake()->unique()->numerify('###');

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'sku' => fake()->optional()->bothify('ING-####'),
            'unit' => fake()->randomElement(Ingredient::allowedUnits()),
            'current_stock' => fake()->randomFloat(3, 0, 50),
            'minimum_stock' => fake()->randomFloat(3, 0, 10),
            'is_active' => fake()->boolean(90),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
