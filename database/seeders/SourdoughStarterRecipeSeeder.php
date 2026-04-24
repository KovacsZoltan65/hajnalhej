<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductIngredient;
use App\Models\RecipeStep;
use Database\Seeders\Concerns\UsesSeededIngredients;
use Illuminate\Database\Seeder;

class SourdoughStarterRecipeSeeder extends Seeder
{
    use UsesSeededIngredients;

    public function run(): void
    {
        $category = Category::query()->updateOrCreate(
            ['slug' => 'elokeszitok'],
            [
                'name' => 'Előkészítők',
                'description' => 'Kovászok, előtészták, prefermentek.',
                'is_active' => true,
                'sort_order' => 5,
            ],
        );

        $product = Product::query()->updateOrCreate(
            ['slug' => 'aktiv-buzakovasz-100-hidracio'],
            [
                'category_id' => $category->id,
                'name' => 'Aktív búzakovász (100% hidratáció)',
                'short_description' => 'Erős, aktív kovász kenyerekhez és péksüteményekhez.',
                'description' => 'Frissen etetett, buborékos, duplázódó búzakovász. Ideális kenyér receptekhez.',
                'price' => 0,
                'image_path' => 'products/aktiv-buzakovasz.jpg',
                'sort_order' => 50,
            ],
        );

        $ingredients = [
            'buzaliszt' => [
                'name' => 'Búzaliszt',
                'sku' => 'ING-BUZALISZT',
                'unit' => 'g',
                'current_stock' => 30000,
                'minimum_stock' => 5000,
                'notes' => 'Általános búzaliszt kovász etetéshez.',
            ],
            'viz' => [
                'name' => 'Víz',
                'sku' => 'ING-VIZ',
                'unit' => 'ml',
                'current_stock' => 50000,
                'minimum_stock' => 10000,
                'notes' => 'Langyos víz ajánlott.',
            ],
            'anyakovasz' => [
                'name' => 'Anyakovász',
                'sku' => 'ING-ANYAKOVASZ',
                'unit' => 'g',
                'current_stock' => 500,
                'minimum_stock' => 100,
                'notes' => 'Már élő kovászmag.',
            ],
        ];

        $ingredientModels = $this->seededIngredients(array_keys($ingredients));

        $recipeItems = [
            [
                'ingredient_slug' => 'anyakovasz',
                'quantity' => 30,
                'sort_order' => 1,
                'notes' => '30 g aktív magkovász',
            ],
            [
                'ingredient_slug' => 'viz',
                'quantity' => 60,
                'sort_order' => 2,
                'notes' => '60 g langyos víz',
            ],
            [
                'ingredient_slug' => 'buzaliszt',
                'quantity' => 60,
                'sort_order' => 3,
                'notes' => '60 g búzaliszt',
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

        $steps = [
            [
                'title' => 'Alapanyagok kimérése',
                'step_type' => 'preparation',
                'description' => 'Mérj ki 30 g anyakovászt, 60 g vizet és 60 g lisztet.',
                'duration_minutes' => 5,
                'wait_minutes' => 0,
                'temperature_celsius' => 24,
                'sort_order' => 1,
            ],
            [
                'title' => 'Bekeverés',
                'step_type' => 'mixing',
                'description' => 'Alaposan keverd össze homogén állagúra.',
                'duration_minutes' => 5,
                'wait_minutes' => 0,
                'temperature_celsius' => 24,
                'sort_order' => 2,
            ],
            [
                'title' => 'Fermentáció',
                'step_type' => 'proofing',
                'description' => 'Szobahőmérsékleten hagyd kelni, amíg duplázódik és buborékos lesz.',
                'duration_minutes' => 0,
                'wait_minutes' => 360,
                'temperature_celsius' => 24,
                'sort_order' => 3,
            ],
            [
                'title' => 'Csúcspont használat',
                'step_type' => 'finishing',
                'description' => '4–8 órán belül használd fel kenyérhez vagy etesd újra.',
                'duration_minutes' => 0,
                'wait_minutes' => 120,
                'temperature_celsius' => 24,
                'sort_order' => 4,
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
                    'is_active' => true,
                ],
            );
        }
    }
}
