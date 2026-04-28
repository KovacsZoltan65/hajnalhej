<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductIngredient;
use App\Models\RecipeStep;
use Database\Seeders\Concerns\UsesSeededIngredients;
use Illuminate\Database\Seeder;

class SourdoughNapoliPizzaSeeder extends Seeder
{
    use UsesSeededIngredients;

    public function run(): void
    {
        $category = Category::query()->updateOrCreate(
            ['slug' => 'kovaszos-pekseg'],
            [
                'name' => 'Kovászos Pékség',
                'description' => 'Kézműves kovászos pékáruk és pizzatészták.',
                'is_active' => true,
                'sort_order' => 20,
            ]
        );

        $ingredients = [
            '00-liszt' => [
                'name' => '00 liszt',
                'sku' => 'ING-00-LISZT',
                'unit' => 'g',
                'current_stock' => 50000,
                'minimum_stock' => 5000,
                'is_active' => true,
                'notes' => 'Finom őrlésű pizzaliszt nápolyi stílushoz.',
            ],
            'viz' => [
                'name' => 'Víz',
                'sku' => 'ING-VIZ',
                'unit' => 'ml',
                'current_stock' => 50000,
                'minimum_stock' => 10000,
                'is_active' => true,
                'notes' => 'Hideg vagy szobahőmérsékletű víz, a körülményektől függően.',
            ],
            'kovasz' => [
                'name' => 'Aktív kovász',
                'sku' => 'ING-AKTIV-KOVASZ',
                'unit' => 'g',
                'current_stock' => 5000,
                'minimum_stock' => 500,
                'is_active' => true,
                'notes' => 'Erős, friss, csúcson lévő kovász.',
            ],
            'so' => [
                'name' => 'Só',
                'sku' => 'ING-SO',
                'unit' => 'g',
                'current_stock' => 5000,
                'minimum_stock' => 1000,
                'is_active' => true,
                'notes' => 'Finom szemcséjű só.',
            ],
        ];

        $ingredientModels = $this->seededIngredients(array_keys($ingredients));

        $product = Product::query()->updateOrCreate(
            ['slug' => 'kovaszos-pizza-napolyi-stilus'],
            [
                'category_id' => $category->id,
                'name' => 'Kovászos Pizza (nápolyi stílus)',
                'short_description' => 'Hosszú, hideg fermentációjú kovászos pizzatészta.',
                'description' => 'Nápolyi ihletésű kovászos pizzatészta 24 órás hűtős érleléssel, otthoni sütőre optimalizálva.',
                'price' => 150,
                'image_path' => 'products/kovaszos-pizza-napolyi.jpg',
                'sort_order' => 40,
            ],
        );

        $recipeItems = [
            ['ingredient_slug' => '00-liszt', 'quantity' => 1000, 'sort_order' => 1, 'notes' => 'Alap pizzaliszt'],
            ['ingredient_slug' => 'viz', 'quantity' => 650, 'sort_order' => 2, 'notes' => 'Kb. 65% hidratáció'],
            ['ingredient_slug' => 'kovasz', 'quantity' => 200, 'sort_order' => 3, 'notes' => 'Aktív, erős kovász'],
            ['ingredient_slug' => 'so', 'quantity' => 25, 'sort_order' => 4, 'notes' => 'Ízesítés és tésztaszerkezet'],
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
                'title' => 'Előző nap 18:00 – dagasztás',
                'step_type' => 'mixing',
                'description' => 'Keverd össze a lisztet, vizet, kovászt és sót, majd dolgozd sima, rugalmas tésztává.',
                'duration_minutes' => 20,
                'wait_minutes' => 40,
                'temperature_celsius' => 24,
                'sort_order' => 1,
                'work_instruction' => 'Mérd ki az alapanyagokat, majd fokozatosan dolgozd össze a tésztát. Dagaszd, amíg sima és rugalmas nem lesz.',
                'completion_criteria' => 'A tészta homogén, rugalmas és enyhén feszes felületű.',
                'attention_points' => 'Ne melegítsd túl dagasztás közben. A túl meleg tészta gyorsabban fermentál.',
                'required_tools' => 'digitális mérleg, dagasztótál, kéz vagy dagasztógép',
                'expected_result' => 'Egységes, jól kidolgozott pizzatészta.',
                'is_active' => true,
            ],
            [
                'title' => 'Előző nap 19:00 – hajtás',
                'step_type' => 'resting',
                'description' => 'Egy megerősítő hajtás a szerkezet javításához.',
                'duration_minutes' => 10,
                'wait_minutes' => 50,
                'temperature_celsius' => 24,
                'sort_order' => 2,
                'work_instruction' => 'Nedves kézzel emeld meg a tészta egyik oldalát, majd hajtsd középre. Ismételd meg több irányból.',
                'completion_criteria' => 'A tészta feszesebb, jobban tartja a formáját.',
                'attention_points' => 'Ne szakítsd a tésztát, csak finoman erősítsd meg.',
                'required_tools' => 'tál, nedves kéz vagy spatula',
                'expected_result' => 'Jobb tartású, erősebb gluténháló.',
                'is_active' => true,
            ],
            [
                'title' => 'Előző nap 20:00 – gombócok készítése',
                'step_type' => 'preparation',
                'description' => 'Oszd a tésztát egyenlő részekre, majd formálj feszes pizzagombócokat.',
                'duration_minutes' => 20,
                'wait_minutes' => 1440,
                'temperature_celsius' => 5,
                'sort_order' => 3,
                'work_instruction' => 'Oszd a tésztát kívánt adagokra, majd húzd feszesre a felületüket és tedd dobozba vagy tálcára.',
                'completion_criteria' => 'A gombócok sima felületűek, tartják a formájukat, hűtőbe kerültek.',
                'attention_points' => 'A gombócok között hagyj helyet. Hűtőben 24 órát pihennek.',
                'required_tools' => 'kaparó, mérleg, pizzás doboz vagy fedeles tálca',
                'expected_result' => 'Hideg fermentációra előkészített pizzagombócok.',
                'is_active' => true,
            ],
            [
                'title' => 'Másnap 18:00 – kivétel a hűtőből',
                'step_type' => 'resting',
                'description' => 'A pizzagombócokat vedd ki időben, hogy visszamelegedjenek és lazuljanak.',
                'duration_minutes' => 5,
                'wait_minutes' => 115,
                'temperature_celsius' => 22,
                'sort_order' => 4,
                'work_instruction' => 'Vedd ki a gombócokat a hűtőből, hagyd őket letakarva szobahőn akklimatizálódni.',
                'completion_criteria' => 'A tészta lazább, nyújthatóbb, nem jéghideg.',
                'attention_points' => 'Ne száradjon ki a felület. Tartsd letakarva.',
                'required_tools' => 'fedő vagy fólia, munkapult',
                'expected_result' => 'Nyújtásra kész pizzagombócok.',
                'is_active' => true,
            ],
            [
                'title' => 'Másnap 20:00 – sütés',
                'step_type' => 'baking',
                'description' => 'Nyújtás, feltétezés és sütés forró sütőben.',
                'duration_minutes' => 20,
                'wait_minutes' => 0,
                'temperature_celsius' => 250,
                'sort_order' => 5,
                'work_instruction' => 'Nyújtsd ki kézzel a tésztát, helyezd rá a feltétet, majd süsd előmelegített acélon vagy kövön a lehető legforróbb sütőben.',
                'completion_criteria' => 'A perem felpúposodott, a pizza alja és széle szépen megsült.',
                'attention_points' => '250°C-os otthoni sütőben az előmelegített acél lap sokat javít az eredményen.',
                'required_tools' => 'pizzalapát, sütőacél vagy pizzakő, sütő',
                'expected_result' => 'Kész, nápolyi stílusú kovászos pizza.',
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
                    ...$step,
                    'step_type' => $this->normalizeStepType((string) $step['step_type']),
                ],
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
