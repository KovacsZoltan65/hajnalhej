<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->randomElement([
            'Kenyerek',
            'Édes Pékáru',
            'Sós pékáru',
            'Pizza',
            'Szezonális különlegességek',
        ]).' '.fake()->unique()->numerify('####');

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => fake()->optional()->sentence(10),
            'is_active' => fake()->boolean(85),
            'sort_order' => fake()->numberBetween(0, 20),
        ];
    }
}
