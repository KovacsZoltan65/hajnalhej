<?php

namespace Database\Factories;

use App\Models\ProductionPlan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<ProductionPlan>
 */
class ProductionPlanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $targetAt = Carbon::now()->addDays(fake()->numberBetween(1, 5))->setTime(8, 0);

        return [
            'plan_number' => sprintf('PLAN-%s-%03d', Carbon::now()->format('Ymd'), fake()->numberBetween(1, 999)),
            'target_at' => $targetAt,
            'status' => fake()->randomElement(ProductionPlan::statuses()),
            'total_active_minutes' => fake()->numberBetween(60, 900),
            'total_wait_minutes' => fake()->numberBetween(30, 1200),
            'total_recipe_minutes' => fake()->numberBetween(120, 1800),
            'planned_start_at' => $targetAt->copy()->subHours(fake()->numberBetween(2, 16)),
            'is_locked' => fake()->boolean(25),
            'notes' => fake()->optional()->sentence(),
            'created_by' => User::factory(),
        ];
    }
}

