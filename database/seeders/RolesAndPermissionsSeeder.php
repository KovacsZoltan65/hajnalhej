<?php

namespace Database\Seeders;

use App\Support\PermissionRegistry;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (PermissionRegistry::permissions() as $permissionName) {
            Permission::findOrCreate($permissionName, 'web');
        }

        $adminRole = Role::findOrCreate(PermissionRegistry::ROLE_ADMIN, 'web');
        $customerRole = Role::findOrCreate(PermissionRegistry::ROLE_CUSTOMER, 'web');

        $adminRole->syncPermissions(PermissionRegistry::adminPermissions());
        $customerRole->syncPermissions(PermissionRegistry::customerPermissions());
    }
}
