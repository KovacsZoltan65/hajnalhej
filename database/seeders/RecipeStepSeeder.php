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
                    'title' => 'Alapanyagok elokeszitese',
                    'step_type' => RecipeStep::TYPE_PREPARATION,
                    'description' => 'A liszt, so es viz kimereve, kovasz felfrissitve.',
                    'duration_minutes' => 20,
                    'wait_minutes' => 0,
                    'temperature_celsius' => null,
                    'sort_order' => 1,
                    'is_active' => true,
                ],
                [
                    'title' => 'Autolizis es dagasztas',
                    'step_type' => RecipeStep::TYPE_MIXING,
                    'description' => 'Rovid autolizis utan fokozatos dagasztas.',
                    'duration_minutes' => 25,
                    'wait_minutes' => 30,
                    'temperature_celsius' => 24.0,
                    'sort_order' => 2,
                    'is_active' => true,
                ],
                [
                    'title' => 'Kelesztes es sutes',
                    'step_type' => RecipeStep::TYPE_BAKING,
                    'description' => 'Hosszu kelesztes, majd gozos sutes.',
                    'duration_minutes' => 45,
                    'wait_minutes' => 180,
                    'temperature_celsius' => 245.0,
                    'sort_order' => 3,
                    'is_active' => true,
                ],
            ],
            'magvas-vekni' => [
                [
                    'title' => 'Magok beaztatasa',
                    'step_type' => RecipeStep::TYPE_PREPARATION,
                    'description' => 'Napraforgo es lenmag elokeszitese.',
                    'duration_minutes' => 10,
                    'wait_minutes' => 60,
                    'temperature_celsius' => null,
                    'sort_order' => 1,
                    'is_active' => true,
                ],
                [
                    'title' => 'Dagasztas es pihentetes',
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

