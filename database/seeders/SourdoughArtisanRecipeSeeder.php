<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Ingredient;
use App\Models\Product;
use App\Models\ProductIngredient;
use App\Models\RecipeStep;
use Illuminate\Database\Seeder;

class SourdoughArtisanRecipeSeeder extends Seeder
{
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
            ['slug' => 'magas-hidracioju-kezmuves-kenyer'],
            [
                'category_id' => $category->id,
                'name' => 'Magas hidratációjú kezmuves kenyér',
                'short_description' => 'Haladó kovászos kenyér magas hidratációval, nyitott bélzettel és roppanós héjjal.',
                'description' => 'Haladó szintű kezmuves kovászos kenyér. Magas hidratáció, hosszabb fermentáció és óvatos kezelés szükséges a nyitott, levegős szerkezethez.',
                'price' => 2890,
                'image_path' => 'products/magas-hidracioju-kezmuves-kenyer.jpg',
                'sort_order' => 20,
            ],
        );

        $ingredients = [
            'kenyerliszt' => [
                'name' => 'Kenyérliszt',
                'sku' => 'ING-KENYERLISZT',
                'unit' => 'g',
                'current_stock' => 25000,
                'minimum_stock' => 5000,
                'notes' => 'Erősebb liszt magas hidratációhoz.',
            ],
            'teljes-kiorlesu-liszt' => [
                'name' => 'Teljes kiőrlésű liszt',
                'sku' => 'ING-TELJES-KIORLESU-LISZT',
                'unit' => 'g',
                'current_stock' => 10000,
                'minimum_stock' => 2000,
                'notes' => 'Ízmélységet és fermentációs aktivitást ad.',
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
                'notes' => 'Erős, csúcson lévő kovász szükséges.',
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

        $ingredientModels = [];

        foreach ($ingredients as $slug => $data) {
            $ingredientModels[$slug] = Ingredient::query()->updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $data['name'],
                    'sku' => $data['sku'],
                    'unit' => $data['unit'],
                    'current_stock' => $data['current_stock'],
                    'minimum_stock' => $data['minimum_stock'],
                    'is_active' => true,
                    'notes' => $data['notes'],
                ],
            );
        }

        $recipeItems = [
            [
                'ingredient_slug' => 'kenyerliszt',
                'quantity' => 450,
                'sort_order' => 1,
                'notes' => '450 g kenyérliszt',
            ],
            [
                'ingredient_slug' => 'teljes-kiorlesu-liszt',
                'quantity' => 50,
                'sort_order' => 2,
                'notes' => '50 g teljes kiőrlésű liszt',
            ],
            [
                'ingredient_slug' => 'viz',
                'quantity' => 390,
                'sort_order' => 3,
                'notes' => '390 g víz',
            ],
            [
                'ingredient_slug' => 'aktiv-kovasz',
                'quantity' => 100,
                'sort_order' => 4,
                'notes' => '100 g aktív kovász',
            ],
            [
                'ingredient_slug' => 'so',
                'quantity' => 11,
                'sort_order' => 5,
                'notes' => '11 g só',
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
                'title' => 'Kovász etetése',
                'step_type' => 'preparation',
                'description' => 'Előző este 22:00-kor etesd meg a kovászt. Ehhez a recepthez erős, aktív kovász kell.',
                'duration_minutes' => 10,
                'wait_minutes' => 600,
                'temperature_celsius' => 24,
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'title' => 'Autolízis',
                'step_type' => 'mixing',
                'description' => 'A liszteket és a vizet keverd össze, majd hagyd pihenni 1 órát.',
                'duration_minutes' => 10,
                'wait_minutes' => 60,
                'temperature_celsius' => 24,
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'title' => 'Kovász és só hozzáadása',
                'step_type' => 'mixing',
                'description' => 'Az autolízis után add hozzá az aktív kovászt és a sót.',
                'duration_minutes' => 10,
                'wait_minutes' => 30,
                'temperature_celsius' => 24,
                'sort_order' => 3,
                'is_active' => true,
            ],
            [
                'title' => '1. coil fold',
                'step_type' => 'resting',
                'description' => 'Első coil fold kör.',
                'duration_minutes' => 3,
                'wait_minutes' => 30,
                'temperature_celsius' => 24,
                'sort_order' => 4,
                'is_active' => true,
            ],
            [
                'title' => '2. coil fold',
                'step_type' => 'resting',
                'description' => 'Második coil fold kör.',
                'duration_minutes' => 3,
                'wait_minutes' => 30,
                'temperature_celsius' => 24,
                'sort_order' => 5,
                'is_active' => true,
            ],
            [
                'title' => '3. coil fold',
                'step_type' => 'resting',
                'description' => 'Harmadik coil fold kör.',
                'duration_minutes' => 3,
                'wait_minutes' => 30,
                'temperature_celsius' => 24,
                'sort_order' => 6,
                'is_active' => true,
            ],
            [
                'title' => '4. coil fold',
                'step_type' => 'resting',
                'description' => 'Negyedik coil fold kör.',
                'duration_minutes' => 3,
                'wait_minutes' => 0,
                'temperature_celsius' => 24,
                'sort_order' => 7,
                'is_active' => true,
            ],
            [
                'title' => 'Bulk ferment',
                'step_type' => 'proofing',
                'description' => '11:30–15:30 között bulk ferment, amíg levegős, rezgős és puha lesz.',
                'duration_minutes' => 0,
                'wait_minutes' => 240,
                'temperature_celsius' => 24,
                'sort_order' => 8,
                'is_active' => true,
            ],
            [
                'title' => 'Óvatos formázás',
                'step_type' => 'preparation',
                'description' => 'A tésztát óvatosan formázd, hogy a szerkezete megmaradjon.',
                'duration_minutes' => 15,
                'wait_minutes' => 30,
                'temperature_celsius' => 24,
                'sort_order' => 9,
                'is_active' => true,
            ],
            [
                'title' => 'Hideg kelesztés',
                'step_type' => 'proofing',
                'description' => '16:00-tól másnap reggelig. Seedhez 14 órával számolunk.',
                'duration_minutes' => 0,
                'wait_minutes' => 840,
                'temperature_celsius' => 5,
                'sort_order' => 10,
                'is_active' => true,
            ],
            [
                'title' => 'Sütő és lábas előmelegítése',
                'step_type' => 'preparation',
                'description' => 'A sütőt és a lábast melegítsd elő 250°C-ra.',
                'duration_minutes' => 30,
                'wait_minutes' => 0,
                'temperature_celsius' => 250,
                'sort_order' => 11,
                'is_active' => true,
            ],
            [
                'title' => 'Sütés fedővel',
                'step_type' => 'baking',
                'description' => '22 perc sütés fedővel 250°C-on.',
                'duration_minutes' => 22,
                'wait_minutes' => 0,
                'temperature_celsius' => 250,
                'sort_order' => 12,
                'is_active' => true,
            ],
            [
                'title' => 'Sütés fedő nélkül',
                'step_type' => 'baking',
                'description' => '25 perc sütés fedő nélkül 220°C-on.',
                'duration_minutes' => 25,
                'wait_minutes' => 0,
                'temperature_celsius' => 220,
                'sort_order' => 13,
                'is_active' => true,
            ],
            [
                'title' => 'Hűlés',
                'step_type' => 'cooling',
                'description' => 'Rácson hűlni hagyjuk a szeletelés előtt.',
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