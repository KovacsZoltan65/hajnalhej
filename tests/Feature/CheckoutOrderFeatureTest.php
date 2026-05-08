<?php

use App\Models\Order;
use App\Models\Product;
use App\Models\WeeklyMenu;
use App\Models\WeeklyMenuItem;
use Illuminate\Support\Carbon;

it('checkout page loads with non-empty cart', function (): void {
    $product = Product::factory()->create([
        'is_active' => true,
        'stock_status' => Product::STOCK_IN_STOCK,
        'price' => 1250,
    ]);
    publishProductForOrdering($product);

    $response = $this->withSession([
        'cart.items' => [
            ['product_id' => $product->id, 'quantity' => 2],
        ],
    ])->get('/checkout');

    $response->assertOk();
});

it('cannot place order from empty cart', function (): void {
    $response = $this->post('/checkout', [
        'customer_name' => 'Teszt Elek',
        'customer_email' => 'teszt@example.com',
        'customer_phone' => '+36123456789',
        'accept_privacy' => true,
        'accept_terms' => true,
    ]);

    $response->assertRedirect('/cart');
    $response->assertSessionHas('error');
});

it('valid checkout creates order and items with server-calculated totals', function (): void {
    $productA = Product::factory()->create([
        'name' => 'Kovaszos vekni',
        'is_active' => true,
        'stock_status' => Product::STOCK_IN_STOCK,
        'price' => 1000,
    ]);

    $productB = Product::factory()->create([
        'name' => 'Fahejas tekercs',
        'is_active' => true,
        'stock_status' => Product::STOCK_IN_STOCK,
        'price' => 1500,
    ]);
    publishProductForOrdering($productA);
    publishProductForOrdering($productB);

    $payload = [
        'customer_name' => 'Teszt Elek',
        'customer_email' => 'teszt@example.com',
        'customer_phone' => '+36123456789',
        'notes' => 'Kerek papirzacskot.',
        'pickup_date' => now()->addDay()->toDateString(),
        'pickup_time_slot' => '08:00-10:00',
        'accept_privacy' => true,
        'accept_terms' => true,
    ];

    $response = $this->withSession([
        'cart.items' => [
            ['product_id' => $productA->id, 'quantity' => 2],
            ['product_id' => $productB->id, 'quantity' => 1],
        ],
    ])->post('/checkout', $payload);

    $order = Order::query()->with('items')->latest('id')->first();

    expect($order)->not->toBeNull();
    expect($order?->order_number)->toMatch('/^HH-\d{8}-\d{4}$/');
    expect((float) $order?->total)->toBe(3500.0);
    expect((float) $order?->subtotal)->toBe(3500.0);
    expect($order?->status)->toBe(Order::STATUS_PENDING);
    expect($order?->items)->toHaveCount(2);
    expect($order?->items->first()->product_name_snapshot)->not->toBe('');

    $response->assertRedirect("/orders/success/{$order?->id}");
    expect(session('cart.items'))->toBeNull();
});

it('cannot place order for product outside published menu', function (): void {
    $product = Product::factory()->create([
        'is_active' => true,
        'stock_status' => Product::STOCK_IN_STOCK,
        'price' => 1250,
    ]);

    $response = $this->withSession([
        'cart.items' => [
            ['product_id' => $product->id, 'quantity' => 1],
        ],
    ])->post('/checkout', [
        'customer_name' => 'Teszt Elek',
        'customer_email' => 'teszt@example.com',
        'customer_phone' => '+36123456789',
        'accept_privacy' => true,
        'accept_terms' => true,
    ]);

    $response->assertRedirect('/cart');
    $response->assertSessionHas('error');
    expect(Order::query()->count())->toBe(0);
});

it('cannot place order for inactive weekly menu item', function (): void {
    $product = Product::factory()->create([
        'is_active' => true,
        'stock_status' => Product::STOCK_IN_STOCK,
        'price' => 1250,
    ]);

    $menu = WeeklyMenu::factory()->create([
        'status' => WeeklyMenu::STATUS_PUBLISHED,
        'week_start' => Carbon::today()->startOfWeek(),
        'week_end' => Carbon::today()->endOfWeek(),
        'published_at' => Carbon::now(),
    ]);

    WeeklyMenuItem::factory()->create([
        'weekly_menu_id' => $menu->id,
        'product_id' => $product->id,
        'category_id' => $product->category_id,
        'is_active' => false,
    ]);

    $response = $this->withSession([
        'cart.items' => [
            ['product_id' => $product->id, 'quantity' => 1],
        ],
    ])->post('/checkout', [
        'customer_name' => 'Teszt Elek',
        'customer_email' => 'teszt@example.com',
        'customer_phone' => '+36123456789',
        'accept_privacy' => true,
        'accept_terms' => true,
    ]);

    $response->assertRedirect('/cart');
    $response->assertSessionHas('error');
    expect(Order::query()->count())->toBe(0);
});
