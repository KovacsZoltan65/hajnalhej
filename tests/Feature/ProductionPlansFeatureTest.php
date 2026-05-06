<?php

use App\Models\Category;
use App\Models\Ingredient;
use App\Models\Product;
use App\Models\ProductIngredient;
use App\Models\ProductionPlan;
use App\Models\RecipeStep;
use App\Models\User;
use App\Services\ProductionPlanService;
use Illuminate\Support\Carbon;
use Inertia\Testing\AssertableInertia as Assert;

function requirementRowsFor(ProductionPlan $plan): array
{
    return app(ProductionPlanService::class)->buildPlanPayload($plan->refresh())['ingredient_requirements'];
}

function requirementRowByName(ProductionPlan $plan, string $name): ?array
{
    return collect(requirementRowsFor($plan))->firstWhere('name', $name);
}

it('production plans guest nem fer hozza admin indexhez', function (): void {
    $response = $this->get('/admin/production-plans');

    $response->assertRedirect('/login');
});

it('production plans auth user hozzafer', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/admin/production-plans');

    $response
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('Admin/ProductionPlans/Index'));
});

it('production plan create flow get route elerheto', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/admin/production-plans/create-flow');

    $response
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('Admin/ProductionPlans/CreateFlow'));
});

it('production plan create-flow sikeresen ment items es steps rekordokkal', function (): void {
    $user = User::factory()->create();
    $category = Category::factory()->create(['is_active' => true]);
    $product = Product::factory()->create(['category_id' => $category->id, 'is_active' => true]);
    $ingredient = Ingredient::factory()->create([
        'unit' => 'g',
        'current_stock' => 10000,
        'minimum_stock' => 1000,
    ]);

    ProductIngredient::factory()->create([
        'product_id' => $product->id,
        'ingredient_id' => $ingredient->id,
        'quantity' => 250,
    ]);

    RecipeStep::factory()->create([
        'product_id' => $product->id,
        'title' => 'Dagasztas',
        'duration_minutes' => 20,
        'wait_minutes' => 40,
        'is_active' => true,
    ]);

    $response = $this->actingAs($user)->post('/admin/production-plans/create-flow', [
        'target_ready_at' => Carbon::tomorrow()->setTime(9, 0)->toDateTimeString(),
        'notes' => 'Reggeli gyartas',
        'items' => [
            [
                'product_id' => $product->id,
                'target_quantity' => 4,
                'unit_label' => 'db',
                'sort_order' => 0,
            ],
        ],
    ]);

    $plan = ProductionPlan::query()->firstOrFail();

    $response->assertRedirect("/admin/production-plans/{$plan->id}");
    $this->assertDatabaseHas('production_plans', [
        'id' => $plan->id,
        'status' => ProductionPlan::STATUS_CALCULATED,
        'notes' => 'Reggeli gyartas',
    ]);
    $this->assertDatabaseHas('production_plan_items', [
        'production_plan_id' => $plan->id,
        'product_id' => $product->id,
        'target_quantity' => '4.000',
    ]);
    $this->assertDatabaseHas('production_plan_steps', [
        'production_plan_id' => $plan->id,
        'product_id' => $product->id,
        'title' => "{$product->name} - Dagasztas",
    ]);
});

it('production plan create-flow validacio hibazik ures items eseten', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/admin/production-plans/create-flow', [
        'target_ready_at' => Carbon::tomorrow()->toDateTimeString(),
        'items' => [],
    ]);

    $response->assertSessionHasErrors(['items']);
});

it('production plan create-flow validacio hibazik invalid product eseten', function (): void {
    $user = User::factory()->create();
    $product = Product::factory()->create(['is_active' => false]);

    $response = $this->actingAs($user)->post('/admin/production-plans/create-flow', [
        'target_ready_at' => Carbon::tomorrow()->toDateTimeString(),
        'items' => [
            [
                'product_id' => $product->id,
                'target_quantity' => 1,
            ],
        ],
    ]);

    $response->assertSessionHasErrors(['items.0.product_id']);
});

it('production plan create-flow validacio hibazik nem pozitiv quantity eseten', function (): void {
    $user = User::factory()->create();
    $product = Product::factory()->create(['is_active' => true]);

    $response = $this->actingAs($user)->post('/admin/production-plans/create-flow', [
        'target_ready_at' => Carbon::tomorrow()->toDateTimeString(),
        'items' => [
            [
                'product_id' => $product->id,
                'target_quantity' => 0,
            ],
        ],
    ]);

    $response->assertSessionHasErrors(['items.0.target_quantity']);
});

it('production plan create-flow validacio hibazik multbeli target_ready_at eseten', function (): void {
    Carbon::setTestNow(Carbon::parse('2026-05-06 10:00:00'));

    $user = User::factory()->create();
    $product = Product::factory()->create(['is_active' => true]);

    $response = $this->actingAs($user)->post('/admin/production-plans/create-flow', [
        'target_ready_at' => Carbon::now()->subMinute()->toDateTimeString(),
        'items' => [
            [
                'product_id' => $product->id,
                'target_quantity' => 1,
                'unit_label' => 'db',
            ],
        ],
    ]);

    $response->assertSessionHasErrors(['target_ready_at']);

    Carbon::setTestNow();
});

it('production plan create-flow tul korai target_ready_at eseten receptido alapjan hibazik', function (): void {
    Carbon::setTestNow(Carbon::parse('2026-05-06 10:00:00'));

    $user = User::factory()->create();
    $product = Product::factory()->create(['is_active' => true]);

    RecipeStep::factory()->create([
        'product_id' => $product->id,
        'duration_minutes' => 30,
        'wait_minutes' => 30,
        'is_active' => true,
    ]);

    $response = $this->actingAs($user)->post('/admin/production-plans/create-flow', [
        'target_ready_at' => Carbon::now()->addMinutes(59)->toDateTimeString(),
        'items' => [
            [
                'product_id' => $product->id,
                'target_quantity' => 1,
                'unit_label' => 'db',
            ],
        ],
    ]);

    $response->assertSessionHasErrors(['target_ready_at']);

    Carbon::setTestNow();
});

it('production plan create-flow elfogadja a pontos minimum ready time idopontot', function (): void {
    Carbon::setTestNow(Carbon::parse('2026-05-06 10:00:00'));

    $user = User::factory()->create();
    $product = Product::factory()->create(['is_active' => true]);

    RecipeStep::factory()->create([
        'product_id' => $product->id,
        'duration_minutes' => 30,
        'wait_minutes' => 30,
        'is_active' => true,
    ]);

    $response = $this->actingAs($user)->post('/admin/production-plans/create-flow', [
        'target_ready_at' => Carbon::now()->addMinutes(60)->toDateTimeString(),
        'items' => [
            [
                'product_id' => $product->id,
                'target_quantity' => 1,
                'unit_label' => 'db',
            ],
        ],
    ]);

    $plan = ProductionPlan::query()->firstOrFail();

    $response->assertRedirect("/admin/production-plans/{$plan->id}");

    Carbon::setTestNow();
});

it('production plan create-flow elfogadja a jovobeli target_ready_at idopontot', function (): void {
    Carbon::setTestNow(Carbon::parse('2026-05-06 10:00:00'));

    $user = User::factory()->create();
    $product = Product::factory()->create(['is_active' => true]);

    RecipeStep::factory()->create([
        'product_id' => $product->id,
        'duration_minutes' => 30,
        'wait_minutes' => 30,
        'is_active' => true,
    ]);

    $response = $this->actingAs($user)->post('/admin/production-plans/create-flow', [
        'target_ready_at' => Carbon::now()->addMinutes(61)->toDateTimeString(),
        'items' => [
            [
                'product_id' => $product->id,
                'target_quantity' => 1,
                'unit_label' => 'db',
            ],
        ],
    ]);

    $plan = ProductionPlan::query()->firstOrFail();

    $response->assertRedirect("/admin/production-plans/{$plan->id}");
    $this->assertDatabaseHas('production_plans', [
        'id' => $plan->id,
    ]);

    Carbon::setTestNow();
});

it('production plan create-flow tobb termeknel a leghosszabb receptidot hasznalja', function (): void {
    Carbon::setTestNow(Carbon::parse('2026-05-06 10:00:00'));

    $user = User::factory()->create();
    $shortProduct = Product::factory()->create(['is_active' => true]);
    $longProduct = Product::factory()->create(['is_active' => true]);

    RecipeStep::factory()->create([
        'product_id' => $shortProduct->id,
        'duration_minutes' => 10,
        'wait_minutes' => 20,
        'is_active' => true,
    ]);
    RecipeStep::factory()->create([
        'product_id' => $longProduct->id,
        'duration_minutes' => 45,
        'wait_minutes' => 30,
        'is_active' => true,
    ]);

    $tooEarly = $this->actingAs($user)->post('/admin/production-plans/create-flow', [
        'target_ready_at' => Carbon::now()->addMinutes(74)->toDateTimeString(),
        'items' => [
            ['product_id' => $shortProduct->id, 'target_quantity' => 1, 'unit_label' => 'db'],
            ['product_id' => $longProduct->id, 'target_quantity' => 1, 'unit_label' => 'db'],
        ],
    ]);

    $tooEarly->assertSessionHasErrors(['target_ready_at']);

    $valid = $this->actingAs($user)->post('/admin/production-plans/create-flow', [
        'target_ready_at' => Carbon::now()->addMinutes(75)->toDateTimeString(),
        'items' => [
            ['product_id' => $shortProduct->id, 'target_quantity' => 1, 'unit_label' => 'db'],
            ['product_id' => $longProduct->id, 'target_quantity' => 1, 'unit_label' => 'db'],
        ],
    ]);

    $plan = ProductionPlan::query()->firstOrFail();

    $valid->assertRedirect("/admin/production-plans/{$plan->id}");

    Carbon::setTestNow();
});

it('production plan create-flow receptlepes nelkuli termeknel 15 perc fallback minimumot hasznal', function (): void {
    Carbon::setTestNow(Carbon::parse('2026-05-06 10:00:00'));

    $user = User::factory()->create();
    $product = Product::factory()->create(['is_active' => true]);

    $tooEarly = $this->actingAs($user)->post('/admin/production-plans/create-flow', [
        'target_ready_at' => Carbon::now()->addMinutes(14)->toDateTimeString(),
        'items' => [
            ['product_id' => $product->id, 'target_quantity' => 1, 'unit_label' => 'db'],
        ],
    ]);

    $tooEarly->assertSessionHasErrors(['target_ready_at']);

    $valid = $this->actingAs($user)->post('/admin/production-plans/create-flow', [
        'target_ready_at' => Carbon::now()->addMinutes(15)->toDateTimeString(),
        'items' => [
            ['product_id' => $product->id, 'target_quantity' => 1, 'unit_label' => 'db'],
        ],
    ]);

    $plan = ProductionPlan::query()->firstOrFail();

    $valid->assertRedirect("/admin/production-plans/{$plan->id}");

    Carbon::setTestNow();
});

it('production plan create kiszamolja idot es inditast', function (): void {
    $user = User::factory()->create();
    $category = Category::factory()->create(['is_active' => true]);
    $product = Product::factory()->create(['category_id' => $category->id]);
    $ingredient = Ingredient::factory()->create([
        'unit' => 'g',
        'current_stock' => 15000,
        'minimum_stock' => 1000,
    ]);

    ProductIngredient::factory()->create([
        'product_id' => $product->id,
        'ingredient_id' => $ingredient->id,
        'quantity' => 500,
        'sort_order' => 0,
    ]);

    RecipeStep::factory()->create([
        'product_id' => $product->id,
        'duration_minutes' => 30,
        'wait_minutes' => 90,
        'is_active' => true,
    ]);

    $targetAt = Carbon::tomorrow()->setTime(8, 0)->toDateTimeString();

    $response = $this->actingAs($user)
        ->post('/admin/production-plans', [
            'target_ready_at' => $targetAt,
            'status' => 'draft',
            'notes' => 'Holnap reggel 8-ra 20 kenyer kell.',
            'items' => [
                [
                    'product_id' => $product->id,
                    'target_quantity' => 20,
                    'unit_label' => 'db',
                    'sort_order' => 0,
                ],
            ],
        ]);

    $response->assertRedirect('/admin/production-plans');

    $plan = ProductionPlan::query()->firstOrFail();

    expect($plan->total_active_minutes)->toBe(600);
    expect($plan->total_wait_minutes)->toBe(1800);
    expect($plan->total_recipe_minutes)->toBe(2400);
    expect($plan->planned_start_at?->toDateTimeString())->toBe(Carbon::parse($targetAt)->subMinutes(2400)->toDateTimeString());

    $this->assertDatabaseHas('production_plan_items', [
        'production_plan_id' => $plan->id,
        'product_id' => $product->id,
        'target_quantity' => '20.000',
        'computed_ingredient_count' => 1,
        'computed_step_count' => 1,
    ]);
});

it('production plan duplicate product validacio hibazik', function (): void {
    $user = User::factory()->create();
    $category = Category::factory()->create(['is_active' => true]);
    $product = Product::factory()->create(['category_id' => $category->id]);

    $response = $this->actingAs($user)
        ->post('/admin/production-plans', [
            'target_ready_at' => Carbon::tomorrow()->setTime(8, 0)->toDateTimeString(),
            'items' => [
                [
                    'product_id' => $product->id,
                    'target_quantity' => 10,
                    'unit_label' => 'db',
                    'sort_order' => 0,
                ],
                [
                    'product_id' => $product->id,
                    'target_quantity' => 12,
                    'unit_label' => 'db',
                    'sort_order' => 1,
                ],
            ],
        ]);

    $response->assertSessionHasErrors(['items.1.product_id']);
});

it('production plan update mukodik', function (): void {
    $user = User::factory()->create();
    $category = Category::factory()->create(['is_active' => true]);
    $product = Product::factory()->create(['category_id' => $category->id]);
    $plan = ProductionPlan::factory()->create([
        'status' => 'draft',
        'is_locked' => false,
    ]);

    $plan->items()->create([
        'product_id' => $product->id,
        'product_name_snapshot' => $product->name,
        'product_slug_snapshot' => $product->slug,
        'target_quantity' => 10,
        'unit_label' => 'db',
        'sort_order' => 0,
    ]);

    $response = $this->actingAs($user)
        ->put("/admin/production-plans/{$plan->id}", [
            'target_ready_at' => Carbon::tomorrow()->setTime(7, 30)->toDateTimeString(),
            'status' => 'ready',
            'is_locked' => true,
            'notes' => 'Veglegesitett muszakterv',
            'items' => [
                [
                    'product_id' => $product->id,
                    'target_quantity' => 14,
                    'unit_label' => 'db',
                    'sort_order' => 0,
                ],
            ],
        ]);

    $response->assertRedirect('/admin/production-plans');
    $this->assertDatabaseHas('production_plans', [
        'id' => $plan->id,
        'status' => 'ready',
        'is_locked' => true,
        'notes' => 'Veglegesitett muszakterv',
    ]);
});

it('production plan update quantity recalculates ingredient requirements', function (): void {
    $user = User::factory()->create();
    $product = Product::factory()->create(['is_active' => true]);
    $flour = Ingredient::factory()->create(['name' => 'Liszt', 'unit' => 'g', 'current_stock' => 1000]);

    ProductIngredient::factory()->create([
        'product_id' => $product->id,
        'ingredient_id' => $flour->id,
        'quantity' => 100,
    ]);

    $plan = ProductionPlan::factory()->create(['status' => 'draft']);
    $plan->items()->create([
        'product_id' => $product->id,
        'product_name_snapshot' => $product->name,
        'product_slug_snapshot' => $product->slug,
        'target_quantity' => 2,
        'unit_label' => 'db',
        'sort_order' => 0,
    ]);

    $this->actingAs($user)->put("/admin/production-plans/{$plan->id}", [
        'target_ready_at' => Carbon::tomorrow()->setTime(9, 0)->toDateTimeString(),
        'status' => 'draft',
        'is_locked' => false,
        'items' => [
            ['product_id' => $product->id, 'target_quantity' => 5, 'unit_label' => 'db', 'sort_order' => 0],
        ],
    ])->assertRedirect('/admin/production-plans');

    expect(requirementRowByName($plan, 'Liszt')['total_required'])->toBe(500.0);
});

it('production plan update add product recalculates ingredient requirements', function (): void {
    $user = User::factory()->create();
    $bread = Product::factory()->create(['is_active' => true]);
    $baguette = Product::factory()->create(['is_active' => true]);
    $flour = Ingredient::factory()->create(['name' => 'Liszt', 'unit' => 'g', 'current_stock' => 5000]);
    $yeast = Ingredient::factory()->create(['name' => 'Élesztő', 'unit' => 'g', 'current_stock' => 100]);

    ProductIngredient::factory()->create(['product_id' => $bread->id, 'ingredient_id' => $flour->id, 'quantity' => 500]);
    ProductIngredient::factory()->create(['product_id' => $baguette->id, 'ingredient_id' => $yeast->id, 'quantity' => 8]);

    $plan = ProductionPlan::factory()->create(['status' => 'draft']);
    $plan->items()->create([
        'product_id' => $bread->id,
        'product_name_snapshot' => $bread->name,
        'product_slug_snapshot' => $bread->slug,
        'target_quantity' => 1,
        'unit_label' => 'db',
        'sort_order' => 0,
    ]);

    $this->actingAs($user)->put("/admin/production-plans/{$plan->id}", [
        'target_ready_at' => Carbon::tomorrow()->setTime(9, 0)->toDateTimeString(),
        'status' => 'draft',
        'is_locked' => false,
        'items' => [
            ['product_id' => $bread->id, 'target_quantity' => 1, 'unit_label' => 'db', 'sort_order' => 0],
            ['product_id' => $baguette->id, 'target_quantity' => 3, 'unit_label' => 'db', 'sort_order' => 1],
        ],
    ])->assertRedirect('/admin/production-plans');

    expect(requirementRowByName($plan, 'Liszt')['total_required'])->toBe(500.0);
    expect(requirementRowByName($plan, 'Élesztő')['total_required'])->toBe(24.0);
});

it('production plan update remove product recalculates ingredient requirements', function (): void {
    $user = User::factory()->create();
    $bread = Product::factory()->create(['is_active' => true]);
    $baguette = Product::factory()->create(['is_active' => true]);
    $flour = Ingredient::factory()->create(['name' => 'Liszt', 'unit' => 'g']);
    $yeast = Ingredient::factory()->create(['name' => 'Élesztő', 'unit' => 'g']);

    ProductIngredient::factory()->create(['product_id' => $bread->id, 'ingredient_id' => $flour->id, 'quantity' => 500]);
    ProductIngredient::factory()->create(['product_id' => $baguette->id, 'ingredient_id' => $yeast->id, 'quantity' => 8]);

    $plan = ProductionPlan::factory()->create(['status' => 'draft']);
    foreach ([[$bread, 1, 0], [$baguette, 2, 1]] as [$product, $quantity, $sortOrder]) {
        $plan->items()->create([
            'product_id' => $product->id,
            'product_name_snapshot' => $product->name,
            'product_slug_snapshot' => $product->slug,
            'target_quantity' => $quantity,
            'unit_label' => 'db',
            'sort_order' => $sortOrder,
        ]);
    }

    $this->actingAs($user)->put("/admin/production-plans/{$plan->id}", [
        'target_ready_at' => Carbon::tomorrow()->setTime(9, 0)->toDateTimeString(),
        'status' => 'draft',
        'is_locked' => false,
        'items' => [
            ['product_id' => $bread->id, 'target_quantity' => 1, 'unit_label' => 'db', 'sort_order' => 0],
        ],
    ])->assertRedirect('/admin/production-plans');

    expect(requirementRowByName($plan, 'Liszt')['total_required'])->toBe(500.0);
    expect(requirementRowByName($plan, 'Élesztő'))->toBeNull();
    $this->assertDatabaseMissing('production_plan_items', [
        'production_plan_id' => $plan->id,
        'product_id' => $baguette->id,
    ]);
});

it('production plan update product change replaces ingredient requirements', function (): void {
    $user = User::factory()->create();
    $bread = Product::factory()->create(['is_active' => true]);
    $baguette = Product::factory()->create(['is_active' => true]);
    $flour = Ingredient::factory()->create(['name' => 'Liszt', 'unit' => 'g']);
    $yeast = Ingredient::factory()->create(['name' => 'Élesztő', 'unit' => 'g']);

    ProductIngredient::factory()->create(['product_id' => $bread->id, 'ingredient_id' => $flour->id, 'quantity' => 500]);
    ProductIngredient::factory()->create(['product_id' => $baguette->id, 'ingredient_id' => $yeast->id, 'quantity' => 8]);

    $plan = ProductionPlan::factory()->create(['status' => 'draft']);
    $plan->items()->create([
        'product_id' => $bread->id,
        'product_name_snapshot' => $bread->name,
        'product_slug_snapshot' => $bread->slug,
        'target_quantity' => 1,
        'unit_label' => 'db',
        'sort_order' => 0,
    ]);

    $this->actingAs($user)->put("/admin/production-plans/{$plan->id}", [
        'target_ready_at' => Carbon::tomorrow()->setTime(9, 0)->toDateTimeString(),
        'status' => 'draft',
        'is_locked' => false,
        'items' => [
            ['product_id' => $baguette->id, 'target_quantity' => 4, 'unit_label' => 'db', 'sort_order' => 0],
        ],
    ])->assertRedirect('/admin/production-plans');

    expect(requirementRowByName($plan, 'Liszt'))->toBeNull();
    expect(requirementRowByName($plan, 'Élesztő')['total_required'])->toBe(32.0);
});

it('production plan identical ingredients are aggregated correctly', function (): void {
    $user = User::factory()->create();
    $bread = Product::factory()->create(['is_active' => true]);
    $baguette = Product::factory()->create(['is_active' => true]);
    $flour = Ingredient::factory()->create(['name' => 'Liszt', 'unit' => 'g', 'current_stock' => 3000]);

    ProductIngredient::factory()->create(['product_id' => $bread->id, 'ingredient_id' => $flour->id, 'quantity' => 500]);
    ProductIngredient::factory()->create(['product_id' => $baguette->id, 'ingredient_id' => $flour->id, 'quantity' => 300]);

    $plan = ProductionPlan::factory()->create(['status' => 'draft']);

    $this->actingAs($user)->put("/admin/production-plans/{$plan->id}", [
        'target_ready_at' => Carbon::tomorrow()->setTime(9, 0)->toDateTimeString(),
        'status' => 'draft',
        'is_locked' => false,
        'items' => [
            ['product_id' => $bread->id, 'target_quantity' => 2, 'unit_label' => 'db', 'sort_order' => 0],
            ['product_id' => $baguette->id, 'target_quantity' => 3, 'unit_label' => 'db', 'sort_order' => 1],
        ],
    ])->assertRedirect('/admin/production-plans');

    $rows = requirementRowsFor($plan);

    expect($rows)->toHaveCount(1);
    expect($rows[0]['name'])->toBe('Liszt');
    expect($rows[0]['total_required'])->toBe(1900.0);
});

it('production plan stale ingredient requirements are removed after update', function (): void {
    $user = User::factory()->create();
    $oldProduct = Product::factory()->create(['is_active' => true]);
    $newProduct = Product::factory()->create(['is_active' => true]);
    $oldIngredient = Ingredient::factory()->create(['name' => 'Régi alapanyag', 'unit' => 'g']);
    $newIngredient = Ingredient::factory()->create(['name' => 'Új alapanyag', 'unit' => 'g']);

    ProductIngredient::factory()->create(['product_id' => $oldProduct->id, 'ingredient_id' => $oldIngredient->id, 'quantity' => 100]);
    ProductIngredient::factory()->create(['product_id' => $newProduct->id, 'ingredient_id' => $newIngredient->id, 'quantity' => 200]);

    $plan = ProductionPlan::factory()->create(['status' => 'draft']);
    $plan->items()->create([
        'product_id' => $oldProduct->id,
        'product_name_snapshot' => $oldProduct->name,
        'product_slug_snapshot' => $oldProduct->slug,
        'target_quantity' => 2,
        'unit_label' => 'db',
        'sort_order' => 0,
    ]);

    expect(requirementRowByName($plan, 'Régi alapanyag')['total_required'])->toBe(200.0);

    $this->actingAs($user)->put("/admin/production-plans/{$plan->id}", [
        'target_ready_at' => Carbon::tomorrow()->setTime(9, 0)->toDateTimeString(),
        'status' => 'draft',
        'is_locked' => false,
        'items' => [
            ['product_id' => $newProduct->id, 'target_quantity' => 3, 'unit_label' => 'db', 'sort_order' => 0],
        ],
    ])->assertRedirect('/admin/production-plans');

    expect(requirementRowByName($plan, 'Régi alapanyag'))->toBeNull();
    expect(requirementRowByName($plan, 'Új alapanyag')['total_required'])->toBe(600.0);
});

it('production plan timeline starter dependency lépéseket general', function (): void {
    $user = User::factory()->create();
    $category = Category::factory()->create(['is_active' => true]);

    $starter = Product::factory()->create([
        'category_id' => $category->id,
        'name' => 'Aktiv kovasz',
        'slug' => 'aktiv-kovasz',
    ]);

    RecipeStep::factory()->create([
        'product_id' => $starter->id,
        'title' => 'Anyakovasz etetese',
        'step_type' => 'preparation',
        'work_instruction' => 'Etess 1:2:2 aranyban.',
        'completion_criteria' => 'Duplazodas 4 oran belul.',
        'attention_points' => 'A homerseklet maradjon 24C korul.',
        'required_tools' => 'Digitalis merleg',
        'expected_result' => 'Aktiv, buborekos starter',
        'duration_minutes' => 15,
        'wait_minutes' => 240,
        'is_active' => true,
        'sort_order' => 0,
    ]);

    $bread = Product::factory()->create([
        'category_id' => $category->id,
        'name' => 'Egyszeru kovaszos feher kenyer',
        'slug' => 'egyszeru-kovaszos-feher-kenyer',
    ]);

    $starterIngredient = Ingredient::factory()->create([
        'name' => 'Aktiv kovasz',
        'slug' => 'aktiv-kovasz',
        'unit' => 'g',
    ]);

    ProductIngredient::factory()->create([
        'product_id' => $bread->id,
        'ingredient_id' => $starterIngredient->id,
        'quantity' => 150,
    ]);

    RecipeStep::factory()->create([
        'product_id' => $bread->id,
        'title' => 'Bulk ferment',
        'step_type' => 'proofing',
        'work_instruction' => 'Pihentesd letakarva.',
        'completion_criteria' => 'Kb. 50% terfogatnovekedes.',
        'attention_points' => 'Tulfermentalast keruld.',
        'required_tools' => 'Fedeles kelesztolada',
        'expected_result' => 'Rugalmas, levegos teszta',
        'duration_minutes' => 20,
        'wait_minutes' => 240,
        'is_active' => true,
        'sort_order' => 0,
    ]);

    $response = $this->actingAs($user)->post('/admin/production-plans', [
        'target_ready_at' => Carbon::tomorrow()->setTime(8, 0)->toDateTimeString(),
        'items' => [
            [
                'product_id' => $bread->id,
                'target_quantity' => 1,
                'unit_label' => 'db',
                'sort_order' => 0,
            ],
        ],
    ]);

    $response->assertRedirect('/admin/production-plans');

    $plan = ProductionPlan::query()->firstOrFail();

    $this->assertDatabaseHas('production_plan_steps', [
        'production_plan_id' => $plan->id,
        'product_id' => $bread->id,
        'title' => 'Egyszeru kovaszos feher kenyer - Bulk ferment',
        'is_dependency' => false,
        'work_instruction' => 'Pihentesd letakarva.',
    ]);

    $this->assertDatabaseHas('production_plan_steps', [
        'production_plan_id' => $plan->id,
        'product_id' => $starter->id,
        'depends_on_product_id' => $bread->id,
        'is_dependency' => true,
    ]);
});

it('production plan delete mukodik', function (): void {
    $user = User::factory()->create();
    $plan = ProductionPlan::factory()->create();

    $response = $this->actingAs($user)->delete("/admin/production-plans/{$plan->id}");

    $response->assertRedirect('/admin/production-plans');
    $this->assertDatabaseMissing('production_plans', ['id' => $plan->id]);
});
