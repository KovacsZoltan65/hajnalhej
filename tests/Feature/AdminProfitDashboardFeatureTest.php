<?php

use App\Models\Ingredient;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function (): void {
    $this->seed(RolesAndPermissionsSeeder::class);
});

it('admin can access profit dashboard', function (): void {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get('/admin/profit-dashboard')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/ProfitDashboard/Index')
            ->where('filters.days', 30)
            ->has('dashboard.summary')
            ->has('dashboard.product_margins')
            ->has('dashboard.top_profit_products')
            ->has('dashboard.order_profit_trend.points'));
});

it('customer cannot access profit dashboard', function (): void {
    $customer = User::factory()->customer()->create();

    $this->actingAs($customer)
        ->get('/admin/profit-dashboard')
        ->assertForbidden();
});

it('profit dashboard computes bom cost margin and order profit trend', function (): void {
    $admin = User::factory()->admin()->create();
    $customer = User::factory()->customer()->create();

    $ingredientA = Ingredient::factory()->create([
        'name' => 'Liszt',
        'estimated_unit_cost' => 2.5,
        'unit' => 'g',
    ]);
    $ingredientB = Ingredient::factory()->create([
        'name' => 'Vaj',
        'estimated_unit_cost' => 5,
        'unit' => 'g',
    ]);

    $product = Product::factory()->create([
        'name' => 'Vajas kalács',
        'price' => 1200,
        'is_active' => true,
    ]);

    $product->productIngredients()->createMany([
        [
            'ingredient_id' => $ingredientA->id,
            'quantity' => 100, // 100 * 2.5 = 250
            'sort_order' => 1,
        ],
        [
            'ingredient_id' => $ingredientB->id,
            'quantity' => 50, // 50 * 5 = 250
            'sort_order' => 2,
        ],
    ]);

    $order = Order::factory()->create([
        'user_id' => $customer->id,
        'customer_email' => $customer->email,
        'status' => Order::STATUS_COMPLETED,
        'placed_at' => now()->subDay(),
        'subtotal' => 3600,
        'total' => 3600,
    ]);

    $order->items()->create([
        'product_id' => $product->id,
        'product_name_snapshot' => $product->name,
        'unit_price' => 1200,
        'quantity' => 3,
        'line_total' => 3600,
    ]);

    $this->actingAs($admin)
        ->get('/admin/profit-dashboard?days=30')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('dashboard.product_margins.0.product_name', 'Vajas kalács')
            ->where('dashboard.product_margins.0.estimated_unit_cost', 500)
            ->where('dashboard.product_margins.0.margin_amount', 700)
            ->where('dashboard.top_profit_products.0.product_name', 'Vajas kalács')
            ->where('dashboard.top_profit_products.0.estimated_profit', 2100)
            ->has('dashboard.order_profit_trend.points'));
});

