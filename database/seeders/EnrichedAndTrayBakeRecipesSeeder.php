<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductIngredient;
use App\Models\RecipeStep;
use Database\Seeders\Concerns\UsesSeededIngredients;
use Illuminate\Database\Seeder;

class EnrichedAndTrayBakeRecipesSeeder extends Seeder
{
    use UsesSeededIngredients;

    public function run(): void
    {
        $sweetCategory = Category::query()->updateOrCreate(
            ['slug' => 'edes-pekseg'],
            [
                'name' => 'Édes Pékség',
                'description' => 'Kovászos édes péksütemények.',
                'is_active' => true,
                'sort_order' => 30,
            ]
        );

        $savoryCategory = Category::query()->updateOrCreate(
            ['slug' => 'sos-pekseg'],
            [
                'name' => 'Sós Pékség',
                'description' => 'Kovászos sós pékáruk és tepsis kenyerek.',
                'is_active' => true,
                'sort_order' => 31,
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
                'notes' => 'Alap liszt péksüteményekhez.',
            ],
            'tojas' => [
                'name' => 'Tojás',
                'sku' => 'ING-TOJAS',
                'unit' => 'g',
                'current_stock' => 12000,
                'minimum_stock' => 1000,
                'is_active' => true,
                'notes' => 'Felvert tojás grammban kezelve a jobb skálázhatóságért.',
            ],
            'tej' => [
                'name' => 'Tej',
                'sku' => 'ING-TEJ',
                'unit' => 'ml',
                'current_stock' => 20000,
                'minimum_stock' => 2000,
                'is_active' => true,
                'notes' => 'Langyos tej a dúsított tésztákhoz.',
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
                'current_stock' => 12000,
                'minimum_stock' => 1000,
                'is_active' => true,
                'notes' => 'Magas zsírtartalmú vaj ajánlott.',
            ],
            'cukor' => [
                'name' => 'Kristálycukor',
                'sku' => 'ING-CUKOR',
                'unit' => 'g',
                'current_stock' => 10000,
                'minimum_stock' => 1000,
                'is_active' => true,
                'notes' => 'Édes tésztákhoz.',
            ],
            'so' => [
                'name' => 'Só',
                'sku' => 'ING-SO',
                'unit' => 'g',
                'current_stock' => 5000,
                'minimum_stock' => 1000,
                'is_active' => true,
                'notes' => 'Általános só.',
            ],
            'viz' => [
                'name' => 'Víz',
                'sku' => 'ING-VIZ',
                'unit' => 'ml',
                'current_stock' => 50000,
                'minimum_stock' => 10000,
                'is_active' => true,
                'notes' => 'Tiszta víz.',
            ],
            'olivaolaj' => [
                'name' => 'Olívaolaj',
                'sku' => 'ING-OLIVAOLAJ',
                'unit' => 'g',
                'current_stock' => 8000,
                'minimum_stock' => 800,
                'is_active' => true,
                'notes' => 'Focacciához és tepsi kikenéshez is.',
            ],
            'rozmaring' => [
                'name' => 'Rozmaring',
                'sku' => 'ING-ROZMARING',
                'unit' => 'g',
                'current_stock' => 1000,
                'minimum_stock' => 100,
                'is_active' => true,
                'notes' => 'Friss vagy szárított.',
            ],
            'paradicsom' => [
                'name' => 'Paradicsom',
                'sku' => 'ING-PARADICSOM',
                'unit' => 'g',
                'current_stock' => 5000,
                'minimum_stock' => 500,
                'is_active' => true,
                'notes' => 'Feltétként.',
            ],
            'fokhagyma' => [
                'name' => 'Fokhagyma',
                'sku' => 'ING-FOKHAGYMA',
                'unit' => 'g',
                'current_stock' => 2000,
                'minimum_stock' => 200,
                'is_active' => true,
                'notes' => 'Feltétként vagy olajba keverve.',
            ],
        ];

        $ingredientModels = $this->seededIngredients(array_keys($ingredients));

        $this->seedBrios($sweetCategory, $ingredientModels);
        $this->seedFocaccia($savoryCategory, $ingredientModels);
    }

    private function seedBrios(Category $category, array $ingredientModels): void
    {
        $product = Product::query()->updateOrCreate(
            ['slug' => 'brios'],
            [
                'category_id' => $category->id,
                'name' => 'Briós',
                'short_description' => 'Dúsított, vajas, kovászos briós másnapi fonással.',
                'description' => 'Gazdag, tojásos-vajas tészta éjszakai hűtéssel, másnapi fonással és hosszú végső keléssel.',
                'price' => 0,
                'image_path' => 'products/brios.jpg',
                'sort_order' => 60,
            ],
        );

        $recipeItems = [
            ['ingredient_slug' => 'liszt', 'quantity' => 500, 'sort_order' => 1, 'notes' => 'Alap liszt'],
            ['ingredient_slug' => 'tojas', 'quantity' => 220, 'sort_order' => 2, 'notes' => 'Tojás grammban mérve'],
            ['ingredient_slug' => 'tej', 'quantity' => 120, 'sort_order' => 3, 'notes' => 'Lágyítja a tésztát'],
            ['ingredient_slug' => 'kovasz', 'quantity' => 100, 'sort_order' => 4, 'notes' => 'Aktív kovász'],
            ['ingredient_slug' => 'vaj', 'quantity' => 180, 'sort_order' => 5, 'notes' => 'Hideg vagy kissé puha vaj'],
            ['ingredient_slug' => 'cukor', 'quantity' => 70, 'sort_order' => 6, 'notes' => 'Édesítés'],
            ['ingredient_slug' => 'so', 'quantity' => 8, 'sort_order' => 7, 'notes' => 'Ízesítés'],
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
                'title' => 'Este – dagasztás',
                'step_type' => 'mixing',
                'description' => 'A liszt, tojás, tej, kovász, cukor és só összedolgozása, majd a vaj fokozatos beledolgozása.',
                'duration_minutes' => 30,
                'wait_minutes' => 30,
                'temperature_celsius' => 24,
                'sort_order' => 1,
                'work_instruction' => 'Először a vaj kivételével dolgozd össze az alapanyagokat, majd több részletben add hozzá a vajat. Dagaszd sima, fényes tésztává.',
                'completion_criteria' => 'A tészta rugalmas, fényes, puha és jól összetart.',
                'attention_points' => 'Ez a tészta ragadós. Normális. Nem lázadt fel ellened. Ne lisztezd túl.',
                'required_tools' => 'digitális mérleg, dagasztótál, dagasztógép vagy kéz',
                'expected_result' => 'Jól kidolgozott brióstészta.',
                'is_active' => true,
            ],
            [
                'title' => 'Este – rövid szobahős pihenés',
                'step_type' => 'proofing',
                'description' => 'A tészta rövid ideig szobahőn indul el, mielőtt hűtőbe kerül.',
                'duration_minutes' => 5,
                'wait_minutes' => 55,
                'temperature_celsius' => 24,
                'sort_order' => 2,
                'work_instruction' => 'Takard le a tésztát, és hagyd kicsit megindulni szobahőn.',
                'completion_criteria' => 'A tészta kissé ellazult és enyhén megindult.',
                'attention_points' => 'Nem cél a teljes megkelés, csak az indulás.',
                'required_tools' => 'kelesztőtál, fedő vagy fólia',
                'expected_result' => 'Hűtőzésre előkészített tészta.',
                'is_active' => true,
            ],
            [
                'title' => 'Éjjel – hűtő',
                'step_type' => 'proofing',
                'description' => 'Éjszakai hideg pihentetés a jobb kezelhetőségért és ízért.',
                'duration_minutes' => 5,
                'wait_minutes' => 540,
                'temperature_celsius' => 5,
                'sort_order' => 3,
                'work_instruction' => 'Tedd a letakart tésztát hűtőbe éjszakára.',
                'completion_criteria' => 'A tészta stabilan, hidegen pihen a hűtőben.',
                'attention_points' => 'A hideg tésztát másnap könnyebb fonni.',
                'required_tools' => 'hűtőszekrény, fedeles doboz vagy tál',
                'expected_result' => 'Másnap fonható brióstészta.',
                'is_active' => true,
            ],
            [
                'title' => 'Másnap – osztás és fonás',
                'step_type' => 'preparation',
                'description' => 'A hideg tésztát osztjuk, sodorjuk, majd fonjuk.',
                'duration_minutes' => 35,
                'wait_minutes' => 25,
                'temperature_celsius' => 22,
                'sort_order' => 4,
                'work_instruction' => 'Oszd a tésztát egyenlő részekre, sodorj belőlük rudakat, majd fond meg a brióst a kívánt formára.',
                'completion_criteria' => 'Az egyenletesen megfont briós formázva, sütőformába vagy tepsire helyezve.',
                'attention_points' => 'A hideg tészta még feszesebb lehet, dolgozz türelmesen.',
                'required_tools' => 'mérleg, kaparó, munkapult, forma vagy tepsi',
                'expected_result' => 'Formázott briós végső kelésre készen.',
                'is_active' => true,
            ],
            [
                'title' => 'Másnap – 3 óra kelés',
                'step_type' => 'proofing',
                'description' => 'Hosszú végső kelés a dúsított tészta miatt.',
                'duration_minutes' => 5,
                'wait_minutes' => 175,
                'temperature_celsius' => 24,
                'sort_order' => 5,
                'work_instruction' => 'Takard le a formázott brióst, és hagyd meleg helyen kelni kb. 3 órát.',
                'completion_criteria' => 'A briós láthatóan megnőtt, puha, rezgős, de még nem esik össze.',
                'attention_points' => 'A dúsított tészta lassú. Ne sürgesd túl meleggel.',
                'required_tools' => 'fólia vagy konyharuha',
                'expected_result' => 'Sütésre kész, megkelt briós.',
                'is_active' => true,
            ],
            [
                'title' => 'Sütés 180°C',
                'step_type' => 'baking',
                'description' => 'A briós aranybarnára sütése 180°C-on.',
                'duration_minutes' => 30,
                'wait_minutes' => 0,
                'temperature_celsius' => 180,
                'sort_order' => 6,
                'work_instruction' => 'Süsd a brióst előmelegített sütőben, amíg mély aranybarna lesz és átsül.',
                'completion_criteria' => 'A briós szépen színeződött, átsült és stabil szerkezetű.',
                'attention_points' => 'Ha gyorsan barnul, lazán takard le sütés közben.',
                'required_tools' => 'sütő, forma vagy tepsi',
                'expected_result' => 'Kész, puha, vajas briós.',
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

    private function seedFocaccia(Category $category, array $ingredientModels): void
    {
        $product = Product::query()->updateOrCreate(
            ['slug' => 'focaccia'],
            [
                'category_id' => $category->id,
                'name' => 'Focaccia',
                'short_description' => 'Magas hidratációjú kovászos focaccia olívaolajjal.',
                'description' => 'Tepsis, levegős focaccia hosszú nappali fermentációval és gazdag topping lehetőségekkel.',
                'price' => 0,
                'image_path' => 'products/focaccia.jpg',
                'sort_order' => 61,
            ],
        );

        $recipeItems = [
            ['ingredient_slug' => 'liszt', 'quantity' => 500, 'sort_order' => 1, 'notes' => 'Alap liszt'],
            ['ingredient_slug' => 'viz', 'quantity' => 420, 'sort_order' => 2, 'notes' => 'Magas hidratáció'],
            ['ingredient_slug' => 'kovasz', 'quantity' => 100, 'sort_order' => 3, 'notes' => 'Aktív kovász'],
            ['ingredient_slug' => 'so', 'quantity' => 12, 'sort_order' => 4, 'notes' => 'Ízesítés'],
            ['ingredient_slug' => 'olivaolaj', 'quantity' => 40, 'sort_order' => 5, 'notes' => 'Tésztához, tepsihez és tetejére'],
            ['ingredient_slug' => 'rozmaring', 'quantity' => 5, 'sort_order' => 6, 'notes' => 'Opcionális topping'],
            ['ingredient_slug' => 'paradicsom', 'quantity' => 80, 'sort_order' => 7, 'notes' => 'Opcionális topping'],
            ['ingredient_slug' => 'fokhagyma', 'quantity' => 10, 'sort_order' => 8, 'notes' => 'Opcionális topping'],
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
                'title' => '08:00 – keverés',
                'step_type' => 'mixing',
                'description' => 'A liszt, víz, kovász és só összekeverése, majd olívaolaj hozzáadása.',
                'duration_minutes' => 15,
                'wait_minutes' => 45,
                'temperature_celsius' => 24,
                'sort_order' => 1,
                'work_instruction' => 'Keverd össze az alapanyagokat, majd dolgozd bele az olívaolajat. Nem kell teljesen simára dagasztani.',
                'completion_criteria' => 'Nedves, puha, homogén focaccia tészta.',
                'attention_points' => 'Ragacsos lesz. Ez jó jel.',
                'required_tools' => 'digitális mérleg, tál, spatula',
                'expected_result' => 'Első pihenésre kész focaccia tészta.',
                'is_active' => true,
            ],
            [
                'title' => '09:00 – hajtás #1',
                'step_type' => 'resting',
                'description' => 'Első megerősítő hajtás.',
                'duration_minutes' => 10,
                'wait_minutes' => 50,
                'temperature_celsius' => 24,
                'sort_order' => 2,
                'work_instruction' => 'Nedves kézzel hajtsd meg a tésztát több irányból.',
                'completion_criteria' => 'A tészta feszesebb és jobban tart.',
                'attention_points' => 'Finoman dolgozz, ne szakítsd a tésztát.',
                'required_tools' => 'tál, nedves kéz vagy spatula',
                'expected_result' => 'Megerősített gluténháló.',
                'is_active' => true,
            ],
            [
                'title' => '10:00 – hajtás #2',
                'step_type' => 'resting',
                'description' => 'Második hajtás a szerkezethez.',
                'duration_minutes' => 10,
                'wait_minutes' => 170,
                'temperature_celsius' => 24,
                'sort_order' => 3,
                'work_instruction' => 'Ismételd meg a hajtást, majd hagyd hosszabban fermentálódni.',
                'completion_criteria' => 'A tészta rugalmasabb és levegősebb.',
                'attention_points' => 'A hosszú pihenés alatt sokat fejlődik a szerkezet.',
                'required_tools' => 'tál, spatula',
                'expected_result' => 'Tepsibe rakásra kész, jól fermentált tészta.',
                'is_active' => true,
            ],
            [
                'title' => '13:00 – tepsibe helyezés',
                'step_type' => 'preparation',
                'description' => 'Olajozott tepsibe húzzuk a tésztát.',
                'duration_minutes' => 15,
                'wait_minutes' => 105,
                'temperature_celsius' => 24,
                'sort_order' => 4,
                'work_instruction' => 'Olajozd ki a tepsit, borítsd bele a tésztát, és finoman húzd a forma felé.',
                'completion_criteria' => 'A tészta tepsiben van, nagyjából egyenletesen elterítve.',
                'attention_points' => 'Ha visszahúzódik, ne erőltesd, hagyd pihenni.',
                'required_tools' => 'tepsi, olívaolaj, kéz',
                'expected_result' => 'Végső kelésre előkészített focaccia.',
                'is_active' => true,
            ],
            [
                'title' => '15:00 – ujjazás és topping',
                'step_type' => 'finishing',
                'description' => 'A megkelt tésztába olajos ujjakkal mélyedéseket nyomunk, majd mehet a topping.',
                'duration_minutes' => 15,
                'wait_minutes' => 0,
                'temperature_celsius' => 24,
                'sort_order' => 5,
                'work_instruction' => 'Olajos ujjakkal nyomkodj mélyedéseket a tésztába, majd szórd meg rozmaringgal, paradicsommal, fokhagymával és locsold meg olívaolajjal.',
                'completion_criteria' => 'A tészta levegőssége megmaradt, a jellegzetes focaccia mélyedések kialakultak.',
                'attention_points' => 'Rozmaring + paradicsom + fokhagyma = veszélyesen jó.',
                'required_tools' => 'tepsi, olívaolaj, kéz',
                'expected_result' => 'Sütésre kész, feltétezett focaccia.',
                'is_active' => true,
            ],
            [
                'title' => '15:15 – sütés',
                'step_type' => 'baking',
                'description' => 'A focaccia aranybarnára sütése.',
                'duration_minutes' => 30,
                'wait_minutes' => 0,
                'temperature_celsius' => 220,
                'sort_order' => 6,
                'work_instruction' => 'Süsd a focacciát előmelegített sütőben, amíg szélei mély aranybarnák lesznek.',
                'completion_criteria' => 'Az alja átsült, a teteje aranybarna, a belseje levegős.',
                'attention_points' => 'A tepsi anyaga és a sütő erőssége sokat számít.',
                'required_tools' => 'sütő, tepsi',
                'expected_result' => 'Kész, puha belsejű, ropogós szélű focaccia.',
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
