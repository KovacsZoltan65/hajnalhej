<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductIngredient;
use App\Models\RecipeStep;
use Database\Seeders\Concerns\UsesSeededIngredients;
use Illuminate\Database\Seeder;

class PremiumBakerySeedPackSeeder extends Seeder
{
    use UsesSeededIngredients;

    public function run(): void
    {
        $categories = [
            'premium-pekseg' => Category::query()->updateOrCreate(
                ['slug' => 'premium-pekseg'],
                [
                    'name' => 'Premium Pékség',
                    'description' => 'Magasabb technológiai igényű péksütemények és kézműves termékek.',
                    'is_active' => true,
                    'sort_order' => 40,
                ],
            ),
            'edes-pekseg' => Category::query()->updateOrCreate(
                ['slug' => 'edes-pekseg'],
                [
                    'name' => 'Édes Pékség',
                    'description' => 'Kovászos édes péksütemények.',
                    'is_active' => true,
                    'sort_order' => 41,
                ],
            ),
        ];

        $ingredients = $this->loadIngredients();

        $this->seedBagel($categories['premium-pekseg'], $ingredients);
        $this->seedCroissant($categories['premium-pekseg'], $ingredients);
        $this->seedCinnamonRoll($categories['edes-pekseg'], $ingredients);
    }

    private function loadIngredients(): array
    {
        $definitions = [
            'liszt' => [
                'name' => 'Búzaliszt',
                'sku' => 'ING-LISZT',
                'unit' => 'g',
                'current_stock' => 60000,
                'minimum_stock' => 5000,
                'is_active' => true,
                'notes' => 'Általános liszt péksüteményekhez.',
            ],
            '00-liszt' => [
                'name' => '00 liszt',
                'sku' => 'ING-00-LISZT',
                'unit' => 'g',
                'current_stock' => 30000,
                'minimum_stock' => 3000,
                'is_active' => true,
                'notes' => 'Finomabb szerkezetű tésztákhoz.',
            ],
            'vaj' => [
                'name' => 'Vaj',
                'sku' => 'ING-VAJ',
                'unit' => 'g',
                'current_stock' => 20000,
                'minimum_stock' => 2000,
                'is_active' => true,
                'notes' => 'Lamináláshoz és dúsított tésztákhoz.',
            ],
            'tej' => [
                'name' => 'Tej',
                'sku' => 'ING-TEJ',
                'unit' => 'ml',
                'current_stock' => 25000,
                'minimum_stock' => 2500,
                'is_active' => true,
                'notes' => 'Langyos tej édes tésztákhoz.',
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
            'kovasz' => [
                'name' => 'Aktív kovász',
                'sku' => 'ING-AKTIV-KOVASZ',
                'unit' => 'g',
                'current_stock' => 8000,
                'minimum_stock' => 500,
                'is_active' => true,
                'notes' => 'Erős, csúcson lévő kovász.',
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
            'cukor' => [
                'name' => 'Kristálycukor',
                'sku' => 'ING-CUKOR',
                'unit' => 'g',
                'current_stock' => 10000,
                'minimum_stock' => 1000,
                'is_active' => true,
                'notes' => 'Édesítéshez.',
            ],
            'barna-cukor' => [
                'name' => 'Barna cukor',
                'sku' => 'ING-BARNA-CUKOR',
                'unit' => 'g',
                'current_stock' => 10000,
                'minimum_stock' => 1000,
                'is_active' => true,
                'notes' => 'Fahéjas töltelékhez.',
            ],
            'tojas' => [
                'name' => 'Tojás',
                'sku' => 'ING-TOJAS',
                'unit' => 'g',
                'current_stock' => 12000,
                'minimum_stock' => 1000,
                'is_active' => true,
                'notes' => 'Grammban kezelve a skálázás miatt.',
            ],
            'fahéj' => [
                'name' => 'Fahéj',
                'sku' => 'ING-FAHEJ',
                'unit' => 'g',
                'current_stock' => 2000,
                'minimum_stock' => 200,
                'is_active' => true,
                'notes' => 'Fahéjas töltelékhez.',
            ],
            'mez' => [
                'name' => 'Méz',
                'sku' => 'ING-MEZ',
                'unit' => 'g',
                'current_stock' => 5000,
                'minimum_stock' => 500,
                'is_active' => true,
                'notes' => 'Bagel főzővízhez és enyhe édesítéshez.',
            ],
            'mak' => [
                'name' => 'Mák',
                'sku' => 'ING-MAK',
                'unit' => 'g',
                'current_stock' => 3000,
                'minimum_stock' => 300,
                'is_active' => true,
                'notes' => 'Bagel topping.',
            ],
            'szezammag' => [
                'name' => 'Szezámmag',
                'sku' => 'ING-SZEZAMMAG',
                'unit' => 'g',
                'current_stock' => 3000,
                'minimum_stock' => 300,
                'is_active' => true,
                'notes' => 'Bagel topping.',
            ],
        ];

        return $this->seededIngredients(array_keys($definitions));
    }

    private function seedBagel(Category $category, array $ingredients): void
    {
        $product = Product::query()->updateOrCreate(
            ['slug' => 'kovaszos-bagel'],
            [
                'category_id' => $category->id,
                'name' => 'Kovászos Bagel',
                'short_description' => 'Rugalmas, sűrűbb szerkezetű, főzött-sütött kovászos bagel.',
                'description' => 'A bagel kiváló planner-teszt a főzés + sütés kombináció miatt.',
                'price' => 0,
                'image_path' => 'products/kovaszos-bagel.jpg',
                'sort_order' => 70,
            ],
        );

        $items = [
            ['liszt', 500, 1, 'Alap tészta'],
            ['viz', 260, 2, 'Alacsonyabb hidratáció'],
            ['kovasz', 120, 3, 'Aktív kovász'],
            ['so', 10, 4, 'Ízesítés'],
            ['mez', 20, 5, 'Enyhe édesség és főzővízhez'],
            ['mak', 20, 6, 'Opcionális topping'],
            ['szezammag', 20, 7, 'Opcionális topping'],
        ];

        foreach ($items as [$slug, $qty, $order, $note]) {
            ProductIngredient::query()->updateOrCreate(
                [
                    'product_id' => $product->id,
                    'ingredient_id' => $ingredients[$slug]->id,
                ],
                [
                    'quantity' => $qty,
                    'sort_order' => $order,
                    'notes' => $note,
                ],
            );
        }

        $steps = [
            [
                'title' => 'Este – dagasztás',
                'step_type' => 'mixing',
                'description' => 'Sűrűbb, feszes bageltészta dagasztása.',
                'duration_minutes' => 20,
                'wait_minutes' => 60,
                'temperature_celsius' => 24,
                'sort_order' => 1,
                'work_instruction' => 'Dolgozd össze az alapanyagokat feszes, tömör tésztává.',
                'completion_criteria' => 'A tészta sima, feszes, kevésbé ragacsos.',
                'attention_points' => 'A bagel tésztája keményebb. Ez nem bug, ez feature.',
                'required_tools' => 'dagasztótál, mérleg, dagasztógép vagy kéz',
                'expected_result' => 'Bagel-formázásra alkalmas tészta.',
                'is_active' => true,
            ],
            [
                'title' => 'Este – előferment',
                'step_type' => 'proofing',
                'description' => 'Rövid szobahős indulás.',
                'duration_minutes' => 5,
                'wait_minutes' => 55,
                'temperature_celsius' => 24,
                'sort_order' => 2,
                'work_instruction' => 'Letakarva hagyd indulni a tésztát.',
                'completion_criteria' => 'A tészta kissé megindult.',
                'attention_points' => 'Nem kell teljes megkelés.',
                'required_tools' => 'tál, fedő',
                'expected_result' => 'Hűtőzésre kész tészta.',
                'is_active' => true,
            ],
            [
                'title' => 'Éjjel – hűtő',
                'step_type' => 'proofing',
                'description' => 'Éjszakai hideg pihenés.',
                'duration_minutes' => 5,
                'wait_minutes' => 480,
                'temperature_celsius' => 5,
                'sort_order' => 3,
                'work_instruction' => 'Tedd a tésztát hűtőbe éjszakára.',
                'completion_criteria' => 'A tészta hidegen pihen.',
                'attention_points' => 'A hideg tészta másnap jobban kezelhető.',
                'required_tools' => 'hűtő',
                'expected_result' => 'Másnap formázható bagel tészta.',
                'is_active' => true,
            ],
            [
                'title' => 'Reggel – osztás és gyűrűzés',
                'step_type' => 'shaping',
                'description' => 'Bagel formázása gyűrű alakra.',
                'duration_minutes' => 30,
                'wait_minutes' => 30,
                'temperature_celsius' => 22,
                'sort_order' => 4,
                'work_instruction' => 'Oszd a tésztát, gömbölyítsd, majd lyukas bagel formára alakítsd.',
                'completion_criteria' => 'A bagel gyűrűk egyenletesek, stabilak.',
                'attention_points' => 'A lyuk legyen nagyobb, mert keléskor szűkül.',
                'required_tools' => 'mérleg, munkapult',
                'expected_result' => 'Főzésre kész bagel gyűrűk.',
                'is_active' => true,
            ],
            [
                'title' => 'Reggel – főzés',
                'step_type' => 'preparation',
                'description' => 'Rövid főzés mézes vízben.',
                'duration_minutes' => 15,
                'wait_minutes' => 5,
                'temperature_celsius' => 100,
                'sort_order' => 5,
                'work_instruction' => 'Röviden főzd meg a bageleket gyöngyöző vízben, majd csöpögtesd le.',
                'completion_criteria' => 'A bagelek feszesebbek, felületük simább.',
                'attention_points' => 'Ne főzd túl, mert túl tömör lesz.',
                'required_tools' => 'lábas, szűrőkanál',
                'expected_result' => 'Sütésre előkészített, főzött bagel.',
                'is_active' => true,
            ],
            [
                'title' => 'Reggel – topping és sütés',
                'step_type' => 'baking',
                'description' => 'Mag vagy mák topping után sütés.',
                'duration_minutes' => 25,
                'wait_minutes' => 0,
                'temperature_celsius' => 220,
                'sort_order' => 6,
                'work_instruction' => 'Szórd meg a tetejét, majd süsd aranybarnára.',
                'completion_criteria' => 'A bagel héja fényes, belseje rugalmas.',
                'attention_points' => 'A jó bagel nem zsemle. Tömörebb, rágósabb.',
                'required_tools' => 'tepsi, sütő',
                'expected_result' => 'Kész kovászos bagel.',
                'is_active' => true,
            ],
        ];

        $this->upsertSteps($product->id, $steps);
    }

    private function seedCroissant(Category $category, array $ingredients): void
    {
        $product = Product::query()->updateOrCreate(
            ['slug' => 'kovaszos-croissant'],
            [
                'category_id' => $category->id,
                'name' => 'Kovászos Croissant',
                'short_description' => 'Vajas, laminált, réteges croissant hosszú hideg pihentetéssel.',
                'description' => 'A croissant a planner egyik legjobb stressztesztje: laminálás, pihentetés, proof, sütés.',
                'price' => 0,
                'image_path' => 'products/kovaszos-croissant.jpg',
                'sort_order' => 71,
            ],
        );

        $items = [
            ['00-liszt', 500, 1, 'Alap tészta'],
            ['tej', 220, 2, 'Tésztához'],
            ['kovasz', 120, 3, 'Aktív kovász'],
            ['cukor', 50, 4, 'Enyhe édesítés'],
            ['so', 10, 5, 'Ízesítés'],
            ['vaj', 280, 6, 'Tésztába és lamináláshoz'],
        ];

        foreach ($items as [$slug, $qty, $order, $note]) {
            ProductIngredient::query()->updateOrCreate(
                [
                    'product_id' => $product->id,
                    'ingredient_id' => $ingredients[$slug]->id,
                ],
                [
                    'quantity' => $qty,
                    'sort_order' => $order,
                    'notes' => $note,
                ],
            );
        }

        $steps = [
            [
                'title' => '1. nap – détrempe dagasztás',
                'step_type' => 'mixing',
                'description' => 'Alaptészta elkészítése vaj nélkül vagy minimális vajjal.',
                'duration_minutes' => 20,
                'wait_minutes' => 60,
                'temperature_celsius' => 22,
                'sort_order' => 1,
                'work_instruction' => 'Dolgozd össze a tészta alapanyagait sima, de nem túlmelegedett tésztává.',
                'completion_criteria' => 'A tészta homogén, feszes, jól kezelhető.',
                'attention_points' => 'A croissant-nál a hő a főellenség. A vaj nem szeret olvadni.',
                'required_tools' => 'dagasztótál, mérleg',
                'expected_result' => 'Laminálásra előkészített alaptészta.',
                'is_active' => true,
            ],
            [
                'title' => '1. nap – első hűtés',
                'step_type' => 'proofing',
                'description' => 'Alaptészta hűtése a laminálás előtt.',
                'duration_minutes' => 5,
                'wait_minutes' => 115,
                'temperature_celsius' => 5,
                'sort_order' => 2,
                'work_instruction' => 'Csomagold és tedd hűtőbe.',
                'completion_criteria' => 'A tészta lehűlt és feszes.',
                'attention_points' => 'A hideg tészta a barátod.',
                'required_tools' => 'hűtő, fólia',
                'expected_result' => 'Laminálásra kész alaptészta.',
                'is_active' => true,
            ],
            [
                'title' => '1. nap – vaj bezárása',
                'step_type' => 'shaping',
                'description' => 'Vajlap beburkolása a tésztába.',
                'duration_minutes' => 20,
                'wait_minutes' => 40,
                'temperature_celsius' => 16,
                'sort_order' => 3,
                'work_instruction' => 'Nyújtsd ki a tésztát, helyezd bele a vajlapot, majd zárd körbe.',
                'completion_criteria' => 'A vaj teljesen a tésztában van, nem szakad ki.',
                'attention_points' => 'Ha a vaj kitör, meg fog bosszulni sütéskor.',
                'required_tools' => 'nyújtófa, mérleg, hideg munkapult',
                'expected_result' => 'Laminált blokk első hajtásra készen.',
                'is_active' => true,
            ],
            [
                'title' => '1. nap – első hajtás',
                'step_type' => 'folding',
                'description' => 'Első egyszeres vagy dupla hajtás.',
                'duration_minutes' => 20,
                'wait_minutes' => 60,
                'temperature_celsius' => 16,
                'sort_order' => 4,
                'work_instruction' => 'Nyújtsd és hajtsd meg a blokkot, majd hűtsd vissza.',
                'completion_criteria' => 'A hajtás szabályos, a rétegek egyenletesek.',
                'attention_points' => 'A tészta és a vaj hasonló keménységű legyen.',
                'required_tools' => 'nyújtófa, hűtő',
                'expected_result' => 'Második hajtásra alkalmas laminált tészta.',
                'is_active' => true,
            ],
            [
                'title' => '1. nap – második hajtás',
                'step_type' => 'folding',
                'description' => 'Második hajtás és újabb hűtés.',
                'duration_minutes' => 20,
                'wait_minutes' => 480,
                'temperature_celsius' => 16,
                'sort_order' => 5,
                'work_instruction' => 'Végezd el a második hajtást, majd hűtsd a tésztát hosszabban.',
                'completion_criteria' => 'A rétegek stabilak, a blokk hideg és szabályos.',
                'attention_points' => 'Itt dől el, croissant lesz-e, vagy vajas trauma.',
                'required_tools' => 'nyújtófa, hűtő',
                'expected_result' => 'Formázásra alkalmas laminált tészta.',
                'is_active' => true,
            ],
            [
                'title' => '2. nap – nyújtás és formázás',
                'step_type' => 'shaping',
                'description' => 'Háromszögek vágása és feltekerés.',
                'duration_minutes' => 35,
                'wait_minutes' => 25,
                'temperature_celsius' => 18,
                'sort_order' => 6,
                'work_instruction' => 'Nyújtsd ki a tésztát, vágj háromszögeket, majd tekerd fel croissant formára.',
                'completion_criteria' => 'Egyenletes, jól tekert croissant-ok.',
                'attention_points' => 'Ne nyomd ki a rétegeket.',
                'required_tools' => 'nyújtófa, kés vagy pizzavágó',
                'expected_result' => 'Proofra kész croissant-ok.',
                'is_active' => true,
            ],
            [
                'title' => '2. nap – végső proof',
                'step_type' => 'proofing',
                'description' => 'Hosszú, kíméletes kelés.',
                'duration_minutes' => 5,
                'wait_minutes' => 175,
                'temperature_celsius' => 24,
                'sort_order' => 7,
                'work_instruction' => 'Hagyd a croissant-okat meleg, de nem túl forró helyen kelni.',
                'completion_criteria' => 'Láthatóan megnőttek, remegősek, könnyedek.',
                'attention_points' => 'Túl meleg proofnál a vaj kifolyik. A croissant ezt személyes sértésnek veszi.',
                'required_tools' => 'tepsi, fólia',
                'expected_result' => 'Sütésre kész croissant.',
                'is_active' => true,
            ],
            [
                'title' => '2. nap – sütés',
                'step_type' => 'baking',
                'description' => 'Aranybarna, leveles croissant sütése.',
                'duration_minutes' => 22,
                'wait_minutes' => 0,
                'temperature_celsius' => 190,
                'sort_order' => 8,
                'work_instruction' => 'Süsd előmelegített sütőben mély aranybarnára.',
                'completion_criteria' => 'Réteges, leveles, jól megemelkedett croissant.',
                'attention_points' => 'A jó croissant nem csak szép, hanem hallhatóan serceg is.',
                'required_tools' => 'sütő, tepsi',
                'expected_result' => 'Kész kovászos croissant.',
                'is_active' => true,
            ],
        ];

        $this->upsertSteps($product->id, $steps);
    }

    private function seedCinnamonRoll(Category $category, array $ingredients): void
    {
        $product = Product::query()->updateOrCreate(
            ['slug' => 'fahejas-csiga'],
            [
                'category_id' => $category->id,
                'name' => 'Fahéjas Csiga',
                'short_description' => 'Kovászos, vajas, fahéjas csiga hideg éjszakai pihentetéssel.',
                'description' => 'Nagyon jó teszt a plannerhez: esti dagasztás, éjszakai pihenés, reggeli formázás és sütés.',
                'price' => 0,
                'image_path' => 'products/fahejas-csiga.jpg',
                'sort_order' => 72,
            ],
        );

        $items = [
            ['liszt', 500, 1, 'Tészta alapja'],
            ['tej', 220, 2, 'Tésztához'],
            ['kovasz', 100, 3, 'Aktív kovász'],
            ['vaj', 140, 4, 'Tésztához és töltelékhez'],
            ['cukor', 70, 5, 'Tésztához'],
            ['barna-cukor', 90, 6, 'Töltelékhez'],
            ['tojas', 50, 7, 'Tésztához kb. 1 tojás'],
            ['so', 8, 8, 'Ízesítés'],
            ['fahéj', 18, 9, 'Töltelékhez'],
        ];

        foreach ($items as [$slug, $qty, $order, $note]) {
            ProductIngredient::query()->updateOrCreate(
                [
                    'product_id' => $product->id,
                    'ingredient_id' => $ingredients[$slug]->id,
                ],
                [
                    'quantity' => $qty,
                    'sort_order' => $order,
                    'notes' => $note,
                ],
            );
        }

        $steps = [
            [
                'title' => 'Este – dagasztás',
                'step_type' => 'mixing',
                'description' => 'Dúsított tészta elkészítése.',
                'duration_minutes' => 25,
                'wait_minutes' => 120,
                'temperature_celsius' => 24,
                'sort_order' => 1,
                'work_instruction' => 'Dolgozd össze a tészta alapanyagait, a vajat fokozatosan add hozzá.',
                'completion_criteria' => 'Puha, sima, rugalmas tészta.',
                'attention_points' => 'Ragadós lesz. Ez a tészta nem támad, csak ilyen.',
                'required_tools' => 'dagasztótál, mérleg',
                'expected_result' => 'Hideg pihentetésre kész csigatészta.',
                'is_active' => true,
            ],
            [
                'title' => 'Este – szobahős indulás',
                'step_type' => 'proofing',
                'description' => 'Két órás induló pihenés.',
                'duration_minutes' => 5,
                'wait_minutes' => 115,
                'temperature_celsius' => 24,
                'sort_order' => 2,
                'work_instruction' => 'Hagyd a tésztát letakarva indulni.',
                'completion_criteria' => 'A tészta enyhén megindult.',
                'attention_points' => 'Nem kell duplázódnia.',
                'required_tools' => 'tál, fedő',
                'expected_result' => 'Éjszakai hűtésre kész tészta.',
                'is_active' => true,
            ],
            [
                'title' => 'Éjjel – hűtő',
                'step_type' => 'proofing',
                'description' => 'Éjszakai hideg pihenés.',
                'duration_minutes' => 5,
                'wait_minutes' => 480,
                'temperature_celsius' => 5,
                'sort_order' => 3,
                'work_instruction' => 'Tedd a tésztát hűtőbe.',
                'completion_criteria' => 'A tészta hidegen pihen.',
                'attention_points' => 'Másnap könnyebb lesz nyújtani.',
                'required_tools' => 'hűtő',
                'expected_result' => 'Reggeli nyújtásra kész tészta.',
                'is_active' => true,
            ],
            [
                'title' => 'Reggel – nyújtás, töltés, tekerés',
                'step_type' => 'shaping',
                'description' => 'Fahéjas-barna cukros töltelékkel feltekerés.',
                'duration_minutes' => 35,
                'wait_minutes' => 25,
                'temperature_celsius' => 22,
                'sort_order' => 4,
                'work_instruction' => 'Nyújtsd ki a tésztát, kend meg vajjal, szórd meg fahéj és barna cukor keverékével, majd tekerd fel és szeleteld.',
                'completion_criteria' => 'Egyenletes csigák tepsire rendezve.',
                'attention_points' => 'Ne tekerd túl lazára, különben szétesik.',
                'required_tools' => 'nyújtófa, spatula, kés vagy cérna',
                'expected_result' => 'Megkelesztésre kész fahéjas csigák.',
                'is_active' => true,
            ],
            [
                'title' => 'Reggel – végső kelés',
                'step_type' => 'proofing',
                'description' => 'Utolsó proof sütés előtt.',
                'duration_minutes' => 5,
                'wait_minutes' => 85,
                'temperature_celsius' => 24,
                'sort_order' => 5,
                'work_instruction' => 'Takard le és hagyd puhára, levegősre kelni.',
                'completion_criteria' => 'A csigák érezhetően megemelkedtek.',
                'attention_points' => 'Túlkelésnél szétlapulnak.',
                'required_tools' => 'tepsi, fólia',
                'expected_result' => 'Sütésre kész fahéjas csigák.',
                'is_active' => true,
            ],
            [
                'title' => 'Reggel – sütés',
                'step_type' => 'baking',
                'description' => 'Aranybarnára sütés.',
                'duration_minutes' => 25,
                'wait_minutes' => 0,
                'temperature_celsius' => 190,
                'sort_order' => 6,
                'work_instruction' => 'Süsd a csigákat aranybarnára.',
                'completion_criteria' => 'A szélek enyhén karamellizáltak, belül puhák.',
                'attention_points' => 'Ne szárítsd ki.',
                'required_tools' => 'sütő, tepsi',
                'expected_result' => 'Kész fahéjas csiga.',
                'is_active' => true,
            ],
        ];

        $this->upsertSteps($product->id, $steps);
    }

    private function upsertSteps(int $productId, array $steps): void
    {
        foreach ($steps as $step) {
            RecipeStep::query()->updateOrCreate(
                [
                    'product_id' => $productId,
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
