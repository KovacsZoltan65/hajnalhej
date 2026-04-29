<?php

use App\Models\Order;
use App\Models\User;
use App\Models\UserDiscount;
use App\Models\UserTemporaryPermission;
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
            ->has('roles')
            ->has('permissions'));
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

it('ideiglenes permission letrehozas mukodik', function (): void {
    $admin = User::factory()->admin()->create();
    $target = User::factory()->customer()->create();

    $this->actingAs($admin)
        ->post("/admin/users/{$target->id}/temporary-permissions", [
            'permission_name' => PermissionRegistry::PRODUCTS_VIEW,
            'starts_at' => now()->subHour()->format('Y-m-d H:i:s'),
            'expires_at' => now()->addDay()->format('Y-m-d H:i:s'),
            'reason' => 'Helyettesítés',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('user_temporary_permissions', [
        'user_id' => $target->id,
        'permission_name' => PermissionRegistry::PRODUCTS_VIEW,
        'granted_by' => $admin->id,
    ]);
});

it('lejart temporary permission nem aktiv', function (): void {
    $target = User::factory()->customer()->create();

    UserTemporaryPermission::query()->create([
        'user_id' => $target->id,
        'permission_name' => PermissionRegistry::PRODUCTS_VIEW,
        'starts_at' => now()->subDays(2),
        'expires_at' => now()->subDay(),
    ]);

    expect(UserTemporaryPermission::query()->currentlyValid()->count())->toBe(0);
});

it('aktiv temporary permission pivot modositas nelkul ervenyesul', function (): void {
    $target = User::factory()->customer()->create();

    UserTemporaryPermission::query()->create([
        'user_id' => $target->id,
        'permission_name' => PermissionRegistry::PRODUCTS_VIEW,
        'starts_at' => now()->subHour(),
        'expires_at' => now()->addHour(),
    ]);

    expect($target->refresh()->can(PermissionRegistry::PRODUCTS_VIEW))->toBeTrue();
});

it('discount letrehozas mukodik', function (): void {
    $admin = User::factory()->admin()->create();
    $target = User::factory()->customer()->create();

    $this->actingAs($admin)
        ->post("/admin/users/{$target->id}/discounts", [
            'type' => UserDiscount::TYPE_PERCENT,
            'value' => 15,
            'active' => true,
            'reason' => 'Törzsvásárló',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('user_discounts', [
        'user_id' => $target->id,
        'type' => UserDiscount::TYPE_PERCENT,
        'value' => 15,
        'active' => true,
        'created_by' => $admin->id,
    ]);
});

it('percent discount validacio hibazik 100 felett', function (): void {
    $admin = User::factory()->admin()->create();
    $target = User::factory()->customer()->create();

    $this->actingAs($admin)
        ->post("/admin/users/{$target->id}/discounts", [
            'type' => UserDiscount::TYPE_PERCENT,
            'value' => 120,
            'active' => true,
        ])
        ->assertSessionHasErrors('value');
});

it('user orders panelhez szukseges adat visszajon', function (): void {
    $admin = User::factory()->admin()->create();
    $target = User::factory()->customer()->create(['name' => 'Rendelő Vásárló']);
    Order::factory()->create([
        'user_id' => $target->id,
        'order_number' => 'HH-TEST-0001',
        'total' => 7500,
    ]);

    $this->actingAs($admin)
        ->get('/admin/users?search=Rendelő')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Users/Index')
            ->where('users.data.0.orders.0.order_number', 'HH-TEST-0001')
            ->where('users.data.0.orders.0.total', 7500));
});
