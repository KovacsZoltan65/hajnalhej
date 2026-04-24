<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductIngredient;
use App\Models\RecipeStep;
use Database\Seeders\Concerns\UsesSeededIngredients;
use Illuminate\Database\Seeder;

class SourdoughBeginnerRecipeSeeder extends Seeder
{
    use UsesSeededIngredients;

    public function run(): void
    {
        $category = Category::query()->updateOrCreate(
            ['slug' => 'kenyerek'],
            [
                'name' => 'Kenyerek',
                'description' => 'Kovászos és egyéb kenyerek.',
                'is_active' => true,
                'sort_order' => 1,
            ],
        );

        $product = Product::query()->updateOrCreate(
            ['slug' => 'egyszeru-kovaszos-feher-kenyer'],
            [
                'category_id' => $category->id,
                'name' => 'Egyszerű kovászos fehér kenyér',
                'short_description' => 'Kezdő szintű kovászos kenyér stabil időzítéssel és biztos sikerélménnyel.',
                'description' => 'Egyszerű, kezdőbarát kovászos fehér kenyér recept. A hangsúly az időzítésen, az aktív kovászon és a türelmes fermentáción van.',
                'price' => 2450,
                'is_active' => true,
                'is_featured' => true,
                'stock_status' => Product::STOCK_IN_STOCK,
                'image_path' => 'products/egyszeru-kovaszos-feher-kenyer.jpg',
                'sort_order' => 10,
            ],
        );

        $ingredients = [
            'bl80-kenyerliszt' => [
                'name' => 'BL80 kenyérliszt',
                'sku' => 'ING-BL80-KENYERLISZT',
                'unit' => 'g',
                'current_stock' => 25000,
                'minimum_stock' => 5000,
                'notes' => 'Alap liszt a kezdő kovászos fehér kenyérhez.',
            ],
            'viz' => [
                'name' => 'Víz',
                'sku' => 'ING-VIZ',
                'unit' => 'ml',
                'current_stock' => 50000,
                'minimum_stock' => 10000,
                'notes' => 'Szűrt vagy pihentetett víz ajánlott.',
            ],
            'aktiv-kovasz' => [
                'name' => 'Aktív kovász',
                'sku' => 'ING-AKTIV-KOVASZ',
                'unit' => 'g',
                'current_stock' => 3000,
                'minimum_stock' => 500,
                'notes' => 'Etetés után 4–8 órával, csúcson használandó.',
            ],
            'so' => [
                'name' => 'Só',
                'sku' => 'ING-SO',
                'unit' => 'g',
                'current_stock' => 5000,
                'minimum_stock' => 1000,
                'notes' => 'Finom szemcséjű só.',
            ],
        ];

        $ingredientModels = $this->seededIngredients(array_keys($ingredients));

        $recipeItems = [
            [
                'ingredient_slug' => 'bl80-kenyerliszt',
                'quantity' => 500,
                'sort_order' => 1,
                'notes' => '500 g BL80 vagy kenyérliszt',
            ],
            [
                'ingredient_slug' => 'viz',
                'quantity' => 350,
                'sort_order' => 2,
                'notes' => '350 g víz',
            ],
            [
                'ingredient_slug' => 'aktiv-kovasz',
                'quantity' => 100,
                'sort_order' => 3,
                'notes' => '100 g aktív kovász',
            ],
            [
                'ingredient_slug' => 'so',
                'quantity' => 10,
                'sort_order' => 4,
                'notes' => '10 g só',
            ],
        ];

        foreach ($recipeItems as $item) {
            $ingredient = $ingredientModels[$item['ingredient_slug']];

            ProductIngredient::query()->updateOrCreate(
                [
                    'product_id' => $product->id,
                    'ingredient_id' => $ingredient->id,
                ],
                [
                    'quantity' => $item['quantity'],
                    'sort_order' => $item['sort_order'],
                    'notes' => $item['notes'],
                ],
            );
        }

        if (class_exists(RecipeStep::class)) {
            $steps = [
                [
                    'title' => 'Kovász etetése',
                    'step_type' => 'preparation',
                    'description' => "30 g kovász + 60 g víz + 60 g liszt.\nHasználat előtt legyen kétszeresére nőve, buborékos és kellemes savanykás illatú.",
                    'duration_minutes' => 10,
                    'wait_minutes' => 480,
                    'temperature_celsius' => 24,
                    'sort_order' => 1,
                    'is_active' => true,
                ],
                [
                    'title' => 'Tészta keverés',
                    'step_type' => 'mixing',
                    'description' => 'Mindent összekeverni, kivéve a sót. Rövid autolízis indul.',
                    'duration_minutes' => 10,
                    'wait_minutes' => 30,
                    'temperature_celsius' => 24,
                    'sort_order' => 2,
                    'is_active' => true,
                ],
                [
                    'title' => 'Só hozzáadása',
                    'step_type' => 'mixing',
                    'description' => 'A sót a pihentetés után gyúrd bele a tésztába.',
                    'duration_minutes' => 5,
                    'wait_minutes' => 0,
                    'temperature_celsius' => 24,
                    'sort_order' => 3,
                    'is_active' => true,
                ],
                [
                    'title' => '1. stretch & fold',
                    'step_type' => 'resting',
                    'description' => 'Első húzás-hajtás.',
                    'duration_minutes' => 3,
                    'wait_minutes' => 30,
                    'temperature_celsius' => 24,
                    'sort_order' => 4,
                    'is_active' => true,
                ],
                [
                    'title' => '2. stretch & fold',
                    'step_type' => 'resting',
                    'description' => 'Második húzás-hajtás.',
                    'duration_minutes' => 3,
                    'wait_minutes' => 30,
                    'temperature_celsius' => 24,
                    'sort_order' => 5,
                    'is_active' => true,
                ],
                [
                    'title' => '3. stretch & fold',
                    'step_type' => 'resting',
                    'description' => 'Harmadik húzás-hajtás.',
                    'duration_minutes' => 3,
                    'wait_minutes' => 0,
                    'temperature_celsius' => 24,
                    'sort_order' => 6,
                    'is_active' => true,
                ],
                [
                    'title' => 'Bulk ferment',
                    'step_type' => 'proofing',
                    'description' => 'Letakarva pihen. Akkor jó, ha a tészta kb. 50%-ot nőtt.',
                    'duration_minutes' => 0,
                    'wait_minutes' => 240,
                    'temperature_celsius' => 24,
                    'sort_order' => 7,
                    'is_active' => true,
                ],
                [
                    'title' => 'Előformázás',
                    'step_type' => 'preparation',
                    'description' => 'Gömbölyítés, majd rövid padon pihenés.',
                    'duration_minutes' => 10,
                    'wait_minutes' => 20,
                    'temperature_celsius' => 24,
                    'sort_order' => 8,
                    'is_active' => true,
                ],
                [
                    'title' => 'Végső formázás',
                    'step_type' => 'preparation',
                    'description' => 'Kosárba vagy lisztezett tálba helyezés.',
                    'duration_minutes' => 10,
                    'wait_minutes' => 0,
                    'temperature_celsius' => 24,
                    'sort_order' => 9,
                    'is_active' => true,
                ],
                [
                    'title' => 'Hűtős kelesztés',
                    'step_type' => 'proofing',
                    'description' => 'Hűtőben 8–16 órát kel. Teszteléshez itt 12 órával seedeljük.',
                    'duration_minutes' => 0,
                    'wait_minutes' => 720,
                    'temperature_celsius' => 5,
                    'sort_order' => 10,
                    'is_active' => true,
                ],
                [
                    'title' => 'Sütő és lábas előmelegítése',
                    'step_type' => 'preparation',
                    'description' => 'Sütő + öntöttvas lábas előmelegítése 250°C-ra.',
                    'duration_minutes' => 30,
                    'wait_minutes' => 0,
                    'temperature_celsius' => 250,
                    'sort_order' => 11,
                    'is_active' => true,
                ],
                [
                    'title' => 'Sütés fedővel',
                    'step_type' => 'baking',
                    'description' => 'Bevágás után 20 perc sütés fedővel.',
                    'duration_minutes' => 20,
                    'wait_minutes' => 0,
                    'temperature_celsius' => 250,
                    'sort_order' => 12,
                    'is_active' => true,
                ],
                [
                    'title' => 'Sütés fedő nélkül',
                    'step_type' => 'baking',
                    'description' => 'További 25 perc sütés fedő nélkül 220°C-on.',
                    'duration_minutes' => 25,
                    'wait_minutes' => 0,
                    'temperature_celsius' => 220,
                    'sort_order' => 13,
                    'is_active' => true,
                ],
                [
                    'title' => 'Hűlés',
                    'step_type' => 'cooling',
                    'description' => 'Rácson hűlni hagyjuk szeletelés előtt.',
                    'duration_minutes' => 0,
                    'wait_minutes' => 60,
                    'temperature_celsius' => 22,
                    'sort_order' => 14,
                    'is_active' => true,
                ],
            ];

            foreach ($steps as $step) {
                RecipeStep::query()->updateOrCreate(
                    [
                        'product_id' => $product->id,
                        'sort_order' => $step['sort_order'],
                    ],
                    [
                        'title' => $step['title'],
                        'step_type' => $step['step_type'],
                        'description' => $step['description'],
                        'duration_minutes' => $step['duration_minutes'],
                        'wait_minutes' => $step['wait_minutes'],
                        'temperature_celsius' => $step['temperature_celsius'],
                        'is_active' => $step['is_active'],
                    ],
                );
            }
        }
    }
}
