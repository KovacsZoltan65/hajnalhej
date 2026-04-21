<?php

use App\Models\Order;
use App\Models\User;

it('admin can access order list', function (): void {
    Order::factory()->count(2)->create();

    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->get('/admin/orders');

    $response->assertOk();
});

it('customer cannot access admin orders', function (): void {
    $customer = User::factory()->customer()->create();

    $response = $this->actingAs($customer)->get('/admin/orders');

    $response->assertForbidden();
});

it('admin can move order status along lifecycle', function (): void {
    $order = Order::factory()->create(['status' => Order::STATUS_PENDING]);
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->patch("/admin/orders/{$order->id}/status", [
        'status' => Order::STATUS_CONFIRMED,
    ])->assertRedirect();

    expect($order->refresh()->status)->toBe(Order::STATUS_CONFIRMED);

    $this->actingAs($admin)->patch("/admin/orders/{$order->id}/status", [
        'status' => Order::STATUS_IN_PREPARATION,
    ])->assertRedirect();

    expect($order->refresh()->status)->toBe(Order::STATUS_IN_PREPARATION);
});
