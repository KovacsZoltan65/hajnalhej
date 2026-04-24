<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductIngredient;
use App\Models\RecipeStep;
use Database\Seeders\Concerns\UsesSeededIngredients;
use Illuminate\Database\Seeder;

class SourdoughCocoaRollSeeder extends Seeder
{
    use UsesSeededIngredients;

    public function run(): void
    {
        $category = Category::query()->updateOrCreate(
            ['slug' => 'edes-pekseg'],
            [
                'name' => 'Édes Pékség',
                'description' => 'Kovászos édes péksütemények.',
                'is_active' => true,
                'sort_order' => 30,
            ]
        );

        $ingredients = [
            'liszt' => [
                'name' => 'Búzaliszt',
                'sku' => 'ING-LISZT',
                'unit' => 'g',
                'current_stock' => 50000,
                'minimum_stock' => 5000,
                'is_active' => true,
                'notes' => 'Finomliszt vagy kenyérliszt dúsított tésztához.',
            ],
            'tej' => [
                'name' => 'Tej',
                'sku' => 'ING-TEJ',
                'unit' => 'ml',
                'current_stock' => 20000,
                'minimum_stock' => 2000,
                'is_active' => true,
                'notes' => 'Langyos tej a tészta lazításához.',
            ],
            'kovasz' => [
                'name' => 'Aktív kovász',
                'sku' => 'ING-AKTIV-KOVASZ',
                'unit' => 'g',
                'current_stock' => 5000,
                'minimum_stock' => 500,
                'is_active' => true,
                'notes' => 'Erős, friss kovász.',
            ],
            'vaj' => [
                'name' => 'Vaj',
                'sku' => 'ING-VAJ',
                'unit' => 'g',
                'current_stock' => 10000,
                'minimum_stock' => 1000,
                'is_active' => true,
                'notes' => 'Tésztához és töltelékhez is.',
            ],
            'cukor' => [
                'name' => 'Kristálycukor',
                'sku' => 'ING-CUKOR',
                'unit' => 'g',
                'current_stock' => 10000,
                'minimum_stock' => 1000,
                'is_active' => true,
                'notes' => 'Tésztához.',
            ],
            'barna-cukor' => [
                'name' => 'Barna cukor',
                'sku' => 'ING-BARNA-CUKOR',
                'unit' => 'g',
                'current_stock' => 8000,
                'minimum_stock' => 1000,
                'is_active' => true,
                'notes' => 'Töltelékhez.',
            ],
            'tojas' => [
                'name' => 'Tojás',
                'sku' => 'ING-TOJAS',
                'unit' => 'db',
                'current_stock' => 200,
                'minimum_stock' => 20,
                'is_active' => true,
                'notes' => 'A tésztához 1 db tojás.',
            ],
            'so' => [
                'name' => 'Só',
                'sku' => 'ING-SO',
                'unit' => 'g',
                'current_stock' => 5000,
                'minimum_stock' => 1000,
                'is_active' => true,
                'notes' => 'Ízesítéshez.',
            ],
            'kakao' => [
                'name' => 'Holland kakaópor',
                'sku' => 'ING-KAKAO',
                'unit' => 'g',
                'current_stock' => 5000,
                'minimum_stock' => 500,
                'is_active' => true,
                'notes' => 'Töltelékhez.',
            ],
        ];

        $ingredientModels = $this->seededIngredients(array_keys($ingredients));

        $product = Product::query()->updateOrCreate(
            ['slug' => 'kakaos-csiga'],
            [
                'category_id' => $category->id,
                'name' => 'Kakaós Csiga',
                'short_description' => 'Kovászos, vajas kakaós csiga éjszakai hideg pihentetéssel.',
                'description' => 'Dúsított kovászos tészta, kakaós-vajas-barna cukros töltelékkel. Kiváló teszt a plannernek, mert esti indításból reggeli sütést modellez.',
                'price' => 0,
                'image_path' => 'products/kakaos-csiga.jpg',
                'sort_order' => 50,
            ],
        );

        /*
         * A BOM itt a teljes recept összesített felhasználását tartalmazza:
         * tészta + töltelék együtt.
         */
        $recipeItems = [
            ['ingredient_slug' => 'liszt', 'quantity' => 500, 'sort_order' => 1, 'notes' => 'Tészta alapja'],
            ['ingredient_slug' => 'tej', 'quantity' => 220, 'sort_order' => 2, 'notes' => 'Tésztához'],
            ['ingredient_slug' => 'kovasz', 'quantity' => 100, 'sort_order' => 3, 'notes' => 'Aktív kovász'],
            ['ingredient_slug' => 'vaj', 'quantity' => 140, 'sort_order' => 4, 'notes' => '80 g a tésztához + 60 g a töltelékhez'],
            ['ingredient_slug' => 'cukor', 'quantity' => 80, 'sort_order' => 5, 'notes' => 'Tésztához'],
            ['ingredient_slug' => 'tojas', 'quantity' => 1, 'sort_order' => 6, 'notes' => '1 db tojás a tésztához'],
            ['ingredient_slug' => 'so', 'quantity' => 8, 'sort_order' => 7, 'notes' => 'Tésztához'],
            ['ingredient_slug' => 'kakao', 'quantity' => 30, 'sort_order' => 8, 'notes' => 'Töltelékhez'],
            ['ingredient_slug' => 'barna-cukor', 'quantity' => 70, 'sort_order' => 9, 'notes' => 'Töltelékhez'],
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
                'title' => 'Este 20:00 – dagasztás',
                'step_type' => 'mixing',
                'description' => 'A liszt, tej, kovász, vaj, cukor, tojás és só összedolgozása sima, puha tésztává.',
                'duration_minutes' => 25,
                'wait_minutes' => 120,
                'temperature_celsius' => 24,
                'sort_order' => 1,
                'work_instruction' => 'Mérd ki az alapanyagokat, majd dagaszd sima, rugalmas, enyhén lágy tésztává. A vajat fokozatosan dolgozd bele.',
                'completion_criteria' => 'A tészta sima, fényes, rugalmas és elválik az edény falától.',
                'attention_points' => 'A dúsított tészta lassabban erősödik. Ne lisztezd túl dagasztás közben.',
                'required_tools' => 'digitális mérleg, dagasztótál, dagasztógép vagy kéz',
                'expected_result' => 'Jól kidolgozott, puha kakaós csiga tészta.',
                'is_active' => true,
            ],
            [
                'title' => 'Éjjel – szobahős pihenés',
                'step_type' => 'proofing',
                'description' => 'A tészta 2 órát szobahőn pihen, hogy elinduljon a fermentáció.',
                'duration_minutes' => 5,
                'wait_minutes' => 115,
                'temperature_celsius' => 24,
                'sort_order' => 2,
                'work_instruction' => 'Takard le a tésztát, és hagyd szobahőmérsékleten 2 órán át pihenni.',
                'completion_criteria' => 'A tészta enyhén megindult, puhább és levegősebb lett.',
                'attention_points' => 'Nem kell teljes duplázódást várni, csak az induló aktivitás a cél.',
                'required_tools' => 'kelesztőtál, fedő vagy fólia',
                'expected_result' => 'Hideg pihentetésre előkészített, fermentációt megkezdett tészta.',
                'is_active' => true,
            ],
            [
                'title' => 'Éjjel – hűtős pihenés',
                'step_type' => 'proofing',
                'description' => 'A tészta éjszakára hűtőbe kerül.',
                'duration_minutes' => 5,
                'wait_minutes' => 480,
                'temperature_celsius' => 5,
                'sort_order' => 3,
                'work_instruction' => 'A tésztát lefedve tedd hűtőbe éjszakára.',
                'completion_criteria' => 'A tészta biztonságosan hűtőben pihen.',
                'attention_points' => 'A hideg tészta reggel könnyebben nyújtható és tölthető.',
                'required_tools' => 'hűtőszekrény, fedeles doboz vagy tál',
                'expected_result' => 'Másnap reggelre jól kezelhető, hideg tészta.',
                'is_active' => true,
            ],
            [
                'title' => 'Reggel 07:00 – nyújtás, töltés, tekerés',
                'step_type' => 'preparation',
                'description' => 'A tésztát kinyújtjuk, megkenjük vajas-kakaós-barna cukros töltelékkel, majd feltekerjük és szeleteljük.',
                'duration_minutes' => 35,
                'wait_minutes' => 25,
                'temperature_celsius' => 22,
                'sort_order' => 4,
                'work_instruction' => 'Nyújtsd téglalappá a tésztát, kend meg puha vajjal, szórd meg kakaó és barna cukor keverékével, majd tekerd fel és szeleteld csigákra.',
                'completion_criteria' => 'A csigák egyenletesen feltekerve, tepsire rendezve várják a kelést.',
                'attention_points' => 'Ne lisztezd túl a felületet, és ne tekerd túl lazára a rudat.',
                'required_tools' => 'nyújtófa, spatula, kés vagy cérna, tepsi',
                'expected_result' => 'Formázott kakaós csigák sütés előtti állapotban.',
                'is_active' => true,
            ],
            [
                'title' => 'Reggel 08:00 – kelés',
                'step_type' => 'proofing',
                'description' => 'A formázott csigák végső kelése sütés előtt.',
                'duration_minutes' => 5,
                'wait_minutes' => 55,
                'temperature_celsius' => 24,
                'sort_order' => 5,
                'work_instruction' => 'Takard le a csigákat, és hagyd őket meleg helyen kelni, amíg láthatóan puhák és levegősek lesznek.',
                'completion_criteria' => 'A csigák enyhén megemelkedtek, puhák, kissé rezgősek.',
                'attention_points' => 'Túlkelésnél szétlapulhatnak, alulkelésnél tömörebbek maradnak.',
                'required_tools' => 'tepsi, konyharuha vagy fólia',
                'expected_result' => 'Sütésre kész, megkelt kakaós csigák.',
                'is_active' => true,
            ],
            [
                'title' => 'Reggel 09:00 – sütés',
                'step_type' => 'baking',
                'description' => 'A csigák aranybarnára sütése.',
                'duration_minutes' => 25,
                'wait_minutes' => 0,
                'temperature_celsius' => 190,
                'sort_order' => 6,
                'work_instruction' => 'Süsd a csigákat előmelegített sütőben, amíg aranybarnák lesznek és a töltelék enyhén karamellizálódik.',
                'completion_criteria' => 'A csigák megsültek, a szélek enyhén karamellizáltak, belül puhák maradtak.',
                'attention_points' => 'Ne süsd túl, mert kiszáradhatnak.',
                'required_tools' => 'sütő, tepsi',
                'expected_result' => 'Kész kakaós csigák tálalásra vagy csomagolásra.',
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
