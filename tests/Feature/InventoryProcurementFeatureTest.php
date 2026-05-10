<?php

use App\Data\Inventory\InventoryAdjustmentData;
use App\Data\Inventory\InventoryLedgerIndexData;
use App\Data\Inventory\InventoryMovementListItemData;
use App\Data\StockCounts\StockCountIndexData;
use App\Data\StockCounts\StockCountItemData;
use App\Data\StockCounts\StockCountListItemData;
use App\Data\StockCounts\StockCountStoreData;
use App\Models\Ingredient;
use App\Models\InventoryMovement;
use App\Models\Order;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\StockCount;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function (): void {
    $this->seed(RolesAndPermissionsSeeder::class);
});

it('purchase posting creates inventory movements and updates stock summary fields', function (): void {
    $admin = User::factory()->admin()->create();
    $ingredient = Ingredient::factory()->create([
        'unit' => 'kg',
        'current_stock' => 0,
        'average_unit_cost' => null,
        'stock_value' => null,
    ]);

    $this->actingAs($admin)->post('/admin/purchases', [
        'purchase_date' => now()->toDateString(),
        'reference_number' => 'PO-2026-001',
        'items' => [
            [
                'ingredient_id' => $ingredient->id,
                'quantity' => 10,
                'unit' => 'kg',
                'unit_cost' => 320,
            ],
        ],
    ])->assertRedirect();

    $purchase = Purchase::query()->firstOrFail();

    $this->actingAs($admin)
        ->post("/admin/purchases/{$purchase->id}/post")
        ->assertRedirect();

    $this->assertDatabaseHas('inventory_movements', [
        'reference_type' => 'purchase',
        'reference_id' => $purchase->id,
        'movement_type' => 'purchase_in',
        'direction' => 'in',
    ]);

    $ingredient->refresh();
    expect((float) $ingredient->current_stock)->toBe(10.0)
        ->and((float) $ingredient->average_unit_cost)->toBe(320.0)
        ->and((float) $ingredient->stock_value)->toBe(3200.0);
});

it('inventory index exposes movement types and waste reasons separately', function (): void {
    $admin = User::factory()->admin()->create();

    $ingredient = Ingredient::factory()->create([
        'name' => 'Liszt',
        'is_active' => true,
    ]);
    $product = Product::factory()->create([
        'name' => 'Kifli',
        'is_active' => true,
    ]);
    $product->productIngredients()->create([
        'ingredient_id' => $ingredient->id,
        'quantity' => 1,
        'sort_order' => 1,
    ]);

    $this->actingAs($admin)
        ->get('/admin/inventory')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Inventory/Index')
            ->has('movement_types')
            ->where('movement_types.0', 'purchase_in')
            ->has('ingredient_options', 1)
            ->has('product_options', 1)
            ->has('waste_reasons', 5)
        );
});

it('inventory ledger index data stabil frontend filter payloadot ad', function (): void {
    $data = InventoryLedgerIndexData::from([
        'days' => 30,
        'date_from' => '2026-05-01',
        'date_to' => '2026-05-10',
        'ingredient_id' => '12',
        'movement_type' => InventoryMovement::TYPE_WASTE_OUT,
        'search' => '  selejt  ',
        'per_page' => 50,
    ]);

    expect($data->search)->toBe('selejt')
        ->and($data->ingredient_id)->toBe(12)
        ->and($data->toFrontendFilters())->toBe([
            'days' => 30,
            'date_from' => '2026-05-01',
            'date_to' => '2026-05-10',
            'ingredient_id' => 12,
            'movement_type' => InventoryMovement::TYPE_WASTE_OUT,
            'search' => 'selejt',
            'per_page' => 50,
        ]);
});

it('inventory movement list item data explicit decimal payloadot ad', function (): void {
    $admin = User::factory()->admin()->create();
    $ingredient = Ingredient::factory()->create(['name' => 'Liszt', 'unit' => 'kg']);
    $movement = InventoryMovement::query()->create([
        'ingredient_id' => $ingredient->id,
        'movement_type' => InventoryMovement::TYPE_ADJUSTMENT_IN,
        'direction' => InventoryMovement::DIRECTION_IN,
        'quantity' => '2.500',
        'unit_cost' => '120.2500',
        'total_cost' => '300.63',
        'occurred_at' => now(),
        'reference_type' => 'adjustment',
        'reference_id' => null,
        'notes' => 'Korrekció',
        'created_by' => $admin->id,
    ]);

    $movement->load(['ingredient:id,name,unit', 'creator:id,name,email']);
    $data = InventoryMovementListItemData::fromModel($movement)->toArray();

    expect($data)->toMatchArray([
        'ingredient_name' => 'Liszt',
        'ingredient_unit' => 'kg',
        'quantity' => 2.5,
        'unit_cost' => 120.25,
        'total_cost' => 300.63,
        'created_by' => $admin->name,
    ]);
});

it('inventory adjustment data explicit quantity contractot ad', function (): void {
    $data = InventoryAdjustmentData::from([
        'ingredient_id' => 5,
        'difference' => '-1.23456',
        'unit_cost' => '99.12345',
        'occurred_at' => '2026-05-10',
        'notes' => '',
    ]);

    expect($data->toPayload())->toBe([
        'ingredient_id' => 5,
        'difference' => -1.235,
        'unit_cost' => 99.1235,
        'occurred_at' => '2026-05-10',
        'notes' => '',
    ]);
});

it('order completion books bom consumption as production_out and updates material cost', function (): void {
    $admin = User::factory()->admin()->create();
    $customer = User::factory()->customer()->create();

    $ingredient = Ingredient::factory()->create([
        'name' => 'Liszt',
        'unit' => 'kg',
        'current_stock' => 5,
        'average_unit_cost' => 100,
        'stock_value' => 500,
        'estimated_unit_cost' => 100,
    ]);

    $product = Product::factory()->create(['name' => 'Briós', 'price' => 1200]);
    $product->productIngredients()->create([
        'ingredient_id' => $ingredient->id,
        'quantity' => 0.25,
        'sort_order' => 1,
    ]);

    $order = Order::factory()->create([
        'user_id' => $customer->id,
        'status' => Order::STATUS_READY_FOR_PICKUP,
        'placed_at' => now(),
        'subtotal' => 2400,
        'total' => 2400,
    ]);
    $order->items()->create([
        'product_id' => $product->id,
        'product_name_snapshot' => $product->name,
        'unit_price' => 1200,
        'quantity' => 2,
        'line_total' => 2400,
    ]);

    $this->actingAs($admin)
        ->patch("/admin/orders/{$order->id}/status", [
            'status' => Order::STATUS_COMPLETED,
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('inventory_movements', [
        'reference_type' => 'order',
        'reference_id' => $order->id,
        'movement_type' => 'production_out',
        'direction' => 'out',
    ]);

    $ingredient->refresh();
    $order->refresh();

    expect((float) $ingredient->current_stock)->toBe(4.5)
        ->and((float) $order->material_cost_total)->toBe(50.0);
});

it('waste entry deducts stock and creates waste movement', function (): void {
    $admin = User::factory()->admin()->create();
    $ingredient = Ingredient::factory()->create([
        'unit' => 'kg',
        'current_stock' => 12,
        'average_unit_cost' => 400,
        'stock_value' => 4800,
    ]);

    $this->actingAs($admin)->post('/admin/inventory/waste', [
        'waste_type' => 'ingredient',
        'ingredient_id' => $ingredient->id,
        'quantity' => 2,
        'reason' => 'sérült',
        'occurred_at' => now()->toDateString(),
    ])->assertRedirect();

    $this->assertDatabaseHas('inventory_movements', [
        'ingredient_id' => $ingredient->id,
        'movement_type' => 'waste_out',
        'direction' => 'out',
    ]);

    $ingredient->refresh();
    expect((float) $ingredient->current_stock)->toBe(10.0);
});

it('product waste deducts ingredient stock by bom quantities', function (): void {
    $admin = User::factory()->admin()->create();

    $flour = Ingredient::factory()->create([
        'name' => 'Liszt',
        'unit' => 'kg',
        'current_stock' => 10,
        'average_unit_cost' => 400,
        'stock_value' => 4000,
    ]);
    $butter = Ingredient::factory()->create([
        'name' => 'Vaj',
        'unit' => 'kg',
        'current_stock' => 5,
        'average_unit_cost' => 1200,
        'stock_value' => 6000,
    ]);

    $product = Product::factory()->create([
        'name' => 'Croissant',
        'is_active' => true,
    ]);
    $product->productIngredients()->createMany([
        [
            'ingredient_id' => $flour->id,
            'quantity' => 0.20,
            'sort_order' => 1,
        ],
        [
            'ingredient_id' => $butter->id,
            'quantity' => 0.05,
            'sort_order' => 2,
        ],
    ]);

    $this->actingAs($admin)->post('/admin/inventory/waste', [
        'waste_type' => 'product',
        'product_id' => $product->id,
        'quantity' => 3,
        'reason' => 'gyártási hiba',
        'occurred_at' => now()->toDateString(),
    ])->assertRedirect();

    $this->assertDatabaseHas('inventory_movements', [
        'ingredient_id' => $flour->id,
        'movement_type' => 'waste_out',
        'direction' => 'out',
        'reference_type' => 'product_waste',
        'reference_id' => $product->id,
    ]);
    $this->assertDatabaseHas('inventory_movements', [
        'ingredient_id' => $butter->id,
        'movement_type' => 'waste_out',
        'direction' => 'out',
        'reference_type' => 'product_waste',
        'reference_id' => $product->id,
    ]);

    $flour->refresh();
    $butter->refresh();

    expect((float) $flour->current_stock)->toBe(9.4)
        ->and((float) $butter->current_stock)->toBe(4.85);
});

it('stock count close creates correction movement', function (): void {
    $admin = User::factory()->admin()->create();
    $ingredient = Ingredient::factory()->create([
        'current_stock' => 10,
        'average_unit_cost' => 300,
        'stock_value' => 3000,
    ]);

    $this->actingAs($admin)->post('/admin/stock-counts', [
        'count_date' => now()->toDateString(),
        'items' => [
            [
                'ingredient_id' => $ingredient->id,
                'expected_quantity' => 10,
                'counted_quantity' => 8,
            ],
        ],
    ])->assertRedirect();

    $stockCountId = (int) DB::table('stock_counts')->value('id');

    $this->actingAs($admin)->post("/admin/stock-counts/{$stockCountId}/close")
        ->assertRedirect();

    $this->assertDatabaseHas('inventory_movements', [
        'reference_type' => 'stock_count',
        'reference_id' => $stockCountId,
        'movement_type' => 'count_correction',
        'direction' => 'out',
    ]);
});

it('stock count index data stabil frontend filter payloadot ad', function (): void {
    $data = StockCountIndexData::from([
        'status' => StockCount::STATUS_DRAFT,
        'date_from' => '2026-05-01',
        'date_to' => '2026-05-10',
        'per_page' => 25,
    ]);

    expect($data->toFrontendFilters())->toBe([
        'status' => StockCount::STATUS_DRAFT,
        'date_from' => '2026-05-01',
        'date_to' => '2026-05-10',
        'per_page' => 25,
    ]);
});

it('stock count store data nested item contractot ad', function (): void {
    $data = StockCountStoreData::from([
        'count_date' => '2026-05-10',
        'notes' => 'Havi leltár',
        'items' => [
            [
                'ingredient_id' => 7,
                'expected_quantity' => '10.1234',
                'counted_quantity' => '9.9999',
            ],
        ],
    ]);

    expect($data->items[0])->toBeInstanceOf(StockCountItemData::class)
        ->and($data->toPayload()['items'][0])->toBe([
            'ingredient_id' => 7,
            'expected_quantity' => 10.123,
            'counted_quantity' => 10.0,
        ]);
});

it('stock count list item data stabil admin lista payloadot ad', function (): void {
    $admin = User::factory()->admin()->create();
    $stockCount = StockCount::query()->create([
        'count_date' => '2026-05-10',
        'status' => StockCount::STATUS_DRAFT,
        'notes' => 'Teszt',
        'created_by' => $admin->id,
        'closed_at' => null,
    ]);
    $stockCount->items()->create([
        'ingredient_id' => Ingredient::factory()->create()->id,
        'expected_quantity' => '10.000',
        'counted_quantity' => '8.000',
        'difference' => '-2.000',
    ]);
    $stockCount->load(['creator:id,name,email'])->loadCount('items');

    $data = StockCountListItemData::fromModel($stockCount)->toArray();

    expect($data)->toMatchArray([
        'id' => $stockCount->id,
        'count_date' => '2026-05-10',
        'status' => StockCount::STATUS_DRAFT,
        'notes' => 'Teszt',
        'items_count' => 1,
        'created_by' => $admin->name,
    ]);
});

it('customer cannot access inventory and procurement admin routes', function (): void {
    $customer = User::factory()->customer()->create();

    $this->actingAs($customer)->get('/admin/suppliers')->assertForbidden();
    $this->actingAs($customer)->get('/admin/purchases')->assertForbidden();
    $this->actingAs($customer)->get('/admin/inventory')->assertForbidden();
});
