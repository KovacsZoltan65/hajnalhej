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
        ->get('/admin/couriers')
        ->assertForbidden();
});

it('jogosult user listazza a futarokat', function (): void {
    $user = User::factory()->admin()->create();
    Courier::factory()->create(['name' => 'Hajnalhéj Teszt Futár', 'vehicle_type' => 'bicycle']);

    $this->actingAs($user)
        ->get('/admin/couriers')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Couriers/Index')
            ->has('couriers.data', 1)
            ->where('couriers.data.0.name', 'Hajnalhéj Teszt Futár')
            ->has('options.vehicleTypes'));
});

it('courier create mukodik', function (): void {
    $user = User::factory()->admin()->create();

    $this->actingAs($user)
        ->post('/admin/couriers', [
            'name' => 'Biciklis Futár',
            'phone' => '+36 30 111 1111',
            'email' => 'biciklis@example.test',
            'vehicle_type' => 'bicycle',
            'active' => true,
            'notes' => 'Délelőtti műszak',
            'meta' => ['zone' => 'belvaros'],
        ])
        ->assertRedirect('/admin/couriers');

    $this->assertDatabaseHas('couriers', [
        'name' => 'Biciklis Futár',
        'vehicle_type' => 'bicycle',
        'active' => true,
    ]);
});

it('invalid vehicle type hibazik', function (): void {
    $user = User::factory()->admin()->create();

    $this->actingAs($user)
        ->post('/admin/couriers', [
            'name' => 'Hibás Futár',
            'vehicle_type' => 'rocket',
            'active' => true,
        ])
        ->assertSessionHasErrors('vehicle_type');
});

it('courier update mukodik', function (): void {
    $user = User::factory()->admin()->create();
    $courier = Courier::factory()->create(['vehicle_type' => 'walking']);

    $this->actingAs($user)
        ->put("/admin/couriers/{$courier->id}", [
            'name' => 'Frissített Futár',
            'phone' => '+36 30 222 2222',
            'email' => 'frissitett@example.test',
            'vehicle_type' => 'car',
            'active' => false,
            'notes' => null,
            'meta' => null,
        ])
        ->assertRedirect('/admin/couriers');

    $this->assertDatabaseHas('couriers', [
        'id' => $courier->id,
        'name' => 'Frissített Futár',
        'vehicle_type' => 'car',
        'active' => false,
    ]);
});

it('courier delete mukodik', function (): void {
    $user = User::factory()->admin()->create();
    $courier = Courier::factory()->create();

    $this->actingAs($user)
        ->delete("/admin/couriers/{$courier->id}")
        ->assertRedirect('/admin/couriers');

    $this->assertDatabaseMissing('couriers', ['id' => $courier->id]);
});
