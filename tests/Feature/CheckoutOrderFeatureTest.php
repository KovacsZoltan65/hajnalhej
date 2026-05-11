<?php

use App\Models\Branch;
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
        'fulfillment_method' => 'pickup',
        'pickup_branch_id' => pickupBranch()->id,
        'billing_address' => checkoutAddress(),
        'same_as_billing' => true,
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

    $branch = pickupBranch();
    $payload = checkoutPayload([
        'customer_name' => 'Teszt Elek',
        'customer_email' => 'teszt@example.com',
        'customer_phone' => '+36123456789',
        'notes' => 'Kerek papirzacskot.',
        'pickup_date' => now()->addDay()->toDateString(),
        'pickup_time_slot' => '08:00-10:00',
        'pickup_branch_id' => $branch->id,
    ]);

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
    expect($order?->fulfillment_method)->toBe('pickup');
    expect($order?->pickup_branch_id)->toBe($branch->id);
    expect($order?->shipping_address_snapshot)->toBeNull();
    expect($order?->billing_address_snapshot['city'])->toBe('Budapest');
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
        'fulfillment_method' => 'pickup',
        'pickup_branch_id' => pickupBranch()->id,
        'billing_address' => checkoutAddress(),
        'same_as_billing' => true,
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
        'fulfillment_method' => 'pickup',
        'pickup_branch_id' => pickupBranch()->id,
        'billing_address' => checkoutAddress(),
        'same_as_billing' => true,
        'accept_privacy' => true,
        'accept_terms' => true,
    ]);

    $response->assertRedirect('/cart');
    $response->assertSessionHas('error');
    expect(Order::query()->count())->toBe(0);
});

it('creates pickup order with active pickup branch', function (): void {
    $product = orderableProduct();
    $branch = pickupBranch(['name' => 'Hajnalhej Belvaros']);

    $response = $this->withSession([
        'cart.items' => [
            ['product_id' => $product->id, 'quantity' => 1],
        ],
    ])->post('/checkout', checkoutPayload([
        'pickup_branch_id' => $branch->id,
    ]));

    $order = Order::query()->latest('id')->first();

    $response->assertRedirect("/orders/success/{$order?->id}");
    expect($order?->fulfillment_method)->toBe('pickup');
    expect($order?->pickup_branch_id)->toBe($branch->id);
    expect((float) $order?->delivery_fee)->toBe(0.0);
});

it('requires pickup branch for pickup orders', function (): void {
    $product = orderableProduct();

    $response = $this->withSession([
        'cart.items' => [
            ['product_id' => $product->id, 'quantity' => 1],
        ],
    ])->post('/checkout', checkoutPayload([
        'pickup_branch_id' => null,
    ]));

    $response->assertSessionHasErrors('pickup_branch_id');
    expect(Order::query()->count())->toBe(0);
});

it('rejects inactive pickup branch for pickup orders', function (): void {
    $product = orderableProduct();
    $branch = pickupBranch(['active' => false]);

    $response = $this->withSession([
        'cart.items' => [
            ['product_id' => $product->id, 'quantity' => 1],
        ],
    ])->post('/checkout', checkoutPayload([
        'pickup_branch_id' => $branch->id,
    ]));

    $response->assertSessionHasErrors('pickup_branch_id');
    expect(Order::query()->count())->toBe(0);
});

it('rejects branch type that is not valid for pickup', function (): void {
    $product = orderableProduct();
    $branch = Branch::factory()->create([
        'type' => 'warehouse',
        'active' => true,
    ]);

    $response = $this->withSession([
        'cart.items' => [
            ['product_id' => $product->id, 'quantity' => 1],
        ],
    ])->post('/checkout', checkoutPayload([
        'pickup_branch_id' => $branch->id,
    ]));

    $response->assertSessionHasErrors('pickup_branch_id');
    expect(Order::query()->count())->toBe(0);
});

it('creates delivery order with shipping address snapshot', function (): void {
    $product = orderableProduct();
    $shippingAddress = checkoutAddress([
        'name' => 'Szallitasi Nev',
        'city' => 'Szeged',
        'street' => 'Tisza utca',
    ]);

    $response = $this->withSession([
        'cart.items' => [
            ['product_id' => $product->id, 'quantity' => 1],
        ],
    ])->post('/checkout', checkoutPayload([
        'fulfillment_method' => 'delivery',
        'pickup_branch_id' => null,
        'same_as_billing' => false,
        'shipping_address' => $shippingAddress,
        'delivery_notes' => 'Kapucsengo 12.',
    ]));

    $order = Order::query()->latest('id')->first();

    $response->assertRedirect("/orders/success/{$order?->id}");
    expect($order?->fulfillment_method)->toBe('delivery');
    expect($order?->pickup_branch_id)->toBeNull();
    expect($order?->shipping_address_snapshot['city'])->toBe('Szeged');
    expect($order?->delivery_notes)->toBe('Kapucsengo 12.');
});

it('uses billing address as shipping snapshot when delivery address is same as billing', function (): void {
    $product = orderableProduct();
    $billingAddress = checkoutAddress(['city' => 'Pecs']);

    $this->withSession([
        'cart.items' => [
            ['product_id' => $product->id, 'quantity' => 1],
        ],
    ])->post('/checkout', checkoutPayload([
        'fulfillment_method' => 'delivery',
        'pickup_branch_id' => null,
        'billing_address' => $billingAddress,
        'same_as_billing' => true,
        'shipping_address' => null,
    ]));

    $order = Order::query()->latest('id')->first();

    expect($order?->billing_address_snapshot['city'])->toBe('Pecs');
    expect($order?->shipping_address_snapshot)->toBe($order?->billing_address_snapshot);
});

it('keeps address snapshots independent from later order field changes', function (): void {
    $product = orderableProduct();

    $this->withSession([
        'cart.items' => [
            ['product_id' => $product->id, 'quantity' => 1],
        ],
    ])->post('/checkout', checkoutPayload([
        'fulfillment_method' => 'delivery',
        'pickup_branch_id' => null,
        'same_as_billing' => false,
        'shipping_address' => checkoutAddress(['name' => 'Eredeti Cimzett']),
    ]));

    $order = Order::query()->latest('id')->firstOrFail();
    $order->update(['customer_name' => 'Modositott Nev']);

    expect($order->refresh()->shipping_address_snapshot['name'])->toBe('Eredeti Cimzett');
});

it('rejects unsupported fulfillment method', function (): void {
    $product = orderableProduct();

    $response = $this->withSession([
        'cart.items' => [
            ['product_id' => $product->id, 'quantity' => 1],
        ],
    ])->post('/checkout', checkoutPayload([
        'fulfillment_method' => 'drone',
    ]));

    $response->assertSessionHasErrors('fulfillment_method');
    expect(Order::query()->count())->toBe(0);
});

function orderableProduct(): Product
{
    $product = Product::factory()->create([
        'is_active' => true,
        'stock_status' => Product::STOCK_IN_STOCK,
        'price' => 1250,
    ]);

    publishProductForOrdering($product);

    return $product;
}

/**
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function checkoutAddress(array $overrides = []): array
{
    return array_merge([
        'name' => 'Teszt Elek',
        'country' => 'Magyarorszag',
        'postal_code' => '1111',
        'city' => 'Budapest',
        'street' => 'Kovaszos utca',
        'house_number' => '12',
        'floor' => null,
        'door' => null,
        'company_name' => null,
        'tax_number' => null,
        'phone' => '+36123456789',
        'notes' => null,
    ], $overrides);
}

/**
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function checkoutPayload(array $overrides = []): array
{
    $branch = pickupBranch();

    return array_merge([
        'customer_name' => 'Teszt Elek',
        'customer_email' => 'teszt@example.com',
        'customer_phone' => '+36123456789',
        'notes' => null,
        'pickup_date' => now()->addDay()->toDateString(),
        'pickup_time_slot' => '08:00-10:00',
        'fulfillment_method' => 'pickup',
        'pickup_branch_id' => $branch->id,
        'billing_address' => checkoutAddress(),
        'shipping_address' => checkoutAddress(['name' => 'Szallitasi Nev']),
        'same_as_billing' => true,
        'delivery_notes' => null,
        'accept_privacy' => true,
        'accept_terms' => true,
    ], $overrides);
}

/**
 * @param  array<string, mixed>  $overrides
 */
function pickupBranch(array $overrides = []): Branch
{
    return Branch::factory()->create(array_merge([
        'type' => 'shop',
        'active' => true,
    ], $overrides));
}
