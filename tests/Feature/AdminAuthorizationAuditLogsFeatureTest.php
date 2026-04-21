<?php

use App\Models\User;
use App\Services\Audit\AuthorizationAuditService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Role;
use App\Support\PermissionRegistry;

beforeEach(function (): void {
    $this->seed(RolesAndPermissionsSeeder::class);
});

it('admin can access authorization audit logs index', function (): void {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get('/admin/audit-logs')
        ->assertOk();
});

it('customer cannot access authorization audit logs index', function (): void {
    $customer = User::factory()->customer()->create();

    $this->actingAs($customer)
        ->get('/admin/audit-logs')
        ->assertForbidden();
});

it('role creation writes authorization audit log entry', function (): void {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->post('/admin/roles', ['name' => 'bakery-auditor'])
        ->assertRedirect('/admin/roles');

    $activity = Activity::query()->latest('id')->first();

    expect($activity)->not->toBeNull()
        ->and($activity->log_name)->toBe(AuthorizationAuditService::LOG_NAME)
        ->and($activity->event)->toBe(AuthorizationAuditService::ROLE_CREATED)
        ->and((string) data_get($activity->properties?->toArray() ?? [], 'event_key'))->toBe(AuthorizationAuditService::ROLE_CREATED);
});

it('role permission sync writes before after and diff details', function (): void {
    $admin = User::factory()->admin()->create();
    $role = Role::findOrCreate('bakery-editor', 'web');
    $role->syncPermissions([PermissionRegistry::PRODUCTS_VIEW]);

    $this->actingAs($admin)
        ->put("/admin/roles/{$role->id}/permissions", [
            'permissions' => [PermissionRegistry::PRODUCTS_VIEW, PermissionRegistry::ORDERS_VIEW],
        ])
        ->assertRedirect();

    $activity = Activity::query()
        ->where('event', AuthorizationAuditService::ROLE_PERMISSIONS_SYNCED)
        ->latest('id')
        ->first();

    expect($activity)->not->toBeNull();

    $properties = $activity->properties?->toArray() ?? [];

    expect(data_get($properties, 'before.permissions', []))->toContain(PermissionRegistry::PRODUCTS_VIEW)
        ->and(data_get($properties, 'after.permissions', []))->toContain(PermissionRegistry::ORDERS_VIEW)
        ->and(data_get($properties, 'added_permissions', []))->toContain(PermissionRegistry::ORDERS_VIEW)
        ->and(data_get($properties, 'removed_permissions', []))->toBe([]);
});

it('user role sync writes before after and role diff details', function (): void {
    $admin = User::factory()->admin()->create();
    $targetUser = User::factory()->customer()->create();
    Role::findOrCreate('bakery-support', 'web');

    $this->actingAs($admin)
        ->put("/admin/users/{$targetUser->id}/roles", [
            'roles' => [PermissionRegistry::ROLE_CUSTOMER, 'bakery-support'],
        ])
        ->assertRedirect();

    $activity = Activity::query()
        ->where('event', AuthorizationAuditService::USER_ROLES_SYNCED)
        ->latest('id')
        ->first();

    expect($activity)->not->toBeNull();

    $properties = $activity->properties?->toArray() ?? [];

    expect(data_get($properties, 'before.roles', []))->toContain(PermissionRegistry::ROLE_CUSTOMER)
        ->and(data_get($properties, 'after.roles', []))->toContain('bakery-support')
        ->and(data_get($properties, 'added_roles', []))->toContain('bakery-support');
});

it('blocked last admin role removal writes blocked audit log', function (): void {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->put("/admin/users/{$admin->id}/roles", [
            'roles' => [PermissionRegistry::ROLE_CUSTOMER],
        ])
        ->assertSessionHasErrors('roles');

    $activity = Activity::query()
        ->where('event', AuthorizationAuditService::USER_ROLES_SYNC_BLOCKED)
        ->latest('id')
        ->first();

    expect($activity)->not->toBeNull()
        ->and((string) data_get($activity->properties?->toArray() ?? [], 'blocked_reason'))
        ->toBe('last_admin_role_removal_forbidden');
});

it('admin can open authorization audit detail page', function (): void {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->post('/admin/roles', ['name' => 'bakery-observer'])
        ->assertRedirect('/admin/roles');

    $activity = Activity::query()->latest('id')->first();

    $this->actingAs($admin)
        ->get("/admin/audit-logs/{$activity->id}")
        ->assertOk();
});
