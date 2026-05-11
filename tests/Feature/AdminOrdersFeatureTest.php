<?php

use App\Data\Orders\OrderDetailData;
use App\Data\Orders\OrderIndexData;
use App\Data\Orders\OrderItemData;
use App\Data\Orders\OrderListItemData;
use App\Data\Orders\OrderStatusUpdateData;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

it('admin can access order list', function (): void {
    Order::factory()->count(2)->create();

    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->get('/admin/orders');

    $response
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page
            ->component('Admin/Orders/Index')
            ->has('orders.data', 2)
            ->where('filters.search', '')
            ->where('filters.status', '')
        );
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

it('order index data normalizalja a frontend filter payloadot', function (): void {
    $data = OrderIndexData::from([
        'search' => '  HH-20260510  ',
        'status' => Order::STATUS_CONFIRMED,
        'sort_field' => 'total',
        'sort_direction' => 'asc',
        'per_page' => 50,
    ]);

    expect($data->search)->toBe('HH-20260510')
        ->and($data->toFrontendFilters())->toBe([
            'search' => 'HH-20260510',
            'status' => Order::STATUS_CONFIRMED,
            'sort_field' => 'total',
            'sort_direction' => 'asc',
            'per_page' => 50,
        ]);
});

it('order list item data stabil admin lista payloadot ad', function (): void {
    $order = Order::factory()->create([
        'order_number' => 'HH-TEST-0001',
        'customer_name' => 'Kovács Anna',
        'total' => 7500,
    ]);
    OrderItem::factory()->for($order)->count(2)->create();
    $order->loadCount('items');

    $data = OrderListItemData::fromModel($order)->toArray();

    expect($data)
        ->toMatchArray([
            'id' => $order->id,
            'order_number' => 'HH-TEST-0001',
            'customer_name' => 'Kovács Anna',
            'total' => 7500.0,
            'currency' => 'HUF',
            'items_count' => 2,
        ]);
});

it('order detail data megorzi az item snapshot mezoket', function (): void {
    $order = Order::factory()->create([
        'subtotal' => 1200,
        'total' => 1200,
    ]);
    OrderItem::factory()->for($order)->create([
        'product_id' => null,
        'product_name_snapshot' => 'Kovászos cipó snapshot',
        'unit_price' => 1200,
        'quantity' => 1,
        'line_total' => 1200,
    ]);

    $order->load(['items.product:id,name,slug']);
    $data = OrderDetailData::fromModel($order);

    expect($data->items[0])->toBeInstanceOf(OrderItemData::class)
        ->and($data->toArray()['items'][0])->toMatchArray([
            'product_id' => null,
            'product_name_snapshot' => 'Kovászos cipó snapshot',
            'unit_price' => 1200.0,
            'quantity' => 1,
            'line_total' => 1200.0,
        ]);
});

it('order status update data validalt payloadbol keszul', function (): void {
    $data = OrderStatusUpdateData::from([
        'status' => Order::STATUS_CONFIRMED,
        'internal_notes' => '',
        'pickup_date' => '2026-05-11',
        'pickup_time_slot' => '08:00-10:00',
    ]);

    expect($data->toPayload())->toBe([
        'status' => Order::STATUS_CONFIRMED,
        'internal_notes' => '',
        'pickup_date' => '2026-05-11',
        'pickup_time_slot' => '08:00-10:00',
    ]);
});
