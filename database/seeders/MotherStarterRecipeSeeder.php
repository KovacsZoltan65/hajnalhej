<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Ingredient;
use App\Models\Product;
use App\Models\ProductIngredient;
use App\Models\RecipeStep;
use Illuminate\Database\Seeder;

class MotherStarterRecipeSeeder extends Seeder
{
    public function run(): void
    {
        $category = Category::query()->updateOrCreate(
            ['slug' => 'elokeszitok'],
            [
                'name' => 'Előkészítők',
                'description' => 'Kovászok, előtészták, starterek.',
                'is_active' => true,
                'sort_order' => 5,
            ],
        );

        $product = Product::query()->updateOrCreate(
            ['slug' => 'anyakovasz-keszitese-5-nap'],
            [
                'category_id' => $category->id,
                'name' => 'Anyakovász készítése (5 nap)',
                'short_description' => 'Búzalisztes anyakovász felépítése többnapos etetési folyamattal.',
                'description' => 'Teszt recept a többnapos tervezéshez. A cél egy stabil, aktív, később tovább etethető anyakovász létrehozása.',
                'price' => 0,
                'image_path' => 'products/anyakovasz-keszitese.jpg',
                'sort_order' => 60,
            ],
        );

        $ingredients = [
            'buzaliszt' => [
                'name' => 'Búzaliszt',
                'sku' => 'ING-BUZALISZT',
                'unit' => 'g',
                'current_stock' => 30000,
                'minimum_stock' => 5000,
                'is_active' => true,
                'notes' => 'Általános búzaliszt kovász építéshez.',
            ],
            'teljes-kiorlesu-liszt' => [
                'name' => 'Teljes kiőrlésű liszt',
                'sku' => 'ING-TELJES-KIORLESU-LISZT',
                'unit' => 'g',
                'current_stock' => 10000,
                'minimum_stock' => 2000,
                'is_active' => true,
                'notes' => 'Segíti az indulást az első napokban.',
            ],
            'viz' => [
                'name' => 'Víz',
                'sku' => 'ING-VIZ',
                'unit' => 'ml',
                'current_stock' => 50000,
                'minimum_stock' => 10000,
                'is_active' => true,
                'notes' => 'Langyos, tiszta víz.',
            ],
        ];

        $ingredientModels = [];

        foreach ($ingredients as $slug => $data) {
            $ingredientModels[$slug] = Ingredient::query()->updateOrCreate(
                ['slug' => $slug],
                $data,
            );
        }

        /*
         * A BOM itt a "napi összes felhasználás" logikát követi.
         * Összesen kb.:
         * - teljes kiőrlésű liszt: 50 g
         * - búzaliszt: 260 g
         * - víz: 310 g
         *
         * Ez tesztelésre jó, mert a planner aggregált anyagigényt is tud számolni.
         */
        $recipeItems = [
            [
                'ingredient_slug' => 'teljes-kiorlesu-liszt',
                'quantity' => 50,
                'sort_order' => 1,
                'notes' => 'Indító etetéshez.',
            ],
            [
                'ingredient_slug' => 'buzaliszt',
                'quantity' => 260,
                'sort_order' => 2,
                'notes' => 'Napi etetésekhez és fenntartáshoz.',
            ],
            [
                'ingredient_slug' => 'viz',
                'quantity' => 310,
                'sort_order' => 3,
                'notes' => 'Összes etetéshez szükséges víz.',
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
                'title' => '1. nap – induló keverék',
                'step_type' => 'preparation',
                'description' => 'Keverj össze 25 g teljes kiőrlésű lisztet, 25 g búzalisztet és 50 g vizet egy tiszta üvegben.',
                'duration_minutes' => 10,
                'wait_minutes' => 1440,
                'temperature_celsius' => 24,
                'sort_order' => 1,
                'work_instruction' => 'Mérd ki a liszteket és a vizet, majd keverd csomómentesre. Lazán lefedve hagyd szobahőmérsékleten.',
                'completion_criteria' => 'A keverék homogén, sűrű palacsintatészta állagú, tiszta üvegbe került.',
                'attention_points' => 'Ne zárd le légmentesen. Tiszta eszközökkel dolgozz.',
                'required_tools' => 'digitális mérleg, befőttesüveg, spatula',
                'expected_result' => 'Elindított starter keverék 1. napra.',
                'is_active' => true,
            ],
            [
                'title' => '2. nap – első ellenőrzés',
                'step_type' => 'resting',
                'description' => 'Ellenőrizd, hogy van-e enyhe buborékosság vagy illatváltozás.',
                'duration_minutes' => 5,
                'wait_minutes' => 720,
                'temperature_celsius' => 24,
                'sort_order' => 2,
                'work_instruction' => 'Vizsgáld meg a startert. Ha enyhe aktivitás látható, keverd át. Ha még semmi nem történik, az normális.',
                'completion_criteria' => 'Az állapot ellenőrizve, a starter átkeverve vagy változatlanul visszahelyezve.',
                'attention_points' => 'A 2. napon még gyenge aktivitás teljesen normális.',
                'required_tools' => 'spatula',
                'expected_result' => 'Megfigyelt starter, felkészítve a következő etetésre.',
                'is_active' => true,
            ],
            [
                'title' => '3. nap – első etetés',
                'step_type' => 'mixing',
                'description' => 'Tarts meg 50 g startert, majd adj hozzá 50 g búzalisztet és 50 g vizet.',
                'duration_minutes' => 10,
                'wait_minutes' => 720,
                'temperature_celsius' => 24,
                'sort_order' => 3,
                'work_instruction' => 'Dobd ki a starter egy részét úgy, hogy 50 g maradjon. Ehhez adj 50 g lisztet és 50 g vizet, majd keverd el.',
                'completion_criteria' => 'A starter újraetetve, homogén állagú, az edény oldalán jelölve a szint.',
                'attention_points' => 'Mindig csak a megtartott mennyiséget etesd tovább, különben kezelhetetlenül sok lesz.',
                'required_tools' => 'digitális mérleg, spatula, tiszta üveg',
                'expected_result' => 'Frissen etetett starter, követhető szintjelöléssel.',
                'is_active' => true,
            ],
            [
                'title' => '3. nap – fermentáció figyelése',
                'step_type' => 'proofing',
                'description' => 'Figyeld 8–12 órán át, hogyan reagál az első etetésre.',
                'duration_minutes' => 5,
                'wait_minutes' => 720,
                'temperature_celsius' => 24,
                'sort_order' => 4,
                'work_instruction' => 'Néhány órával etetés után ellenőrizd, nőtt-e a térfogata és jelentek-e meg buborékok.',
                'completion_criteria' => 'A starter aktivitása megfigyelve és értékelve.',
                'attention_points' => 'A növekedés még lehet egyenetlen, ez nem hiba.',
                'required_tools' => 'jelölő filc vagy gumi',
                'expected_result' => 'Megfigyelt aktivitási mintázat a további etetésekhez.',
                'is_active' => true,
            ],
            [
                'title' => '4. nap – második etetés',
                'step_type' => 'mixing',
                'description' => 'Tarts meg 50 g startert, adj hozzá 60 g búzalisztet és 60 g vizet.',
                'duration_minutes' => 10,
                'wait_minutes' => 720,
                'temperature_celsius' => 24,
                'sort_order' => 5,
                'work_instruction' => 'Újra tarts meg 50 g startert, majd etesd 60 g liszttel és 60 g vízzel.',
                'completion_criteria' => 'A starter újraetetve, homogén és jól átkevert.',
                'attention_points' => 'Ha erős savanyú vagy kellemetlen szagú, figyeld fokozottan a tisztaságot és a hőmérsékletet.',
                'required_tools' => 'digitális mérleg, spatula',
                'expected_result' => 'Erősebb aktivitású, stabilizálódó starter.',
                'is_active' => true,
            ],
            [
                'title' => '4. nap – aktivitás ellenőrzése',
                'step_type' => 'proofing',
                'description' => 'Ellenőrizd, hogy a starter 8–12 órán belül érezhetően megemelkedik-e.',
                'duration_minutes' => 5,
                'wait_minutes' => 720,
                'temperature_celsius' => 24,
                'sort_order' => 6,
                'work_instruction' => 'Vizsgáld meg, hogy a starter nő-e, buborékos-e, és mennyire tartja a szerkezetét.',
                'completion_criteria' => 'Az aktivitás szintje felmérve, a következő etetéshez elegendő információ rendelkezésre áll.',
                'attention_points' => 'A cél a kiszámítható, ismételhető növekedés.',
                'required_tools' => 'jelölő filc vagy gumi',
                'expected_result' => 'Megítélhető, hogy a starter közelít-e a stabil állapothoz.',
                'is_active' => true,
            ],
            [
                'title' => '5. nap – harmadik etetés',
                'step_type' => 'mixing',
                'description' => 'Tarts meg 50 g startert, majd adj hozzá 75 g búzalisztet és 75 g vizet.',
                'duration_minutes' => 10,
                'wait_minutes' => 480,
                'temperature_celsius' => 24,
                'sort_order' => 7,
                'work_instruction' => 'Tarts meg 50 g startert, majd etesd 75 g liszttel és 75 g vízzel. Jelöld meg a kiinduló szintet.',
                'completion_criteria' => 'Az etetett starter sima, egynemű és szintjelöléssel ellátott.',
                'attention_points' => 'Ha nagyon gyorsan nő, sűrűbb ellenőrzés szükséges.',
                'required_tools' => 'digitális mérleg, spatula, befőttesüveg',
                'expected_result' => 'Készen álló, erősödő anyakovász.',
                'is_active' => true,
            ],
            [
                'title' => '5. nap – csúcspont figyelése',
                'step_type' => 'proofing',
                'description' => 'Figyeld, hogy 4–8 órán belül képes-e legalább duplázódni.',
                'duration_minutes' => 5,
                'wait_minutes' => 240,
                'temperature_celsius' => 24,
                'sort_order' => 8,
                'work_instruction' => 'Ellenőrizd a térfogatot, a buborékosságot és az illatot. Akkor tekinthető használhatónak, ha kiszámíthatóan nő és még nem esett vissza.',
                'completion_criteria' => 'A starter legalább duplázódott, buborékos, enyhén domború tetejű, kellemes savanykás illatú.',
                'attention_points' => 'Ha már összeesett, etesd újra használat előtt.',
                'required_tools' => 'jelölő filc vagy gumi',
                'expected_result' => 'Stabil, használható anyakovász.',
                'is_active' => true,
            ],
            [
                'title' => 'Anyakovász fenntartása',
                'step_type' => 'finishing',
                'description' => 'A stabil starter innentől hűtőben tartható, rendszeres etetéssel fenntartható.',
                'duration_minutes' => 5,
                'wait_minutes' => 10080,
                'temperature_celsius' => 5,
                'sort_order' => 9,
                'work_instruction' => 'Ha nem használod azonnal, tedd hűtőbe. Hetente legalább egyszer etesd újra. Használat előtt egy friss etetés ajánlott.',
                'completion_criteria' => 'A starter tárolásra előkészítve és fenntartási üteme meghatározva.',
                'attention_points' => 'A fenntartás már ciklikus folyamat, nem egyszeri lépés.',
                'required_tools' => 'befőttesüveg, hűtőszekrény',
                'expected_result' => 'Fenntartható anyakovász alap a későbbi aktív starterhez.',
                'is_active' => true,
            ],
        ];

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