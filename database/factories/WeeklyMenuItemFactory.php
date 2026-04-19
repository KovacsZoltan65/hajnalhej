<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\WeeklyMenu;
use App\Models\WeeklyMenuItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WeeklyMenuItem>
 */
class WeeklyMenuItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'weekly_menu_id' => WeeklyMenu::factory(),
            'product_id' => Product::factory(),
            'category_id' => null,
            'override_name' => fake()->optional()->sentence(3),
            'override_short_description' => fake()->optional()->sentence(6),
            'override_price' => fake()->optional()->randomFloat(2, 650, 4500),
            'sort_order' => fake()->numberBetween(0, 30),
            'is_active' => fake()->boolean(90),
            'badge_text' => fake()->optional()->randomElement(['Uj', 'Kedvenc', 'Heti ajanlat']),
            'stock_note' => fake()->optional()->sentence(4),
        ];
    }
}
