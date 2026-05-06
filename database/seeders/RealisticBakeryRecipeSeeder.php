<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Ingredient;
use App\Models\Product;
use App\Models\ProductIngredient;
use App\Models\RecipeStep;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RealisticBakeryRecipeSeeder extends Seeder
{
    /**
     * @var array<string, array<string, mixed>>
     */
    private array $recipes = [
        'klasszikus-kovaszos-kenyer' => [
            'product' => [
                'name' => 'Klasszikus kovászos kenyér',
                'category_slug' => 'kenyerek',
                'price' => 2450,
                'short_description' => 'BL80 lisztből, aktív kovásszal, hosszú érleléssel készített alapkenyér.',
                'description' => 'Két közepes veknire számolt alaptészta, körülbelül 70% hidratációval.',
                'stock_status' => Product::STOCK_IN_STOCK,
                'sort_order' => 1,
            ],
            'yield' => '2 db vekni',
            'ingredients' => [
                ['slug' => 'bl80-kenyerliszt', 'name' => 'BL80 kenyérliszt', 'unit' => 'g', 'quantity' => 1000],
                ['slug' => 'viz', 'name' => 'Víz', 'unit' => 'ml', 'quantity' => 700],
                ['slug' => 'aktiv-kovasz', 'name' => 'Aktív kovász', 'unit' => 'g', 'quantity' => 200],
                ['slug' => 'so', 'name' => 'Só', 'unit' => 'g', 'quantity' => 20],
            ],
            'steps' => [
                ['title' => 'Autolízis', 'type' => RecipeStep::TYPE_PREPARATION, 'duration' => 10, 'wait' => 30, 'temp' => 24],
                ['title' => 'Dagasztás és sózás', 'type' => RecipeStep::TYPE_MIXING, 'duration' => 25, 'wait' => 0, 'temp' => 24],
                ['title' => 'Hajtogatásos bulk fermentáció', 'type' => RecipeStep::TYPE_PROOFING, 'duration' => 20, 'wait' => 240, 'temp' => 24],
                ['title' => 'Formázás és hideg kelesztés', 'type' => RecipeStep::TYPE_RESTING, 'duration' => 20, 'wait' => 720, 'temp' => 5],
                ['title' => 'Sütés gőzzel', 'type' => RecipeStep::TYPE_BAKING, 'duration' => 45, 'wait' => 0, 'temp' => 245],
                ['title' => 'Hűtés rácson', 'type' => RecipeStep::TYPE_COOLING, 'duration' => 5, 'wait' => 60, 'temp' => null],
            ],
        ],
        'rozmaringos-focaccia' => [
            'product' => [
                'name' => 'Rozmaringos focaccia',
                'category_slug' => 'sos-pekaru',
                'price' => 1990,
                'short_description' => 'Magas hidratációjú tepsis tészta olívaolajjal és rozmaringgal.',
                'description' => 'Egy nagy tepsire számolt, levegős focaccia tészta friss rozmaringgal.',
                'stock_status' => Product::STOCK_PREORDER,
                'sort_order' => 3,
            ],
            'yield' => '1 nagy tepsi',
            'ingredients' => [
                ['slug' => 'kenyerliszt', 'name' => 'Kenyérliszt', 'unit' => 'g', 'quantity' => 1000],
                ['slug' => 'viz', 'name' => 'Víz', 'unit' => 'ml', 'quantity' => 800],
                ['slug' => 'aktiv-kovasz', 'name' => 'Aktív kovász', 'unit' => 'g', 'quantity' => 150],
                ['slug' => 'so', 'name' => 'Só', 'unit' => 'g', 'quantity' => 22],
                ['slug' => 'olivaolaj', 'name' => 'Olívaolaj', 'unit' => 'g', 'quantity' => 70],
                ['slug' => 'rozmaring', 'name' => 'Rozmaring', 'unit' => 'g', 'quantity' => 5],
            ],
            'steps' => [
                ['title' => 'Laza keverés', 'type' => RecipeStep::TYPE_MIXING, 'duration' => 15, 'wait' => 20, 'temp' => 24],
                ['title' => 'Hajtogatás olajozott edényben', 'type' => RecipeStep::TYPE_PROOFING, 'duration' => 15, 'wait' => 180, 'temp' => 24],
                ['title' => 'Tepsibe húzás', 'type' => RecipeStep::TYPE_PREPARATION, 'duration' => 10, 'wait' => 60, 'temp' => 24],
                ['title' => 'Olajozás és rozmaringozás', 'type' => RecipeStep::TYPE_FINISHING, 'duration' => 10, 'wait' => 0, 'temp' => null],
                ['title' => 'Sütés', 'type' => RecipeStep::TYPE_BAKING, 'duration' => 25, 'wait' => 0, 'temp' => 230],
            ],
        ],
        'kakaos-csiga' => [
            'product' => [
                'name' => 'Kakaós csiga',
                'category_slug' => 'edes-pekaru',
                'price' => 990,
                'short_description' => 'Puha kelt tészta holland kakaós, vajas töltelékkel.',
                'description' => 'Tizennyolc darabos reggeli batch, jól számolható kakaós töltelékkel.',
                'stock_status' => Product::STOCK_IN_STOCK,
                'sort_order' => 4,
            ],
            'yield' => '18 db',
            'ingredients' => [
                ['slug' => 'liszt', 'name' => 'Búzakenyérliszt', 'unit' => 'g', 'quantity' => 1000],
                ['slug' => 'tej', 'name' => 'Tej', 'unit' => 'ml', 'quantity' => 500],
                ['slug' => 'vaj', 'name' => 'Vaj', 'unit' => 'g', 'quantity' => 220],
                ['slug' => 'cukor', 'name' => 'Cukor', 'unit' => 'g', 'quantity' => 160],
                ['slug' => 'tojas', 'name' => 'Tojás', 'unit' => 'g', 'quantity' => 100],
                ['slug' => 'eleszto', 'name' => 'Élesztő', 'unit' => 'g', 'quantity' => 35],
                ['slug' => 'so', 'name' => 'Só', 'unit' => 'g', 'quantity' => 15],
                ['slug' => 'kakao', 'name' => 'Holland kakaópor', 'unit' => 'g', 'quantity' => 80],
                ['slug' => 'barna-cukor', 'name' => 'Barna cukor', 'unit' => 'g', 'quantity' => 120],
            ],
            'steps' => [
                ['title' => 'Kelt tészta dagasztása', 'type' => RecipeStep::TYPE_MIXING, 'duration' => 25, 'wait' => 0, 'temp' => 24],
                ['title' => 'Első kelesztés', 'type' => RecipeStep::TYPE_PROOFING, 'duration' => 5, 'wait' => 75, 'temp' => 26],
                ['title' => 'Töltelék kenése és feltekerés', 'type' => RecipeStep::TYPE_PREPARATION, 'duration' => 25, 'wait' => 0, 'temp' => null],
                ['title' => 'Darabolás és második kelesztés', 'type' => RecipeStep::TYPE_PROOFING, 'duration' => 15, 'wait' => 45, 'temp' => 26],
                ['title' => 'Sütés', 'type' => RecipeStep::TYPE_BAKING, 'duration' => 22, 'wait' => 0, 'temp' => 190],
                ['title' => 'Hűtés', 'type' => RecipeStep::TYPE_COOLING, 'duration' => 5, 'wait' => 20, 'temp' => null],
            ],
        ],
        'brios' => [
            'product' => [
                'name' => 'Briós',
                'category_slug' => 'edes-pekaru',
                'price' => 1190,
                'short_description' => 'Gazdag, tojásos-vajas kelt tészta, fonott formában.',
                'description' => 'Tizenhat darabos, magas vajtartalmú reggeli briós batch.',
                'stock_status' => Product::STOCK_PREORDER,
                'sort_order' => 60,
            ],
            'yield' => '16 db',
            'ingredients' => [
                ['slug' => 'liszt', 'name' => 'Búzakenyérliszt', 'unit' => 'g', 'quantity' => 1000],
                ['slug' => 'tojas', 'name' => 'Tojás', 'unit' => 'g', 'quantity' => 400],
                ['slug' => 'tej', 'name' => 'Tej', 'unit' => 'ml', 'quantity' => 250],
                ['slug' => 'vaj', 'name' => 'Vaj', 'unit' => 'g', 'quantity' => 350],
                ['slug' => 'cukor', 'name' => 'Cukor', 'unit' => 'g', 'quantity' => 140],
                ['slug' => 'so', 'name' => 'Só', 'unit' => 'g', 'quantity' => 18],
                ['slug' => 'eleszto', 'name' => 'Élesztő', 'unit' => 'g', 'quantity' => 25],
            ],
            'steps' => [
                ['title' => 'Alaptészta dagasztása', 'type' => RecipeStep::TYPE_MIXING, 'duration' => 20, 'wait' => 0, 'temp' => 22],
                ['title' => 'Vaj bedolgozása', 'type' => RecipeStep::TYPE_MIXING, 'duration' => 20, 'wait' => 0, 'temp' => 22],
                ['title' => 'Hideg érlelés', 'type' => RecipeStep::TYPE_RESTING, 'duration' => 10, 'wait' => 480, 'temp' => 5],
                ['title' => 'Formázás', 'type' => RecipeStep::TYPE_PREPARATION, 'duration' => 30, 'wait' => 0, 'temp' => null],
                ['title' => 'Végső kelesztés', 'type' => RecipeStep::TYPE_PROOFING, 'duration' => 5, 'wait' => 90, 'temp' => 26],
                ['title' => 'Sütés', 'type' => RecipeStep::TYPE_BAKING, 'duration' => 20, 'wait' => 0, 'temp' => 185],
            ],
        ],
        'kovaszos-bagett' => [
            'product' => [
                'name' => 'Kovászos bagett',
                'category_slug' => 'kenyerek',
                'price' => 1290,
                'short_description' => 'Hosszú érlelésű, vékony héjú kovászos bagett.',
                'description' => 'Hat bagettre számolt batch, közepes hidratációval és aktív kovásszal.',
                'stock_status' => Product::STOCK_IN_STOCK,
                'sort_order' => 20,
            ],
            'yield' => '6 db',
            'ingredients' => [
                ['slug' => 'kenyerliszt', 'name' => 'Kenyérliszt', 'unit' => 'g', 'quantity' => 1000],
                ['slug' => 'viz', 'name' => 'Víz', 'unit' => 'ml', 'quantity' => 680],
                ['slug' => 'aktiv-kovasz', 'name' => 'Aktív kovász', 'unit' => 'g', 'quantity' => 180],
                ['slug' => 'so', 'name' => 'Só', 'unit' => 'g', 'quantity' => 20],
            ],
            'steps' => [
                ['title' => 'Dagasztás', 'type' => RecipeStep::TYPE_MIXING, 'duration' => 20, 'wait' => 0, 'temp' => 24],
                ['title' => 'Bulk fermentáció', 'type' => RecipeStep::TYPE_PROOFING, 'duration' => 10, 'wait' => 180, 'temp' => 24],
                ['title' => 'Előformázás', 'type' => RecipeStep::TYPE_PREPARATION, 'duration' => 20, 'wait' => 20, 'temp' => null],
                ['title' => 'Végső formázás és kelesztés', 'type' => RecipeStep::TYPE_PROOFING, 'duration' => 25, 'wait' => 60, 'temp' => 24],
                ['title' => 'Sütés gőzzel', 'type' => RecipeStep::TYPE_BAKING, 'duration' => 24, 'wait' => 0, 'temp' => 245],
            ],
        ],
        'fahejas-csiga' => [
            'product' => [
                'name' => 'Fahéjas csiga',
                'category_slug' => 'edes-pekaru',
                'price' => 1090,
                'short_description' => 'Puha kelt csiga barna cukros, fahéjas töltelékkel.',
                'description' => 'Tizennyolc darabos batch, külön számolt fahéjas töltelékkel.',
                'stock_status' => Product::STOCK_IN_STOCK,
                'sort_order' => 72,
            ],
            'yield' => '18 db',
            'ingredients' => [
                ['slug' => 'liszt', 'name' => 'Búzakenyérliszt', 'unit' => 'g', 'quantity' => 1000],
                ['slug' => 'tej', 'name' => 'Tej', 'unit' => 'ml', 'quantity' => 520],
                ['slug' => 'vaj', 'name' => 'Vaj', 'unit' => 'g', 'quantity' => 250],
                ['slug' => 'cukor', 'name' => 'Cukor', 'unit' => 'g', 'quantity' => 150],
                ['slug' => 'tojas', 'name' => 'Tojás', 'unit' => 'g', 'quantity' => 100],
                ['slug' => 'eleszto', 'name' => 'Élesztő', 'unit' => 'g', 'quantity' => 30],
                ['slug' => 'so', 'name' => 'Só', 'unit' => 'g', 'quantity' => 15],
                ['slug' => 'fahej', 'name' => 'Fahéj', 'unit' => 'g', 'quantity' => 25],
                ['slug' => 'barna-cukor', 'name' => 'Barna cukor', 'unit' => 'g', 'quantity' => 180],
            ],
            'steps' => [
                ['title' => 'Tészta dagasztása', 'type' => RecipeStep::TYPE_MIXING, 'duration' => 25, 'wait' => 0, 'temp' => 24],
                ['title' => 'Első kelesztés', 'type' => RecipeStep::TYPE_PROOFING, 'duration' => 5, 'wait' => 75, 'temp' => 26],
                ['title' => 'Fahéjas töltés és tekerés', 'type' => RecipeStep::TYPE_PREPARATION, 'duration' => 30, 'wait' => 0, 'temp' => null],
                ['title' => 'Második kelesztés', 'type' => RecipeStep::TYPE_PROOFING, 'duration' => 5, 'wait' => 45, 'temp' => 26],
                ['title' => 'Sütés', 'type' => RecipeStep::TYPE_BAKING, 'duration' => 22, 'wait' => 0, 'temp' => 190],
            ],
        ],
        'teljes-kiorlesu-kenyer' => [
            'product' => [
                'name' => 'Teljes kiőrlésű kenyér',
                'category_slug' => 'kenyerek',
                'price' => 2590,
                'short_description' => 'Teljes kiőrlésű liszttel, kovásszal és mézzel lágyított vekni.',
                'description' => 'Két veknire számolt, magas rosttartalmú kovászos kenyér.',
                'stock_status' => Product::STOCK_PREORDER,
                'sort_order' => 6,
            ],
            'yield' => '2 db vekni',
            'ingredients' => [
                ['slug' => 'teljes-kiorlesu-liszt', 'name' => 'Teljes kiőrlésű liszt', 'unit' => 'g', 'quantity' => 700],
                ['slug' => 'bl80-kenyerliszt', 'name' => 'BL80 kenyérliszt', 'unit' => 'g', 'quantity' => 300],
                ['slug' => 'viz', 'name' => 'Víz', 'unit' => 'ml', 'quantity' => 760],
                ['slug' => 'aktiv-kovasz', 'name' => 'Aktív kovász', 'unit' => 'g', 'quantity' => 220],
                ['slug' => 'so', 'name' => 'Só', 'unit' => 'g', 'quantity' => 22],
                ['slug' => 'mez', 'name' => 'Méz', 'unit' => 'g', 'quantity' => 30],
            ],
            'steps' => [
                ['title' => 'Teljes kiőrlésű liszt hidratálása', 'type' => RecipeStep::TYPE_PREPARATION, 'duration' => 10, 'wait' => 45, 'temp' => 24],
                ['title' => 'Dagasztás kovásszal', 'type' => RecipeStep::TYPE_MIXING, 'duration' => 25, 'wait' => 0, 'temp' => 24],
                ['title' => 'Bulk fermentáció', 'type' => RecipeStep::TYPE_PROOFING, 'duration' => 20, 'wait' => 210, 'temp' => 24],
                ['title' => 'Formázás és kelesztés', 'type' => RecipeStep::TYPE_PROOFING, 'duration' => 25, 'wait' => 120, 'temp' => 24],
                ['title' => 'Sütés', 'type' => RecipeStep::TYPE_BAKING, 'duration' => 45, 'wait' => 0, 'temp' => 235],
            ],
        ],
        'croissant' => [
            'product' => [
                'name' => 'Croissant',
                'category_slug' => 'edes-pekaru',
                'price' => 1390,
                'short_description' => 'Vajas, hajtogatott tészta ropogós rétegekkel.',
                'description' => 'Huszonnégy darabos batch, számolható lamináló vajmennyiséggel.',
                'stock_status' => Product::STOCK_PREORDER,
                'sort_order' => 71,
            ],
            'yield' => '24 db',
            'ingredients' => [
                ['slug' => 'liszt', 'name' => 'Búzakenyérliszt', 'unit' => 'g', 'quantity' => 1000],
                ['slug' => 'tej', 'name' => 'Tej', 'unit' => 'ml', 'quantity' => 420],
                ['slug' => 'viz', 'name' => 'Víz', 'unit' => 'ml', 'quantity' => 180],
                ['slug' => 'cukor', 'name' => 'Cukor', 'unit' => 'g', 'quantity' => 120],
                ['slug' => 'so', 'name' => 'Só', 'unit' => 'g', 'quantity' => 20],
                ['slug' => 'eleszto', 'name' => 'Élesztő', 'unit' => 'g', 'quantity' => 35],
                ['slug' => 'vaj', 'name' => 'Vaj', 'unit' => 'g', 'quantity' => 500],
                ['slug' => 'tojas', 'name' => 'Tojás', 'unit' => 'g', 'quantity' => 100],
            ],
            'steps' => [
                ['title' => 'Alaptészta dagasztása', 'type' => RecipeStep::TYPE_MIXING, 'duration' => 20, 'wait' => 0, 'temp' => 22],
                ['title' => 'Hideg pihentetés', 'type' => RecipeStep::TYPE_RESTING, 'duration' => 5, 'wait' => 360, 'temp' => 5],
                ['title' => 'Vajblokk előkészítése', 'type' => RecipeStep::TYPE_PREPARATION, 'duration' => 20, 'wait' => 0, 'temp' => 14],
                ['title' => 'Laminálás három hajtással', 'type' => RecipeStep::TYPE_PREPARATION, 'duration' => 45, 'wait' => 90, 'temp' => 14],
                ['title' => 'Vágás és formázás', 'type' => RecipeStep::TYPE_PREPARATION, 'duration' => 35, 'wait' => 0, 'temp' => null],
                ['title' => 'Végső kelesztés', 'type' => RecipeStep::TYPE_PROOFING, 'duration' => 5, 'wait' => 120, 'temp' => 25],
                ['title' => 'Tojásozás és sütés', 'type' => RecipeStep::TYPE_BAKING, 'duration' => 25, 'wait' => 0, 'temp' => 190],
            ],
        ],
        'magvas-vekni' => [
            'product' => [
                'name' => 'Magvas vekni',
                'category_slug' => 'kenyerek',
                'price' => 2690,
                'short_description' => 'Kovászos vekni napraforgóval, lenmaggal és szezámmaggal.',
                'description' => 'Két veknire számolt magvas kenyér, áztatott magkeverékkel.',
                'stock_status' => Product::STOCK_IN_STOCK,
                'sort_order' => 2,
            ],
            'yield' => '2 db vekni',
            'ingredients' => [
                ['slug' => 'bl80-kenyerliszt', 'name' => 'BL80 kenyérliszt', 'unit' => 'g', 'quantity' => 700],
                ['slug' => 'teljes-kiorlesu-liszt', 'name' => 'Teljes kiőrlésű liszt', 'unit' => 'g', 'quantity' => 300],
                ['slug' => 'viz', 'name' => 'Víz', 'unit' => 'ml', 'quantity' => 750],
                ['slug' => 'aktiv-kovasz', 'name' => 'Aktív kovász', 'unit' => 'g', 'quantity' => 200],
                ['slug' => 'so', 'name' => 'Só', 'unit' => 'g', 'quantity' => 22],
                ['slug' => 'napraforgomag', 'name' => 'Napraforgómag', 'unit' => 'g', 'quantity' => 120],
                ['slug' => 'lenmag', 'name' => 'Lenmag', 'unit' => 'g', 'quantity' => 80],
                ['slug' => 'szezammag', 'name' => 'Szezámmag', 'unit' => 'g', 'quantity' => 60],
            ],
            'steps' => [
                ['title' => 'Magok beáztatása', 'type' => RecipeStep::TYPE_PREPARATION, 'duration' => 10, 'wait' => 120, 'temp' => null],
                ['title' => 'Dagasztás', 'type' => RecipeStep::TYPE_MIXING, 'duration' => 25, 'wait' => 0, 'temp' => 24],
                ['title' => 'Bulk fermentáció', 'type' => RecipeStep::TYPE_PROOFING, 'duration' => 20, 'wait' => 210, 'temp' => 24],
                ['title' => 'Formázás', 'type' => RecipeStep::TYPE_PREPARATION, 'duration' => 20, 'wait' => 60, 'temp' => 24],
                ['title' => 'Sütés', 'type' => RecipeStep::TYPE_BAKING, 'duration' => 45, 'wait' => 0, 'temp' => 235],
            ],
        ],
    ];

    public function run(): void
    {
        DB::transaction(function (): void {
            foreach ($this->recipes as $slug => $recipe) {
                $product = $this->syncProduct($slug, $recipe['product']);
                $ingredientIds = $this->syncIngredients($product, $recipe);

                ProductIngredient::query()
                    ->where('product_id', $product->id)
                    ->whereNotIn('ingredient_id', $ingredientIds)
                    ->delete();

                $this->syncSteps($product, $recipe['steps'], (string) $recipe['yield']);
            }
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function syncProduct(string $slug, array $data): Product
    {
        $category = Category::query()->firstOrCreate(
            ['slug' => $data['category_slug']],
            [
                'name' => Str::headline((string) $data['category_slug']),
                'is_active' => true,
                'sort_order' => 99,
            ],
        );

        return Product::query()->updateOrCreate(
            ['slug' => $slug],
            [
                'category_id' => $category->id,
                'name' => $data['name'],
                'short_description' => $data['short_description'],
                'description' => $data['description'],
                'price' => $data['price'],
                'is_active' => true,
                'is_featured' => false,
                'stock_status' => $data['stock_status'],
                'image_path' => "products/{$slug}.jpg",
                'sort_order' => $data['sort_order'],
            ],
        );
    }

    /**
     * @param  array<string, mixed>  $recipe
     * @return array<int, int>
     */
    private function syncIngredients(Product $product, array $recipe): array
    {
        $ingredientIds = [];

        foreach ($recipe['ingredients'] as $index => $item) {
            $ingredient = $this->ingredient($item);
            $ingredientIds[] = $ingredient->id;

            ProductIngredient::query()->updateOrCreate(
                [
                    'product_id' => $product->id,
                    'ingredient_id' => $ingredient->id,
                ],
                [
                    'quantity' => $item['quantity'],
                    'sort_order' => $index + 1,
                    'notes' => sprintf('Alap batch: %s', $recipe['yield']),
                ],
            );
        }

        return $ingredientIds;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function ingredient(array $data): Ingredient
    {
        $ingredient = Ingredient::query()
            ->where('slug', $data['slug'])
            ->orWhere('name', $data['name'])
            ->first();

        $payload = [
            'name' => $data['name'],
            'slug' => $data['slug'],
            'sku' => 'ING-'.Str::upper(Str::slug((string) $data['slug'])),
            'unit' => $data['unit'],
            'estimated_unit_cost' => $data['estimated_unit_cost'] ?? $this->defaultUnitCost((string) $data['slug']),
            'current_stock' => $data['current_stock'] ?? $this->defaultStock((string) $data['slug']),
            'minimum_stock' => $data['minimum_stock'] ?? $this->defaultMinimumStock((string) $data['slug']),
            'is_active' => true,
            'notes' => null,
        ];

        if ($ingredient instanceof Ingredient) {
            $ingredient->update($payload);

            return $ingredient->refresh();
        }

        return Ingredient::query()->create($payload);
    }

    /**
     * @param  array<int, array<string, mixed>>  $steps
     */
    private function syncSteps(Product $product, array $steps, string $yield): void
    {
        RecipeStep::query()->where('product_id', $product->id)->delete();

        foreach ($steps as $index => $step) {
            RecipeStep::query()->create([
                'product_id' => $product->id,
                'title' => $step['title'],
                'step_type' => $step['type'],
                'description' => sprintf('%s alap batchre számolva: %s.', $step['title'], $yield),
                'work_instruction' => $this->instructionFor((string) $step['type']),
                'completion_criteria' => $this->completionFor((string) $step['type']),
                'attention_points' => 'A tészta hőmérsékletét és állagát a műszakvezető ellenőrzi.',
                'required_tools' => 'Mérleg, dagasztógép, szakajtó vagy tepsi, sütő',
                'expected_result' => 'Következő munkafázisra kész, stabil minőségű félkész termék.',
                'duration_minutes' => $step['duration'],
                'wait_minutes' => $step['wait'],
                'temperature_celsius' => $step['temp'],
                'sort_order' => $index + 1,
                'is_active' => true,
            ]);
        }
    }

    private function defaultUnitCost(string $slug): int
    {
        return match ($slug) {
            'vaj' => 1500,
            'olivaolaj' => 3500,
            'kakao' => 4500,
            'fahej', 'rozmaring' => 9000,
            'eleszto' => 25,
            'so' => 1500,
            'cukor', 'barna-cukor' => 520,
            'tej' => 199,
            'tojas' => 99,
            'aktiv-kovasz', 'kovasz' => 250,
            'napraforgomag', 'lenmag', 'szezammag' => 2800,
            default => 540,
        };
    }

    private function defaultStock(string $slug): int
    {
        return match ($slug) {
            'eleszto' => 750,
            'fahej', 'rozmaring' => 1200,
            'kakao', 'mez' => 5000,
            'vaj', 'tej', 'tojas' => 20000,
            'aktiv-kovasz', 'kovasz' => 8000,
            'napraforgomag', 'lenmag', 'szezammag' => 6000,
            default => 50000,
        };
    }

    private function defaultMinimumStock(string $slug): int
    {
        return match ($slug) {
            'eleszto' => 500,
            'fahej', 'rozmaring' => 200,
            'kakao', 'mez' => 500,
            'vaj', 'tej', 'tojas' => 2000,
            'aktiv-kovasz', 'kovasz' => 500,
            'napraforgomag', 'lenmag', 'szezammag' => 500,
            default => 5000,
        };
    }

    private function instructionFor(string $type): string
    {
        return match ($type) {
            RecipeStep::TYPE_MIXING => 'Mérd ki az alapanyagokat, majd dagaszd a recepthez illő sikérfejlődésig.',
            RecipeStep::TYPE_PROOFING => 'Tartsd a megadott hőmérsékleti tartományt, és a megadott idő alatt ellenőrizd a térfogatot.',
            RecipeStep::TYPE_BAKING => 'Előmelegített sütőben, a termékhez illő gőzzel és hőmérséklettel süsd.',
            RecipeStep::TYPE_COOLING => 'Rácson hűtsd, csomagolás vagy szeletelés előtt stabilizáld a héjat és a belzetet.',
            RecipeStep::TYPE_FINISHING => 'Végezd el a végső felületkezelést vagy toppingolást.',
            default => 'Készítsd elő az adott munkafázishoz szükséges alapanyagokat és eszközöket.',
        };
    }

    private function completionFor(string $type): string
    {
        return match ($type) {
            RecipeStep::TYPE_MIXING => 'A tészta egynemű, rugalmas, és nem marad száraz liszt a csészében.',
            RecipeStep::TYPE_PROOFING => 'A tészta láthatóan lazább, levegősebb, de nem esett túl a kelésen.',
            RecipeStep::TYPE_BAKING => 'A termék héja színes, stabil, az alja kopogtatva üreges hangot ad.',
            RecipeStep::TYPE_COOLING => 'A termék szeletelhető vagy csomagolható állapotra hűlt.',
            default => 'A munkafázis rendezett, mérhető és átadható a következő lépésre.',
        };
    }
}
