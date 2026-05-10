<?php

use App\Data\ProductionPlans\ProductionPlanStoreData;
use App\Models\Product;
use App\Models\ProductionPlan;
use App\Models\User;
use App\Services\ProductionPlanService;
use Database\Seeders\CategorySeeder;
use Database\Seeders\IngredientSeeder;
use Database\Seeders\ProductSeeder;
use Database\Seeders\RealisticBakeryRecipeSeeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

function realisticRecipeSlugs(): array
{
    return [
        'klasszikus-kovaszos-kenyer',
        'rozmaringos-focaccia',
        'kakaos-csiga',
        'brios',
        'kovaszos-bagett',
        'fahejas-csiga',
        'teljes-kiorlesu-kenyer',
        'croissant',
        'magvas-vekni',
    ];
}

beforeEach(function (): void {
    $this->seed([
        CategorySeeder::class,
        ProductSeeder::class,
        IngredientSeeder::class,
        RealisticBakeryRecipeSeeder::class,
    ]);
});

it('realistic recipe seeder runs and creates curated bakery products', function (): void {
    foreach (realisticRecipeSlugs() as $slug) {
        $this->assertDatabaseHas('products', [
            'slug' => $slug,
            'is_active' => true,
        ]);
    }
});

it('every curated seeded product has ingredients and positive quantities', function (): void {
    $products = Product::query()
        ->with('productIngredients.ingredient')
        ->whereIn('slug', realisticRecipeSlugs())
        ->get();

    expect($products)->toHaveCount(count(realisticRecipeSlugs()));

    foreach ($products as $product) {
        expect($product->productIngredients)->not->toBeEmpty();

        foreach ($product->productIngredients as $item) {
            expect((float) $item->quantity)->toBeGreaterThan(0);
            expect($item->ingredient)->not->toBeNull();
            expect($item->ingredient->unit)->toBeIn(['g', 'ml']);
        }
    }
});

it('does not leave duplicate product ingredient relationships', function (): void {
    $duplicates = DB::table('product_ingredients')
        ->select('product_id', 'ingredient_id', DB::raw('COUNT(*) as duplicate_count'))
        ->groupBy('product_id', 'ingredient_id')
        ->having('duplicate_count', '>', 1)
        ->count();

    expect($duplicates)->toBe(0);
});

it('keeps recipe step order stable and positive for curated products', function (): void {
    $products = Product::query()
        ->with('recipeSteps')
        ->whereIn('slug', realisticRecipeSlugs())
        ->get();

    foreach ($products as $product) {
        expect($product->recipeSteps)->not->toBeEmpty();
        expect($product->recipeSteps->pluck('sort_order')->values()->all())->toBe(range(1, $product->recipeSteps->count()));

        foreach ($product->recipeSteps as $step) {
            expect((int) $step->duration_minutes + (int) $step->wait_minutes)->toBeGreaterThan(0);
        }
    }
});

it('production plan ingredient aggregation works with seeded recipe data', function (): void {
    Carbon::setTestNow(Carbon::parse('2026-05-06 10:00:00'));

    $user = User::factory()->create();
    $classicBread = Product::query()->where('slug', 'klasszikus-kovaszos-kenyer')->firstOrFail();
    $seededLoaf = Product::query()->where('slug', 'magvas-vekni')->firstOrFail();

    $plan = app(ProductionPlanService::class)->create(ProductionPlanStoreData::from([
        'target_ready_at' => Carbon::now()->addDays(2)->toDateTimeString(),
        'status' => ProductionPlan::STATUS_DRAFT,
        'is_locked' => false,
        'notes' => 'Seeder aggregációs ellenőrzés',
        'items' => [
            ['product_id' => $classicBread->id, 'target_quantity' => 2, 'unit_label' => 'db', 'sort_order' => 0],
            ['product_id' => $seededLoaf->id, 'target_quantity' => 3, 'unit_label' => 'db', 'sort_order' => 1],
        ],
    ]), $user->id);

    $requirements = collect(app(ProductionPlanService::class)->buildPlanPayload($plan)['ingredient_requirements']);
    $flour = $requirements->firstWhere('name', 'BL80 kenyérliszt');

    expect($flour)->not->toBeNull();
    expect($flour['total_required'])->toBe(4100.0);

    Carbon::setTestNow();
});
