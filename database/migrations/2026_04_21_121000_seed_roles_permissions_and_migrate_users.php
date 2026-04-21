<?php

use App\Models\User;
use App\Support\PermissionRegistry;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $tableNames = config('permission.table_names');
        $columnNames = config('permission.column_names');
        $guardName = 'web';
        $now = now();

        foreach (PermissionRegistry::permissions() as $permissionName) {
            $exists = DB::table($tableNames['permissions'])
                ->where('name', $permissionName)
                ->where('guard_name', $guardName)
                ->exists();

            if (! $exists) {
                DB::table($tableNames['permissions'])->insert([
                    'name' => $permissionName,
                    'guard_name' => $guardName,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        foreach ([PermissionRegistry::ROLE_ADMIN, PermissionRegistry::ROLE_CUSTOMER] as $roleName) {
            $exists = DB::table($tableNames['roles'])
                ->where('name', $roleName)
                ->where('guard_name', $guardName)
                ->exists();

            if (! $exists) {
                DB::table($tableNames['roles'])->insert([
                    'name' => $roleName,
                    'guard_name' => $guardName,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        $adminRoleId = DB::table($tableNames['roles'])
            ->where('name', PermissionRegistry::ROLE_ADMIN)
            ->where('guard_name', $guardName)
            ->value('id');

        $customerRoleId = DB::table($tableNames['roles'])
            ->where('name', PermissionRegistry::ROLE_CUSTOMER)
            ->where('guard_name', $guardName)
            ->value('id');

        if ($adminRoleId !== null) {
            foreach (PermissionRegistry::adminPermissions() as $permissionName) {
                $permissionId = DB::table($tableNames['permissions'])
                    ->where('name', $permissionName)
                    ->where('guard_name', $guardName)
                    ->value('id');

                if ($permissionId === null) {
                    continue;
                }

                $exists = DB::table($tableNames['role_has_permissions'])
                    ->where('permission_id', $permissionId)
                    ->where('role_id', $adminRoleId)
                    ->exists();

                if (! $exists) {
                    DB::table($tableNames['role_has_permissions'])->insert([
                        'permission_id' => $permissionId,
                        'role_id' => $adminRoleId,
                    ]);
                }
            }
        }

        if ($customerRoleId !== null) {
            foreach (PermissionRegistry::customerPermissions() as $permissionName) {
                $permissionId = DB::table($tableNames['permissions'])
                    ->where('name', $permissionName)
                    ->where('guard_name', $guardName)
                    ->value('id');

                if ($permissionId === null) {
                    continue;
                }

                $exists = DB::table($tableNames['role_has_permissions'])
                    ->where('permission_id', $permissionId)
                    ->where('role_id', $customerRoleId)
                    ->exists();

                if (! $exists) {
                    DB::table($tableNames['role_has_permissions'])->insert([
                        'permission_id' => $permissionId,
                        'role_id' => $customerRoleId,
                    ]);
                }
            }
        }

        if (Schema::hasColumn('users', 'role')) {
            $users = DB::table('users')->select('id', 'role')->get();

            foreach ($users as $user) {
                $resolvedRoleId = $user->role === PermissionRegistry::ROLE_ADMIN
                    ? $adminRoleId
                    : $customerRoleId;

                if ($resolvedRoleId === null) {
                    continue;
                }

                $exists = DB::table($tableNames['model_has_roles'])
                    ->where('role_id', $resolvedRoleId)
                    ->where($columnNames['model_morph_key'], $user->id)
                    ->where('model_type', User::class)
                    ->exists();

                if (! $exists) {
                    DB::table($tableNames['model_has_roles'])->insert([
                        'role_id' => $resolvedRoleId,
                        'model_type' => User::class,
                        $columnNames['model_morph_key'] => $user->id,
                    ]);
                }
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tableNames = config('permission.table_names');
        $guardName = 'web';

        $roleIds = DB::table($tableNames['roles'])
            ->whereIn('name', [PermissionRegistry::ROLE_ADMIN, PermissionRegistry::ROLE_CUSTOMER])
            ->where('guard_name', $guardName)
            ->pluck('id')
            ->all();

        if ($roleIds !== []) {
            DB::table($tableNames['model_has_roles'])
                ->whereIn('role_id', $roleIds)
                ->where('model_type', User::class)
                ->delete();

            DB::table($tableNames['role_has_permissions'])
                ->whereIn('role_id', $roleIds)
                ->delete();
        }

        $permissionIds = DB::table($tableNames['permissions'])
            ->whereIn('name', PermissionRegistry::permissions())
            ->where('guard_name', $guardName)
            ->pluck('id')
            ->all();

        if ($permissionIds !== []) {
            DB::table($tableNames['model_has_permissions'])
                ->whereIn('permission_id', $permissionIds)
                ->where('model_type', User::class)
                ->delete();
        }

        DB::table($tableNames['roles'])
            ->whereIn('name', [PermissionRegistry::ROLE_ADMIN, PermissionRegistry::ROLE_CUSTOMER])
            ->where('guard_name', $guardName)
            ->delete();

        DB::table($tableNames['permissions'])
            ->whereIn('name', PermissionRegistry::permissions())
            ->where('guard_name', $guardName)
            ->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
