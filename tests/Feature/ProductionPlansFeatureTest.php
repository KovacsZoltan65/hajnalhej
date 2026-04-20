<?php

use App\Models\Category;
use App\Models\Ingredient;
use App\Models\Product;
use App\Models\ProductIngredient;
use App\Models\ProductionPlan;
use App\Models\RecipeStep;
use App\Models\User;
use Illuminate\Support\Carbon;
use Inertia\Testing\AssertableInertia as Assert;

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

it('production plan timeline starter dependency lepeseket general', function (): void {
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
