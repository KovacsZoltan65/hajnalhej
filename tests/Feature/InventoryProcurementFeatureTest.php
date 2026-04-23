<?php

use App\Models\Ingredient;
use App\Models\Order;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;

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

    $stockCountId = (int) \DB::table('stock_counts')->value('id');

    $this->actingAs($admin)->post("/admin/stock-counts/{$stockCountId}/close")
        ->assertRedirect();

    $this->assertDatabaseHas('inventory_movements', [
        'reference_type' => 'stock_count',
        'reference_id' => $stockCountId,
        'movement_type' => 'count_correction',
        'direction' => 'out',
    ]);
});

it('customer cannot access inventory and procurement admin routes', function (): void {
    $customer = User::factory()->customer()->create();

    $this->actingAs($customer)->get('/admin/suppliers')->assertForbidden();
    $this->actingAs($customer)->get('/admin/purchases')->assertForbidden();
    $this->actingAs($customer)->get('/admin/inventory')->assertForbidden();
});

