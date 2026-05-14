<?php

use App\Models\Courier;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function (): void {
    $this->seed(RolesAndPermissionsSeeder::class);
});

it('jogosulatlan user nem eri el a couriers indexet', function (): void {
    $user = User::factory()->customer()->create();

    $this->actingAs($user)
        ->get(route('admin.couriers.index'))
        ->assertForbidden();
});

it('jogosult user listazza a futarokat', function (): void {
    $user = User::factory()->admin()->create();
    Courier::factory()->create(['name' => 'Hajnalhéj Teszt Futár', 'status' => Courier::STATUS_ACTIVE]);

    $this->actingAs($user)
        ->get(route('admin.couriers.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Couriers/Index')
            ->has('couriers.data', 1)
            ->where('couriers.data.0.name', 'Hajnalhéj Teszt Futár')
            ->where('couriers.data.0.status', Courier::STATUS_ACTIVE)
            ->has('options.statusOptions'));
});

it('courier create mukodik', function (): void {
    $user = User::factory()->admin()->create();

    $this->actingAs($user)
        ->post(route('admin.couriers.store'), [
            'name' => 'Biciklis Futár',
            'phone' => '+36 30 111 1111',
            'email' => 'biciklis@example.test',
            'status' => Courier::STATUS_ACTIVE,
            'notes' => 'Délelőtti műszak',
        ])
        ->assertRedirect(route('admin.couriers.index', absolute: false));

    $this->assertDatabaseHas('couriers', [
        'name' => 'Biciklis Futár',
        'status' => Courier::STATUS_ACTIVE,
        'active' => true,
    ]);

    $this->assertDatabaseHas('activity_log', [
        'log_name' => 'couriers',
        'event' => 'courier.created',
    ]);
});

it('courier validacio hibazik ervenytelen statuszra', function (): void {
    $user = User::factory()->admin()->create();

    $this->actingAs($user)
        ->post(route('admin.couriers.store'), [
            'name' => 'Hibás Futár',
            'status' => 'paused',
        ])
        ->assertSessionHasErrors('status');
});

it('courier update mukodik', function (): void {
    $user = User::factory()->admin()->create();
    $courier = Courier::factory()->create(['status' => Courier::STATUS_ACTIVE, 'active' => true]);

    $this->actingAs($user)
        ->put(route('admin.couriers.update', $courier), [
            'name' => 'Frissített Futár',
            'phone' => '+36 30 222 2222',
            'email' => 'frissitett@example.test',
            'status' => Courier::STATUS_INACTIVE,
            'notes' => null,
        ])
        ->assertRedirect(route('admin.couriers.index', absolute: false));

    $this->assertDatabaseHas('couriers', [
        'id' => $courier->id,
        'name' => 'Frissített Futár',
        'status' => Courier::STATUS_INACTIVE,
        'active' => false,
    ]);

    $this->assertDatabaseHas('activity_log', [
        'log_name' => 'couriers',
        'event' => 'courier.updated',
    ]);
});

it('courier delete mukodik', function (): void {
    $user = User::factory()->admin()->create();
    $courier = Courier::factory()->create();

    $this->actingAs($user)
        ->delete(route('admin.couriers.destroy', $courier))
        ->assertRedirect(route('admin.couriers.index', absolute: false));

    $this->assertSoftDeleted('couriers', ['id' => $courier->id]);

    $this->assertDatabaseHas('activity_log', [
        'log_name' => 'couriers',
        'event' => 'courier.deleted',
    ]);
});

it('status szerint szur', function (): void {
    $user = User::factory()->admin()->create();
    Courier::factory()->create(['name' => 'Aktív Futár', 'status' => Courier::STATUS_ACTIVE]);
    Courier::factory()->create(['name' => 'Inaktív Futár', 'status' => Courier::STATUS_INACTIVE]);

    $this->actingAs($user)
        ->get(route('admin.couriers.index', ['status' => Courier::STATUS_INACTIVE]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('couriers.data', 1)
            ->where('couriers.data.0.name', 'Inaktív Futár'));
});

it('nev telefon es email alapjan keres', function (): void {
    $user = User::factory()->admin()->create();
    Courier::factory()->create([
        'name' => 'Keresett Futár',
        'phone' => '+36 30 999 0000',
        'email' => 'keresett@example.test',
    ]);
    Courier::factory()->create(['name' => 'Másik Futár']);

    $this->actingAs($user)
        ->get(route('admin.couriers.index', ['search' => '999']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('couriers.data', 1)
            ->where('couriers.data.0.email', 'keresett@example.test'));
});
