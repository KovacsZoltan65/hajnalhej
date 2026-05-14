<?php

use App\Enums\Delivery\DeliveryStatus;
use App\Enums\Orders\FulfillmentMethod;
use App\Models\Courier;
use App\Models\Order;
use App\Models\User;
use App\Support\PermissionRegistry;
use Database\Seeders\RolesAndPermissionsSeeder;

beforeEach(function (): void {
    $this->seed(RolesAndPermissionsSeeder::class);
});

function courierAssignmentOrder(array $attributes = []): Order
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

it('CourierAssignment: jogosultsaggal futar hozzarendelheto', function (): void {
    $admin = User::factory()->admin()->create();
    $order = courierAssignmentOrder();
    $courier = Courier::factory()->create([
        'status' => Courier::STATUS_ACTIVE,
        'active' => true,
    ]);

    $this->actingAs($admin)
        ->patch(route('admin.orders.assign-courier', $order), [
            'courier_id' => $courier->id,
        ])
        ->assertRedirect();

    expect($order->refresh())
        ->courier_id->toBe($courier->id)
        ->delivery_status->toBe(DeliveryStatus::ASSIGNED->value);
});

it('CourierAssignment: jogosultsag nelkul tiltott', function (): void {
    $user = User::factory()->customer()->create();
    $user->givePermissionTo([
        PermissionRegistry::ADMIN_PANEL_ACCESS,
        PermissionRegistry::ORDERS_VIEW,
    ]);

    $order = courierAssignmentOrder();
    $courier = Courier::factory()->create([
        'status' => Courier::STATUS_ACTIVE,
        'active' => true,
    ]);

    $this->actingAs($user)
        ->patch(route('admin.orders.assign-courier', $order), [
            'courier_id' => $courier->id,
        ])
        ->assertForbidden();

    expect($order->refresh()->courier_id)->toBeNull();
});

it('CourierAssignment: pickup rendeleshez nem rendelheto futar', function (): void {
    $admin = User::factory()->admin()->create();
    $order = Order::factory()->create(['fulfillment_method' => FulfillmentMethod::PICKUP->value]);
    $courier = Courier::factory()->create([
        'status' => Courier::STATUS_ACTIVE,
        'active' => true,
    ]);

    $this->actingAs($admin)
        ->patch(route('admin.orders.assign-courier', $order), [
            'courier_id' => $courier->id,
        ])
        ->assertSessionHasErrors('courier_id');

    expect($order->refresh()->courier_id)->toBeNull();
});

it('CourierAssignment: inactive courier nem rendelheto', function (): void {
    $admin = User::factory()->admin()->create();
    $order = courierAssignmentOrder();
    $courier = Courier::factory()->create([
        'status' => Courier::STATUS_INACTIVE,
        'active' => false,
    ]);

    $this->actingAs($admin)
        ->patch(route('admin.orders.assign-courier', $order), [
            'courier_id' => $courier->id,
        ])
        ->assertSessionHasErrors('courier_id');

    expect($order->refresh()->courier_id)->toBeNull();
});

it('CourierAssignment: nem letezo courier validacios hiba', function (): void {
    $admin = User::factory()->admin()->create();
    $order = courierAssignmentOrder();

    $this->actingAs($admin)
        ->patch(route('admin.orders.assign-courier', $order), [
            'courier_id' => 999999,
        ])
        ->assertSessionHasErrors('courier_id');
});

it('CourierAssignment: futar csere mukodik', function (): void {
    $admin = User::factory()->admin()->create();
    $previousCourier = Courier::factory()->create([
        'status' => Courier::STATUS_ACTIVE,
        'active' => true,
    ]);
    $newCourier = Courier::factory()->create([
        'status' => Courier::STATUS_ACTIVE,
        'active' => true,
    ]);
    $order = courierAssignmentOrder([
        'courier_id' => $previousCourier->id,
        'delivery_status' => DeliveryStatus::ASSIGNED->value,
    ]);

    $this->actingAs($admin)
        ->patch(route('admin.orders.assign-courier', $order), [
            'courier_id' => $newCourier->id,
        ])
        ->assertRedirect();

    expect($order->refresh()->courier_id)->toBe($newCourier->id);
});

it('CourierAssignment: lezart rendeleshez nem rendelheto futar', function (): void {
    $admin = User::factory()->admin()->create();
    $order = courierAssignmentOrder(['status' => Order::STATUS_COMPLETED]);
    $courier = Courier::factory()->create([
        'status' => Courier::STATUS_ACTIVE,
        'active' => true,
    ]);

    $this->actingAs($admin)
        ->patch(route('admin.orders.assign-courier', $order), [
            'courier_id' => $courier->id,
        ])
        ->assertSessionHasErrors('courier_id');

    expect($order->refresh()->courier_id)->toBeNull();
});

it('CourierAssignment: audit log letrejon', function (): void {
    $admin = User::factory()->admin()->create();
    $previousCourier = Courier::factory()->create([
        'name' => 'Régi Futár',
        'status' => Courier::STATUS_ACTIVE,
        'active' => true,
    ]);
    $newCourier = Courier::factory()->create([
        'name' => 'Új Futár',
        'status' => Courier::STATUS_ACTIVE,
        'active' => true,
    ]);
    $order = courierAssignmentOrder([
        'courier_id' => $previousCourier->id,
        'delivery_status' => DeliveryStatus::ASSIGNED->value,
    ]);

    $this->actingAs($admin)
        ->patch(route('admin.orders.assign-courier', $order), [
            'courier_id' => $newCourier->id,
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('activity_log', [
        'log_name' => 'orders',
        'event' => 'order.courier_assigned',
        'subject_type' => Order::class,
        'subject_id' => $order->id,
        'causer_id' => $admin->id,
    ]);
});
