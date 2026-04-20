<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\RecipeStep;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RecipeStep>
 */
class RecipeStepFactory extends Factory
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
            'title' => fake()->randomElement([
                'Elokeszites',
                'Dagasztas',
                'Pihentetes',
                'Kelesztes',
                'Sutes',
                'Hutes',
            ]),
            'step_type' => fake()->randomElement(RecipeStep::stepTypes()),
            'description' => fake()->optional()->sentence(),
            'duration_minutes' => fake()->optional(80)->numberBetween(5, 180),
            'wait_minutes' => fake()->optional(70)->numberBetween(5, 240),
            'temperature_celsius' => fake()->optional()->randomFloat(1, 18, 260),
            'sort_order' => fake()->numberBetween(0, 30),
            'is_active' => fake()->boolean(90),
        ];
    }
}

