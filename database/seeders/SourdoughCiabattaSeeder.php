<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Ingredient;
use App\Models\Product;
use App\Models\ProductIngredient;
use App\Models\RecipeStep;
use Illuminate\Database\Seeder;

class SourdoughCiabattaSeeder extends Seeder
{
    public function run(): void
    {
        $category = Category::query()->updateOrCreate(
            ['slug' => 'kovaszos-pekseg'],
            [
                'name' => 'Kovászos Pékség',
                'description' => 'Kezmuves kovászos pékáruk.',
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

        $models = [];

        foreach ($ingredients as $slug => $row) {
            $models[$slug] = $this->upsertIngredient($slug, $row['name'], $row['unit']);
        }

        $product = Product::query()->updateOrCreate(
            ['slug' => 'kovaszos-ciabatta'],
            [
                'category_id' => $category->id,
                'name' => 'Kovászos Ciabatta',
                'short_description' => 'Rusztikus, lyukacsos bélzetű ciabatta.',
                'description' => 'Magas hidratációjú kovászos ciabatta olívaolajjal.',
                'price' => 0,
                'image_path' => 'products/kovaszos-ciabatta.jpg',
                'sort_order' => 30,
            ]
        );

        $items = [
            ['liszt', 500, 1, 'Alap liszt'],
            ['viz', 410, 2, 'Magas hidratáció'],
            ['kovasz', 100, 3, 'Aktív starter'],
            ['so', 10, 4, 'Ízesítés'],
            ['olivaolaj', 15, 5, 'Lágyabb textúra'],
        ];

        foreach ($items as [$slug, $qty, $order, $note]) {
            ProductIngredient::query()->updateOrCreate(
                [
                    'product_id' => $product->id,
                    'ingredient_id' => $models[$slug]->id,
                ],
                [
                    'quantity' => $qty,
                    'sort_order' => $order,
                    'notes' => $note,
                ]
            );
        }

        $steps = [
            [
                'title' => '08:00 keverés',
                'step_type' => 'mixing',
                'duration_minutes' => 15,
                'wait_minutes' => 45,
                'instruction' => 'Keverd össze a lisztet, vizet, kovászt, sót és olívaolajat.',
                'criteria' => 'Nedves, ragacsos, homogén massza.',
            ],
            [
                'title' => '09:00 coil fold #1',
                'step_type' => 'folding',
                'duration_minutes' => 10,
                'wait_minutes' => 20,
                'instruction' => 'Nedves kézzel emeld meg a tésztát és hajtsd maga alá.',
                'criteria' => 'A tészta feszesebb lesz.',
            ],
            [
                'title' => '09:30 coil fold #2',
                'step_type' => 'folding',
                'duration_minutes' => 10,
                'wait_minutes' => 20,
                'instruction' => 'Ismételd meg a coil fold műveletet.',
                'criteria' => 'Jobb tartás, buborékosodás indul.',
            ],
            [
                'title' => '10:00 coil fold #3',
                'step_type' => 'folding',
                'duration_minutes' => 10,
                'wait_minutes' => 170,
                'instruction' => 'Utolsó coil fold, majd hagyd békén fermentálódni.',
                'criteria' => 'Rugalmas, levegősödő tészta.',
            ],
            [
                'title' => '13:00 lisztes pultra borítás',
                'step_type' => 'preparation',
                'duration_minutes' => 10,
                'wait_minutes' => 20,
                'instruction' => 'Borítsd erősen lisztezett pultra.',
                'criteria' => 'A tészta megtartja a levegőt.',
            ],
            [
                'title' => '13:30 darabolás',
                'step_type' => 'shaping',
                'duration_minutes' => 15,
                'wait_minutes' => 15,
                'instruction' => 'Kaparóval vágd téglalapokra. Ne nyomkodd.',
                'criteria' => 'Laza szerkezet megmarad.',
            ],
            [
                'title' => '14:00 sütés',
                'step_type' => 'baking',
                'duration_minutes' => 30,
                'wait_minutes' => 0,
                'instruction' => '250°C-on süsd aranybarnára.',
                'criteria' => 'Ropogós héj, könnyű szerkezet.',
            ],
        ];

        foreach ($steps as $index => $step) {
            RecipeStep::query()->updateOrCreate(
                [
                    'product_id' => $product->id,
                    'sort_order' => $index + 1,
                ],
                [
                    'title' => $step['title'],
                    'step_type' => $this->normalizeStepType($step['step_type']),
                    'duration_minutes' => $step['duration_minutes'],
                    'wait_minutes' => $step['wait_minutes'],
                    'work_instruction' => $step['instruction'],
                    'completion_criteria' => $step['criteria'],
                    'attention_points' => 'Ne formázd túl. A ciabatta szereti a szabadságot.',
                    'required_tools' => 'kaparó, tál, sütőlap',
                    'expected_result' => 'Következő lépésre kész ciabatta tészta.',
                    'is_active' => true,
                ]
            );
        }
    }

    private function upsertIngredient(string $seedSlug, string $name, string $unit): Ingredient
    {
        $ingredient = Ingredient::query()
            ->where('name', $name)
            ->first();

        if (! $ingredient instanceof Ingredient) {
            $ingredient = Ingredient::query()
                ->where('slug', $seedSlug)
                ->first();
        }

        if (! $ingredient instanceof Ingredient) {
            return Ingredient::query()->create([
                'slug' => $seedSlug,
                'name' => $name,
                'sku' => 'ING-' . strtoupper($seedSlug),
                'unit' => $unit,
                'current_stock' => 50000,
                'minimum_stock' => 5000,
                'is_active' => true,
            ]);
        }

        $ingredient->update([
            'name' => $name,
            'sku' => $ingredient->sku ?: 'ING-' . strtoupper($seedSlug),
            'unit' => $unit,
            'current_stock' => max((float) $ingredient->current_stock, 50000),
            'minimum_stock' => max((float) $ingredient->minimum_stock, 5000),
            'is_active' => true,
        ]);

        return $ingredient->refresh();
    }

    private function normalizeStepType(string $stepType): string
    {
        return match ($stepType) {
            'folding', 'shaping' => 'preparation',
            default => $stepType,
        };
    }
}
