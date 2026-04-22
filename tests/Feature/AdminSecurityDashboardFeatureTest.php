<?php

use App\Models\User;
use App\Support\PermissionRegistry;
use App\Support\SecurityRiskRegistry;
use Database\Seeders\RolesAndPermissionsSeeder;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function (): void {
    $this->seed(RolesAndPermissionsSeeder::class);
});

it('admin with permission can access security dashboard', function (): void {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get('/admin/security-dashboard')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('Admin/SecurityDashboard/Index'));
});

it('customer cannot access security dashboard', function (): void {
    $customer = User::factory()->customer()->create();

    $this->actingAs($customer)
        ->get('/admin/security-dashboard')
        ->assertForbidden();
});

it('permission risk stats include registry drift and dangerous usage', function (): void {
    $admin = User::factory()->admin()->create();
    Permission::query()->where('name', PermissionRegistry::PERMISSIONS_SYNC)->delete();
    Permission::findOrCreate('legacy.security.custom', 'web');

    $this->actingAs($admin)
        ->get('/admin/security-dashboard')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/SecurityDashboard/Index')
            ->where('permission_risk.registry_missing_in_db', 1)
            ->where('permission_risk.db_without_registry', 1)
            ->where('permission_risk.dangerous_permissions', fn (int $count) => $count > 0));
});

it('orphan permission panel surfaces orphan and missing states', function (): void {
    $admin = User::factory()->admin()->create();
    Permission::query()->where('name', PermissionRegistry::PERMISSIONS_SYNC)->delete();
    Permission::findOrCreate('legacy.dashboard.orphan', 'web');

    $this->actingAs($admin)
        ->get('/admin/security-dashboard')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('orphan_permissions', fn ($rows): bool => collect($rows)->contains(
                fn (array $row): bool => in_array($row['registry_state'], ['orphan_db_only', 'missing_in_db'], true)
            )));
});

it('privileged users list includes dangerous permission inheritance', function (): void {
    $admin = User::factory()->admin()->create();
    $manager = User::factory()->customer()->create();
    $managerRole = Role::findOrCreate('manager', 'web');
    $managerRole->syncPermissions([PermissionRegistry::ROLES_ASSIGN_PERMISSIONS, PermissionRegistry::USERS_ASSIGN_ROLES]);
    $manager->syncRoles([$managerRole->name]);

    $this->actingAs($admin)
        ->get('/admin/security-dashboard')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('privileged_users', fn ($rows): bool => collect($rows)->contains(function (array $row) use ($manager): bool {
                return (int) $row['id'] === $manager->id
                    && (int) $row['dangerous_permissions_count'] > 0;
            })));
});

it('recent critical events can be filtered by domain and risk level', function (): void {
    $admin = User::factory()->admin()->create();

    activity()
        ->useLog('authorization')
        ->causedBy($admin)
        ->event('permissions.synced')
        ->withProperties(['event_key' => 'permissions.synced'])
        ->log('Permissions synced');

    activity()
        ->useLog('user-activity')
        ->causedBy($admin)
        ->event('user.login')
        ->withProperties(['event_key' => 'user.login'])
        ->log('User login');

    $this->actingAs($admin)
        ->get('/admin/security-dashboard?log_name=authorization&risk_level=high')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('recent_critical_events', fn ($events): bool => collect($events)->every(
                fn (array $event): bool => $event['log_name'] === 'authorization' && $event['severity'] === 'high'
            )));
});

it('risk and severity mapping stay consistent with registry', function (): void {
    expect(SecurityRiskRegistry::permissionRiskLevel([
        'name' => PermissionRegistry::PERMISSIONS_SYNC,
        'registry_state' => 'synced',
        'dangerous' => true,
        'audit_sensitive' => true,
        'roles_count' => 1,
        'users_count' => 1,
    ]))->toBe('high');

    $meta = SecurityRiskRegistry::auditEventMeta('user-activity', 'user.login');
    expect($meta['severity'])->toBe('info');
});
