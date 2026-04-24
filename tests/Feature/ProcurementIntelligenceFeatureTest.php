<?php

use App\Models\Ingredient;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\User;
use App\Services\ProcurementIntelligenceService;
use Database\Seeders\RolesAndPermissionsSeeder;

beforeEach(function (): void {
    $this->seed(RolesAndPermissionsSeeder::class);
});

function postedPurchase(Supplier $supplier, Ingredient $ingredient, float $quantity, float $unitCost, string $date): Purchase
{
    $purchase = Purchase::query()->create([
        'supplier_id' => $supplier->id,
        'purchase_date' => $date,
        'status' => Purchase::STATUS_POSTED,
        'subtotal' => $quantity * $unitCost,
        'total' => $quantity * $unitCost,
    ]);

    $purchase->items()->create([
        'ingredient_id' => $ingredient->id,
        'quantity' => $quantity,
        'unit' => $ingredient->unit,
        'unit_cost' => $unitCost,
        'line_total' => $quantity * $unitCost,
    ]);

    return $purchase;
}

it('admin can access procurement intelligence page', function (): void {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get('/admin/procurement-intelligence')
        ->assertOk();
});

it('customer cannot access procurement intelligence page', function (): void {
    $customer = User::factory()->customer()->create();

    $this->actingAs($customer)
        ->get('/admin/procurement-intelligence')
        ->assertForbidden();
});

it('calculates supplier price trend from posted purchase items', function (): void {
    $supplier = Supplier::factory()->create(['name' => 'Malom Kft.']);
    $ingredient = Ingredient::factory()->create(['name' => 'BL80 liszt', 'unit' => 'kg']);

    postedPurchase($supplier, $ingredient, 10, 300, now()->subDays(5)->toDateString());
    postedPurchase($supplier, $ingredient, 10, 360, now()->toDateString());

    $dashboard = app(ProcurementIntelligenceService::class)->buildDashboard(['days' => 30]);
    $trend = collect($dashboard['supplier_price_trends'])->firstWhere('ingredient_id', $ingredient->id);

    expect($trend['last_unit_cost'])->toBe(360.0)
        ->and($trend['previous_unit_cost'])->toBe(300.0)
        ->and($trend['change_amount'])->toBe(60.0)
        ->and($trend['change_percent'])->toBe(20.0)
        ->and($trend['trend'])->toBe('emelkedik');
});

it('calculates minimum stock recommendation from stock and production consumption', function (): void {
    $ingredient = Ingredient::factory()->create([
        'name' => 'Vaj',
        'unit' => 'kg',
        'current_stock' => 2,
        'minimum_stock' => 3,
        'estimated_unit_cost' => 1200,
        'is_active' => true,
    ]);

    InventoryMovement::query()->create([
        'ingredient_id' => $ingredient->id,
        'movement_type' => InventoryMovement::TYPE_PRODUCTION_OUT,
        'direction' => InventoryMovement::DIRECTION_OUT,
        'quantity' => 28,
        'occurred_at' => now()->subDays(3),
    ]);

    $dashboard = app(ProcurementIntelligenceService::class)->buildDashboard(['days' => 30]);
    $recommendation = collect($dashboard['minimum_stock_recommendations'])->firstWhere('ingredient_id', $ingredient->id);

    expect($recommendation['weekly_average_consumption'])->toBe(7.0)
        ->and($recommendation['days_on_hand'])->toBe(2.0)
        ->and($recommendation['suggested_order_quantity'])->toBe(12.0)
        ->and($recommendation['urgency'])->toBe('critical');
});

it('calculates weekly consumption forecast from four week average', function (): void {
    $ingredient = Ingredient::factory()->create([
        'name' => 'Tej',
        'unit' => 'l',
        'current_stock' => 14,
        'minimum_stock' => 4,
        'estimated_unit_cost' => 420,
        'is_active' => true,
    ]);

    InventoryMovement::query()->create([
        'ingredient_id' => $ingredient->id,
        'movement_type' => InventoryMovement::TYPE_PRODUCTION_OUT,
        'direction' => InventoryMovement::DIRECTION_OUT,
        'quantity' => 28,
        'occurred_at' => now()->subDays(10),
    ]);

    InventoryMovement::query()->create([
        'ingredient_id' => $ingredient->id,
        'movement_type' => InventoryMovement::TYPE_PRODUCTION_OUT,
        'direction' => InventoryMovement::DIRECTION_OUT,
        'quantity' => 7,
        'occurred_at' => now()->subDays(2),
    ]);

    $dashboard = app(ProcurementIntelligenceService::class)->buildDashboard(['days' => 30]);
    $forecast = collect($dashboard['weekly_consumption_forecast'])->firstWhere('ingredient_id', $ingredient->id);

    expect($forecast['last_week_consumption'])->toBe(7.0)
        ->and($forecast['four_week_average'])->toBe(8.75)
        ->and($forecast['next_week_forecast'])->toBe(8.75)
        ->and($forecast['coverage_days'])->toBe(11.2);
});

it('creates low stock and price increase alerts', function (): void {
    $supplier = Supplier::factory()->create();
    $ingredient = Ingredient::factory()->create([
        'name' => 'Rozsliszt',
        'unit' => 'kg',
        'current_stock' => 1,
        'minimum_stock' => 5,
        'estimated_unit_cost' => 260,
        'is_active' => true,
    ]);

    postedPurchase($supplier, $ingredient, 10, 250, now()->subDays(8)->toDateString());
    postedPurchase($supplier, $ingredient, 10, 300, now()->toDateString());

    $dashboard = app(ProcurementIntelligenceService::class)->buildDashboard(['days' => 30]);
    $types = collect($dashboard['alerts'])->pluck('type');

    expect($types)->toContain('low_stock')
        ->and($types)->toContain('price_increase');
});

it('creates missing cost and minimum stock data alerts', function (): void {
    $ingredient = Ingredient::factory()->create([
        'name' => 'Mák',
        'unit' => 'kg',
        'current_stock' => 3,
        'minimum_stock' => 0,
        'estimated_unit_cost' => 0,
        'is_active' => true,
    ]);

    $dashboard = app(ProcurementIntelligenceService::class)->buildDashboard(['days' => 30]);
    $alerts = collect($dashboard['alerts'])->where('ingredient_id', $ingredient->id)->pluck('type');

    expect($alerts)->toContain('missing_estimated_cost')
        ->and($alerts)->toContain('missing_minimum_stock');
});

it('creates bom no stock alert for ingredients used by recipes', function (): void {
    $ingredient = Ingredient::factory()->create([
        'name' => 'Kovászmag',
        'unit' => 'kg',
        'current_stock' => 0,
        'minimum_stock' => 1,
        'estimated_unit_cost' => 800,
        'is_active' => true,
    ]);
    $product = Product::factory()->create();
    $product->productIngredients()->create([
        'ingredient_id' => $ingredient->id,
        'quantity' => 0.2,
        'sort_order' => 1,
    ]);

    $dashboard = app(ProcurementIntelligenceService::class)->buildDashboard(['days' => 30]);
    $alerts = collect($dashboard['alerts'])->where('ingredient_id', $ingredient->id)->pluck('type');

    expect($alerts)->toContain('bom_no_stock');
});
