<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductionPlan;
use App\Models\ProductionPlanItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductionPlanItem>
 */
class ProductionPlanItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'production_plan_id' => ProductionPlan::factory(),
            'product_id' => Product::factory(),
            'product_name_snapshot' => fake()->words(3, true),
            'product_slug_snapshot' => fake()->slug(3),
            'target_quantity' => fake()->randomFloat(3, 1, 120),
            'unit_label' => 'db',
            'sort_order' => fake()->numberBetween(0, 20),
            'computed_ingredient_count' => fake()->numberBetween(0, 12),
            'computed_step_count' => fake()->numberBetween(0, 10),
            'computed_active_minutes' => fake()->numberBetween(0, 800),
            'computed_wait_minutes' => fake()->numberBetween(0, 1600),
        ];
    }
}

