<?php

use App\Models\User;
use App\Support\PermissionRegistry;
use Database\Seeders\RolesAndPermissionsSeeder;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Role;

beforeEach(function (): void {
    $this->seed(RolesAndPermissionsSeeder::class);
});

it('admin latja a users indexet', function (): void {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get('/admin/users')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Users/Index')
            ->has('users.data')
            ->has('roles'));
});

it('jogosultsag nelkuli user nem latja a users indexet', function (): void {
    $customer = User::factory()->customer()->create();

    $this->actingAs($customer)
        ->get('/admin/users')
        ->assertForbidden();
});

it('user letrehozas mukodik', function (): void {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->post('/admin/users', [
            'name' => 'Pék Admin',
            'email' => 'pekadmin@example.test',
            'phone' => '+36301234567',
            'status' => User::STATUS_ACTIVE,
            'password' => 'secret-password',
            'roles' => [PermissionRegistry::ROLE_CUSTOMER],
        ])
        ->assertRedirect('/admin/users');

    $this->assertDatabaseHas('users', [
        'name' => 'Pék Admin',
        'email' => 'pekadmin@example.test',
        'phone' => '+36301234567',
        'status' => User::STATUS_ACTIVE,
    ]);

    expect(User::query()->where('email', 'pekadmin@example.test')->first()?->hasRole(PermissionRegistry::ROLE_CUSTOMER))->toBeTrue();
});

it('user frissites mukodik', function (): void {
    $admin = User::factory()->admin()->create();
    $target = User::factory()->customer()->create(['status' => User::STATUS_ACTIVE]);

    $this->actingAs($admin)
        ->put("/admin/users/{$target->id}", [
            'name' => 'Frissített Vásárló',
            'email' => 'frissitett@example.test',
            'phone' => '+36307654321',
            'status' => User::STATUS_INACTIVE,
            'password' => '',
            'roles' => [PermissionRegistry::ROLE_CUSTOMER],
        ])
        ->assertRedirect('/admin/users');

    $this->assertDatabaseHas('users', [
        'id' => $target->id,
        'name' => 'Frissített Vásárló',
        'email' => 'frissitett@example.test',
        'status' => User::STATUS_INACTIVE,
    ]);
});

it('role sync mukodik user frissiteskor', function (): void {
    $admin = User::factory()->admin()->create();
    $target = User::factory()->customer()->create();
    Role::findOrCreate('bakery-manager', 'web');

    $this->actingAs($admin)
        ->put("/admin/users/{$target->id}", [
            'name' => $target->name,
            'email' => $target->email,
            'phone' => null,
            'status' => User::STATUS_ACTIVE,
            'password' => '',
            'roles' => ['bakery-manager'],
        ])
        ->assertRedirect('/admin/users');

    expect($target->refresh()->getRoleNames()->values()->all())->toBe(['bakery-manager']);
});
