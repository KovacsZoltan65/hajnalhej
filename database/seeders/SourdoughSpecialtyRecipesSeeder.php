<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductIngredient;
use App\Models\RecipeStep;
use Database\Seeders\Concerns\UsesSeededIngredients;
use Illuminate\Database\Seeder;

class SourdoughSpecialtyRecipesSeeder extends Seeder
{
    use UsesSeededIngredients;

    public function run(): void
    {
        $category = Category::query()->updateOrCreate(
            ['slug' => 'kovaszos-pekseg'],
            [
                'name' => 'Kovászos Pékség',
                'description' => 'Bagett, ciabatta és kézműves pékáruk.',
                'is_active' => true,
                'sort_order' => 20,
            ]
        );

        $ingredients = [
            'liszt' => ['name' => 'Búzakenyérliszt', 'unit' => 'g'],
            'viz' => ['name' => 'Víz', 'unit' => 'ml'],
            'kovasz' => ['name' => 'Aktív kovász', 'unit' => 'g'],
            'so' => ['name' => 'Só', 'unit' => 'g'],
            'olivaolaj' => ['name' => 'Olívaolaj', 'unit' => 'g'],
        ];

        $models = $this->seededIngredients(array_keys($ingredients));

        $this->seedBaguette($category, $models);
        $this->seedCiabatta($category, $models);
    }

    protected function seedBaguette($category, $ingredients): void
    {
        $product = Product::query()->updateOrCreate(
            ['slug' => 'kovaszos-bagett'],
            [
                'category_id' => $category->id,
                'name' => 'Kovászos Bagett',
                'short_description' => 'Vékony roppanós héjú klasszikus bagett.',
                'description' => 'Kovászos bagett hosszú fermentációval és gőzös sütéssel.',
                'price' => 150,
                'sort_order' => 10,
            ]
        );

        $items = [
            ['liszt', 500, 1],
            ['viz', 360, 2],
            ['kovasz', 100, 3],
            ['so', 10, 4],
        ];

        foreach ($items as [$slug, $qty, $order]) {
            ProductIngredient::query()->updateOrCreate(
                [
                    'product_id' => $product->id,
                    'ingredient_id' => $ingredients[$slug]->id,
                ],
                [
                    'quantity' => $qty,
                    'sort_order' => $order,
                ]
            );
        }

        $steps = [
            ['08:00 keverés', 'mixing', 15, 45],
            ['09:00 hajtás #1', 'resting', 10, 50],
            ['10:00 hajtás #2', 'resting', 10, 140],
            ['12:30 osztás', 'shaping', 15, 15],
            ['13:00 pihentetés', 'resting', 5, 25],
            ['13:30 rudazás', 'preparation', 20, 40],
            ['14:30 sütés gőzzel', 'baking', 30, 0],
        ];

        foreach ($steps as $index => $step) {
            RecipeStep::query()->updateOrCreate(
                [
                    'product_id' => $product->id,
                    'sort_order' => $index + 1,
                ],
                [
                    'title' => $step[0],
                    'step_type' => $this->normalizeStepType($step[1]),
                    'duration_minutes' => $step[2],
                    'wait_minutes' => $step[3],
                    'work_instruction' => $step[0],
                    'completion_criteria' => 'Lépés sikeresen végrehajtva.',
                    'attention_points' => 'Első 10 perc gőz a vékony héjért.',
                    'required_tools' => 'mérleg, kaparó, sütő',
                    'expected_result' => 'Következő lépésre kész bagett tészta.',
                    'is_active' => true,
                ]
            );
        }
    }

    protected function seedCiabatta($category, $ingredients): void
    {
        $product = Product::query()->updateOrCreate(
            ['slug' => 'kovaszos-ciabatta'],
            [
                'category_id' => $category->id,
                'name' => 'Kovászos Ciabatta',
                'short_description' => 'Nyitott bélzetű, rusztikus ciabatta.',
                'description' => 'Magas hidratációjú kovászos ciabatta olívaolajjal.',
                'price' => 150,
                'sort_order' => 20,
            ]
        );

        $items = [
            ['liszt', 500, 1],
            ['viz', 410, 2],
            ['kovasz', 100, 3],
            ['so', 10, 4],
            ['olivaolaj', 15, 5],
        ];

        foreach ($items as [$slug, $qty, $order]) {
            ProductIngredient::query()->updateOrCreate(
                [
                    'product_id' => $product->id,
                    'ingredient_id' => $ingredients[$slug]->id,
                ],
                [
                    'quantity' => $qty,
                    'sort_order' => $order,
                ]
            );
        }

        $steps = [
            ['08:00 keverés', 'mixing', 15, 45],
            ['09:00 coil fold #1', 'resting', 10, 20],
            ['09:30 coil fold #2', 'resting', 10, 20],
            ['10:00 coil fold #3', 'resting', 10, 170],
            ['13:00 lisztes pultra borítás', 'preparation', 10, 20],
            ['13:30 darabolás', 'preparation', 15, 15],
            ['14:00 sütés', 'baking', 30, 0],
        ];

        foreach ($steps as $index => $step) {
            RecipeStep::query()->updateOrCreate(
                [
                    'product_id' => $product->id,
                    'sort_order' => $index + 1,
                ],
                [
                    'title' => $step[0],
                    'step_type' => $this->normalizeStepType($step[1]),
                    'duration_minutes' => $step[2],
                    'wait_minutes' => $step[3],
                    'work_instruction' => $step[0],
                    'completion_criteria' => 'Lépés sikeresen végrehajtva.',
                    'attention_points' => 'Ne formázd túl. A ciabatta szereti a szabadságot.',
                    'required_tools' => 'kaparó, sütőlap, sütő',
                    'expected_result' => 'Következő lépésre kész ciabatta tészta.',
                    'is_active' => true,
                ]
            );
        }
    }

    private function normalizeStepType(string $stepType): string
    {
        return match ($stepType) {
            'folding', 'shaping' => 'preparation',
            default => $stepType,
        };
    }
}
