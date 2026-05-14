<?php

use App\Enums\Delivery\DeliveryStatus;
use App\Enums\Orders\FulfillmentMethod;
use App\Models\Courier;
use App\Models\Order;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;

beforeEach(function (): void {
    $this->seed(RolesAndPermissionsSeeder::class);
});

function deliveryOrder(array $attributes = []): Order
{
    return Order::factory()->create(array_merge([
        'fulfillment_method' => FulfillmentMethod::DELIVERY->value,
        'delivery_status' => DeliveryStatus::PENDING->value,
        'shipping_address_snapshot' => [
            'name' => 'Teszt Elek',
            'country' => 'Magyarország',
            'postal_code' => '1111',
            'city' => 'Budapest',
            'street' => 'Kovász utca',
            'house_number' => '1',
        ],
    ], $attributes));
}

it('futar rendelheto delivery orderhez', function (): void {
    $admin = User::factory()->admin()->create();
    $order = deliveryOrder();
    $courier = Courier::factory()->create(['active' => true]);

    $this->actingAs($admin)
        ->post("/admin/orders/{$order->id}/delivery/assign", [
            'courier_id' => $courier->id,
            'delivery_scheduled_at' => '2026-05-12 09:00:00',
        ])
        ->assertRedirect();

    expect($order->refresh())
        ->courier_id->toBe($courier->id)
        ->delivery_status->toBe(DeliveryStatus::ASSIGNED->value);
});

it('pickup orderhez nem rendelheto futar', function (): void {
    $admin = User::factory()->admin()->create();
    $order = Order::factory()->create(['fulfillment_method' => FulfillmentMethod::PICKUP->value]);
    $courier = Courier::factory()->create(['active' => true]);

    $this->actingAs($admin)
        ->post("/admin/orders/{$order->id}/delivery/assign", ['courier_id' => $courier->id])
        ->assertSessionHasErrors('courier_id');

    expect($order->refresh()->courier_id)->toBeNull();
});

it('inactive courier nem rendelheto', function (): void {
    $admin = User::factory()->admin()->create();
    $order = deliveryOrder();
    $courier = Courier::factory()->create(['active' => false]);

    $this->actingAs($admin)
        ->post("/admin/orders/{$order->id}/delivery/assign", ['courier_id' => $courier->id])
        ->assertSessionHasErrors('courier_id');

    expect($order->refresh()->courier_id)->toBeNull()
        ->and($order->delivery_status)->toBe(DeliveryStatus::PENDING->value);
});

it('assigned allapotbol kiszallitas indithato', function (): void {
    $admin = User::factory()->admin()->create();
    $order = deliveryOrder(['delivery_status' => DeliveryStatus::ASSIGNED->value]);

    $this->actingAs($admin)
        ->post("/admin/orders/{$order->id}/delivery/start")
        ->assertRedirect();

    expect($order->refresh()->delivery_status)->toBe(DeliveryStatus::OUT_FOR_DELIVERY->value)
        ->and($order->out_for_delivery_at)->not->toBeNull();
});

it('out for delivery allapotbol delivered mukodik', function (): void {
    $admin = User::factory()->admin()->create();
    $order = deliveryOrder(['delivery_status' => DeliveryStatus::OUT_FOR_DELIVERY->value]);

    $this->actingAs($admin)
        ->post("/admin/orders/{$order->id}/delivery/delivered")
        ->assertRedirect();

    expect($order->refresh()->delivery_status)->toBe(DeliveryStatus::DELIVERED->value)
        ->and($order->delivered_at)->not->toBeNull();
});

it('assigned allapotbol failed mukodik reasonnel', function (): void {
    $admin = User::factory()->admin()->create();
    $order = deliveryOrder(['delivery_status' => DeliveryStatus::ASSIGNED->value]);

    $this->actingAs($admin)
        ->post("/admin/orders/{$order->id}/delivery/failed", [
            'failed_delivery_reason' => 'Nem volt otthon a vevő.',
        ])
        ->assertRedirect();

    expect($order->refresh()->delivery_status)->toBe(DeliveryStatus::FAILED->value)
        ->and($order->failed_delivery_reason)->toBe('Nem volt otthon a vevő.')
        ->and($order->delivered_at)->toBeNull();
});

it('out for delivery allapotbol failed mukodik reasonnel', function (): void {
    $admin = User::factory()->admin()->create();
    $order = deliveryOrder(['delivery_status' => DeliveryStatus::OUT_FOR_DELIVERY->value]);

    $this->actingAs($admin)
        ->post("/admin/orders/{$order->id}/delivery/failed", [
            'failed_delivery_reason' => 'Hibás cím.',
        ])
        ->assertRedirect();

    expect($order->refresh()->delivery_status)->toBe(DeliveryStatus::FAILED->value)
        ->and($order->failed_delivery_reason)->toBe('Hibás cím.');
});

it('failed reason nelkul hibazik', function (): void {
    $admin = User::factory()->admin()->create();
    $order = deliveryOrder(['delivery_status' => DeliveryStatus::ASSIGNED->value]);

    $this->actingAs($admin)
        ->post("/admin/orders/{$order->id}/delivery/failed", [
            'failed_delivery_reason' => '',
        ])
        ->assertSessionHasErrors('failed_delivery_reason');
});

it('final statuszbol nem lehet ujrainditani', function (): void {
    $admin = User::factory()->admin()->create();
    $order = deliveryOrder(['delivery_status' => DeliveryStatus::DELIVERED->value]);

    $this->actingAs($admin)
        ->post("/admin/orders/{$order->id}/delivery/start")
        ->assertSessionHas('error');

    expect($order->refresh()->delivery_status)->toBe(DeliveryStatus::DELIVERED->value);
});

it('delivery status nem keveredik az order status mezovel', function (): void {
    $admin = User::factory()->admin()->create();
    $order = deliveryOrder([
        'status' => Order::STATUS_CONFIRMED,
        'delivery_status' => DeliveryStatus::ASSIGNED->value,
    ]);

    $this->actingAs($admin)
        ->post("/admin/orders/{$order->id}/delivery/start")
        ->assertRedirect();

    expect($order->refresh()->status)->toBe(Order::STATUS_CONFIRMED)
        ->and($order->delivery_status)->toBe(DeliveryStatus::OUT_FOR_DELIVERY->value);
});
