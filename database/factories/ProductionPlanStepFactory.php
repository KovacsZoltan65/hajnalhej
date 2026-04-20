<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductionPlan;
use App\Models\ProductionPlanItem;
use App\Models\ProductionPlanStep;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<ProductionPlanStep>
 */
class ProductionPlanStepFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $start = Carbon::now()->addMinutes(fake()->numberBetween(-720, 60));
        $duration = fake()->numberBetween(5, 180);
        $wait = fake()->numberBetween(0, 360);

        return [
            'production_plan_id' => ProductionPlan::factory(),
            'production_plan_item_id' => ProductionPlanItem::factory(),
            'product_id' => Product::factory(),
            'depends_on_product_id' => null,
            'title' => fake()->randomElement(['Kovasz etetese', 'Bulk ferment', 'Sutes', 'Hutes']),
            'step_type' => fake()->randomElement(['preparation', 'mixing', 'resting', 'proofing', 'baking', 'cooling', 'finishing']),
            'description' => fake()->optional()->sentence(),
            'work_instruction' => fake()->optional()->sentence(),
            'completion_criteria' => fake()->optional()->sentence(),
            'attention_points' => fake()->optional()->sentence(),
            'required_tools' => fake()->optional()->sentence(),
            'expected_result' => fake()->optional()->sentence(),
            'starts_at' => $start,
            'ends_at' => $start->copy()->addMinutes($duration + $wait),
            'duration_minutes' => $duration,
            'wait_minutes' => $wait,
            'sort_order' => fake()->numberBetween(0, 200),
            'timeline_group' => fake()->slug(2),
            'is_dependency' => fake()->boolean(20),
            'meta' => null,
        ];
    }
}
