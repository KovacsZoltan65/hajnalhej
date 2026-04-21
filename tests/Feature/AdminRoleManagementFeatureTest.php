<?php

use App\Models\User;
use App\Support\PermissionRegistry;
use Database\Seeders\RolesAndPermissionsSeeder;
use Spatie\Permission\Models\Role;

beforeEach(function (): void {
    $this->seed(RolesAndPermissionsSeeder::class);
});

it('admin user can access roles index', function (): void {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get('/admin/roles')
        ->assertOk();
});

it('customer user cannot access roles index', function (): void {
    $customer = User::factory()->customer()->create();

    $this->actingAs($customer)
        ->get('/admin/roles')
        ->assertForbidden();
});

it('admin can create a new role', function (): void {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->post('/admin/roles', ['name' => 'bakery-manager'])
        ->assertRedirect('/admin/roles');

    $this->assertDatabaseHas('roles', [
        'name' => 'bakery-manager',
        'guard_name' => 'web',
    ]);
});

it('duplicate role name is rejected', function (): void {
    $admin = User::factory()->admin()->create();
    Role::findOrCreate('bakery-manager', 'web');

    $this->actingAs($admin)
        ->post('/admin/roles', ['name' => 'bakery-manager'])
        ->assertSessionHasErrors('name');
});

it('admin can sync permissions to a role', function (): void {
    $admin = User::factory()->admin()->create();
    $role = Role::findOrCreate('assistant-manager', 'web');

    $this->actingAs($admin)
        ->put("/admin/roles/{$role->id}/permissions", [
            'permissions' => [
                PermissionRegistry::PRODUCTS_VIEW,
                PermissionRegistry::ORDERS_VIEW,
            ],
        ])
        ->assertRedirect();

    expect($role->refresh()->permissions->pluck('name')->sort()->values()->all())
        ->toBe([
            PermissionRegistry::ORDERS_VIEW,
            PermissionRegistry::PRODUCTS_VIEW,
        ]);
});

it('only known permissions can be assigned to a role', function (): void {
    $admin = User::factory()->admin()->create();
    $role = Role::findOrCreate('assistant-manager', 'web');

    $this->actingAs($admin)
        ->put("/admin/roles/{$role->id}/permissions", [
            'permissions' => ['unknown.permission'],
        ])
        ->assertSessionHasErrors('permissions.0');
});

it('admin can assign roles to users', function (): void {
    $admin = User::factory()->admin()->create();
    $targetUser = User::factory()->customer()->create();
    Role::findOrCreate('bakery-manager', 'web');

    $this->actingAs($admin)
        ->put("/admin/users/{$targetUser->id}/roles", [
            'roles' => ['bakery-manager', PermissionRegistry::ROLE_CUSTOMER],
        ])
        ->assertRedirect();

    expect($targetUser->refresh()->getRoleNames()->sort()->values()->all())
        ->toBe(['bakery-manager', PermissionRegistry::ROLE_CUSTOMER]);
});

it('customer cannot assign roles', function (): void {
    $customer = User::factory()->customer()->create();
    $targetUser = User::factory()->customer()->create();

    $this->actingAs($customer)
        ->put("/admin/users/{$targetUser->id}/roles", [
            'roles' => [PermissionRegistry::ROLE_CUSTOMER],
        ])
        ->assertForbidden();
});

it('system roles cannot be deleted', function (): void {
    $admin = User::factory()->admin()->create();
    $adminRole = Role::findByName(PermissionRegistry::ROLE_ADMIN, 'web');
    $customerRole = Role::findByName(PermissionRegistry::ROLE_CUSTOMER, 'web');

    $this->actingAs($admin)
        ->delete("/admin/roles/{$adminRole->id}")
        ->assertSessionHasErrors('role');

    $this->actingAs($admin)
        ->delete("/admin/roles/{$customerRole->id}")
        ->assertSessionHasErrors('role');
});

it('last admin lockout is blocked', function (): void {
    $lastAdmin = User::factory()->admin()->create();
    $actingAdmin = $lastAdmin;

    $this->actingAs($actingAdmin)
        ->put("/admin/users/{$lastAdmin->id}/roles", [
            'roles' => [PermissionRegistry::ROLE_CUSTOMER],
        ])
        ->assertSessionHasErrors('roles');

    expect($lastAdmin->refresh()->hasRole(PermissionRegistry::ROLE_ADMIN))->toBeTrue();
});

it('permission registry groups remain consistent with permission list', function (): void {
    $flatFromGroups = collect(PermissionRegistry::groupedDefinitions())
        ->flatMap(fn (array $group): array => array_map(
            static fn (array $definition): string => $definition['name'],
            $group,
        ))
        ->sort()
        ->values()
        ->all();

    $allPermissions = collect(PermissionRegistry::permissions())
        ->sort()
        ->values()
        ->all();

    expect($flatFromGroups)->toBe($allPermissions);
});
