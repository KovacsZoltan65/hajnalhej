<?php

use App\Models\User;
use App\Services\Audit\PermissionAuditService;
use App\Support\PermissionRegistry;
use Database\Seeders\RolesAndPermissionsSeeder;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function (): void {
    $this->seed(RolesAndPermissionsSeeder::class);
});

it('admin can access permissions index', function (): void {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get('/admin/permissions')
        ->assertOk();
});

it('customer cannot access permissions index', function (): void {
    $customer = User::factory()->customer()->create();

    $this->actingAs($customer)
        ->get('/admin/permissions')
        ->assertForbidden();
});

it('permissions index includes registry defined data', function (): void {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get('/admin/permissions?search=permissions.view')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Permissions/Index')
            ->where('permissions.data.0.name', PermissionRegistry::PERMISSIONS_VIEW)
            ->where('permissions.data.0.is_registry_defined', true));
});

it('permission registry metadata is localized without changing permission names', function (): void {
    app()->setLocale('hu');
    $hungarian = PermissionRegistry::definition(PermissionRegistry::PRODUCTS_VIEW);

    app()->setLocale('en');
    $english = PermissionRegistry::definition(PermissionRegistry::PRODUCTS_VIEW);

    expect($hungarian['name'])->toBe(PermissionRegistry::PRODUCTS_VIEW)
        ->and($english['name'])->toBe(PermissionRegistry::PRODUCTS_VIEW)
        ->and($hungarian['module'])->toBe('Termékek')
        ->and($hungarian['label'])->toBe('Termékek megtekintése')
        ->and($english['module'])->toBe('Products')
        ->and($english['label'])->toBe('View products');
});

it('sync creates missing registry permissions', function (): void {
    $admin = User::factory()->admin()->create();

    Permission::query()->where('name', PermissionRegistry::PERMISSIONS_SYNC)->delete();
    expect(Permission::query()->where('name', PermissionRegistry::PERMISSIONS_SYNC)->exists())->toBeFalse();

    $this->actingAs($admin)
        ->post('/admin/permissions/sync')
        ->assertRedirect();

    expect(Permission::query()->where('name', PermissionRegistry::PERMISSIONS_SYNC)->exists())->toBeTrue();
});

it('sync does not delete orphan database permissions', function (): void {
    $admin = User::factory()->admin()->create();
    Permission::findOrCreate('legacy.custom.permission', 'web');

    $this->actingAs($admin)
        ->post('/admin/permissions/sync')
        ->assertRedirect();

    expect(Permission::query()->where('name', 'legacy.custom.permission')->exists())->toBeTrue();
});

it('orphan permission state is visible in index filter', function (): void {
    $admin = User::factory()->admin()->create();
    Permission::findOrCreate('legacy.custom.permission', 'web');

    $this->actingAs($admin)
        ->get('/admin/permissions?registry_state=orphan_db_only')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Permissions/Index')
            ->where('permissions.data.0.registry_state', 'orphan_db_only')
            ->where('permissions.data.0.name', 'legacy.custom.permission'));
});

it('permission usage counts are calculated correctly', function (): void {
    $admin = User::factory()->admin()->create();
    $manager = User::factory()->customer()->create();
    $role = Role::findOrCreate('manager', 'web');
    $permission = Permission::findByName(PermissionRegistry::PRODUCTS_VIEW, 'web');
    $role->givePermissionTo($permission);
    $manager->syncRoles([$role->name]);

    $this->actingAs($admin)
        ->get('/admin/permissions?search=products.view')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Permissions/Index')
            ->where('permissions.data.0.name', PermissionRegistry::PRODUCTS_VIEW)
            ->where('permissions.data.0.roles_count', 2)
            ->where('permissions.data.0.users_count', 2));
});

it('sync writes permission audit log entry', function (): void {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->post('/admin/permissions/sync')
        ->assertRedirect();

    $activity = Activity::query()
        ->where('event', PermissionAuditService::PERMISSIONS_SYNCED)
        ->latest('id')
        ->first();

    expect($activity)->not->toBeNull()
        ->and($activity?->log_name)->toBe(PermissionAuditService::LOG_NAME)
        ->and((string) data_get($activity?->properties?->toArray() ?? [], 'event_key'))
        ->toBe(PermissionAuditService::PERMISSIONS_SYNCED);
});

it('permissions show route is protected for non admin users', function (): void {
    $customer = User::factory()->customer()->create();

    $this->actingAs($customer)
        ->get('/admin/permissions/'.PermissionRegistry::PRODUCTS_VIEW)
        ->assertForbidden();
});

it('customer cannot trigger permissions sync', function (): void {
    $customer = User::factory()->customer()->create();

    $this->actingAs($customer)
        ->post('/admin/permissions/sync')
        ->assertForbidden();
});
