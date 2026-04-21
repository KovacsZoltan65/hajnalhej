<?php

use App\Models\Product;

it('guest can add product to cart', function (): void {
    $product = Product::factory()->create([
        'is_active' => true,
        'stock_status' => Product::STOCK_IN_STOCK,
        'price' => 1200,
    ]);

    $response = $this->post('/cart/items', [
        'product_id' => $product->id,
        'quantity' => 2,
    ]);

    $response->assertRedirect();

    $this->assertEquals([
        ['product_id' => $product->id, 'quantity' => 2],
    ], session('cart.items'));
});

it('cart quantity update works', function (): void {
    $product = Product::factory()->create([
        'is_active' => true,
        'stock_status' => Product::STOCK_IN_STOCK,
    ]);

    $this->withSession([
        'cart.items' => [
            ['product_id' => $product->id, 'quantity' => 1],
        ],
    ]);

    $response = $this->patch("/cart/items/{$product->id}", [
        'quantity' => 4,
    ]);

    $response->assertRedirect();

    $this->assertEquals(4, session('cart.items.0.quantity'));
});

it('cart item removal works', function (): void {
    $product = Product::factory()->create([
        'is_active' => true,
        'stock_status' => Product::STOCK_IN_STOCK,
    ]);

    $this->withSession([
        'cart.items' => [
            ['product_id' => $product->id, 'quantity' => 2],
        ],
    ]);

    $response = $this->delete("/cart/items/{$product->id}");

    $response->assertRedirect();

    expect(session('cart.items'))->toBeArray()->toHaveCount(0);
});
