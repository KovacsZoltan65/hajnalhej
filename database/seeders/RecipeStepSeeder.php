<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\RecipeStep;
use Illuminate\Database\Seeder;

class RecipeStepSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $recipes = [
            'klasszikus-kovaszos-kenyer' => [
                [
                    'title' => 'Alapanyagok előkészítése',
                    'step_type' => RecipeStep::TYPE_PREPARATION,
                    'description' => 'A liszt, só és víz kimeréve, kovász felfrissítve.',
                    'duration_minutes' => 20,
                    'wait_minutes' => 0,
                    'temperature_celsius' => null,
                    'sort_order' => 1,
                    'is_active' => true,
                ],
                [
                    'title' => 'Autolízis és dagasztás',
                    'step_type' => RecipeStep::TYPE_MIXING,
                    'description' => 'Rövid autolízis után fokozatos dagasztás.',
                    'duration_minutes' => 25,
                    'wait_minutes' => 30,
                    'temperature_celsius' => 24.0,
                    'sort_order' => 2,
                    'is_active' => true,
                ],
                [
                    'title' => 'Kelesztés és sütés',
                    'step_type' => RecipeStep::TYPE_BAKING,
                    'description' => 'Hosszú kelesztés, majd gőzös sütés.',
                    'duration_minutes' => 45,
                    'wait_minutes' => 180,
                    'temperature_celsius' => 245.0,
                    'sort_order' => 3,
                    'is_active' => true,
                ],
            ],
            'magvas-vekni' => [
                [
                    'title' => 'Magok beáztatása',
                    'step_type' => RecipeStep::TYPE_PREPARATION,
                    'description' => 'Napraforgó és lenmag előkészítése.',
                    'duration_minutes' => 10,
                    'wait_minutes' => 60,
                    'temperature_celsius' => null,
                    'sort_order' => 1,
                    'is_active' => true,
                ],
                [
                    'title' => 'Dagasztás és pihentetés',
                    'step_type' => RecipeStep::TYPE_RESTING,
                    'description' => null,
                    'duration_minutes' => 30,
                    'wait_minutes' => 120,
                    'temperature_celsius' => 24.0,
                    'sort_order' => 2,
                    'is_active' => true,
                ],
            ],
        ];

        foreach ($recipes as $productSlug => $steps) {
            $product = Product::query()->where('slug', $productSlug)->first();

            if (! $product) {
                continue;
            }

            foreach ($steps as $step) {
                RecipeStep::query()->updateOrCreate(
                    [
                        'product_id' => $product->id,
                        'sort_order' => $step['sort_order'],
                    ],
                    $step,
                );
            }
        }
    }
}

