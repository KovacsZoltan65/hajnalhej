<?php

namespace App\Repositories;

use App\Models\User;
use App\Support\PermissionRegistry;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionRepository
{
    /**
     * @return Collection<int, Permission>
     */
    public function findByNames(array $permissionNames): Collection
    {
        return Permission::query()
            ->where('guard_name', 'web')
            ->whereIn('name', $permissionNames)
            ->get();
    }

    /**
     * @return array<int, string>
     */
    public function allowedPermissionNames(): array
    {
        return PermissionRegistry::permissions();
    }

    /**
     * @return array<string, array<int, array{name:string,label:string,description:string,dangerous:bool,sort:int,system:bool,audit_sensitive:bool}>>
     */
    public function groupedDefinitions(): array
    {
        return PermissionRegistry::groupedDefinitions();
    }

    /**
     * @return array<string, array{name:string,module:string,label:string,description:string,dangerous:bool,sort:int,system:bool,audit_sensitive:bool}>
     */
    public function definitionsByName(): array
    {
        return PermissionRegistry::definitionsByName();
    }

    /**
     * @return Collection<int, Permission>
     */
    public function allWithUsage(): Collection
    {
        return Permission::query()
            ->where('guard_name', 'web')
            ->withCount('roles')
            ->with(['roles:id,name'])
            ->orderBy('name')
            ->get();
    }

    public function findByName(string $permissionName): ?Permission
    {
        return Permission::query()
            ->where('guard_name', 'web')
            ->where('name', $permissionName)
            ->withCount('roles')
            ->with(['roles:id,name'])
            ->first();
    }

    /**
     * @param array<int, string> $permissionNames
     * @return array<int, string>
     */
    public function missingNamesFromDatabase(array $permissionNames): array
    {
        $existing = Permission::query()
            ->where('guard_name', 'web')
            ->whereIn('name', $permissionNames)
            ->pluck('name')
            ->all();

        return array_values(array_diff($permissionNames, $existing));
    }

    /**
     * @param array<int, string> $permissionNames
     * @return array<int, string>
     */
    public function createMissing(array $permissionNames): array
    {
        $created = [];

        foreach ($permissionNames as $permissionName) {
            $permission = Permission::findOrCreate($permissionName, 'web');
            $created[] = $permission->name;
        }

        sort($created);

        return $created;
    }

    /**
     * @param array<int, string> $registryPermissionNames
     * @return array<int, string>
     */
    public function orphanNames(array $registryPermissionNames): array
    {
        $orphans = Permission::query()
            ->where('guard_name', 'web')
            ->whereNotIn('name', $registryPermissionNames)
            ->orderBy('name')
            ->pluck('name')
            ->all();

        return array_values($orphans);
    }

    /**
     * @param array<int, string> $permissionNames
     * @return array<string, int>
     */
    public function userUsageCountByPermissionNames(array $permissionNames): array
    {
        if ($permissionNames === []) {
            return [];
        }

        $rows = DB::table('permissions')
            ->join('role_has_permissions', 'permissions.id', '=', 'role_has_permissions.permission_id')
            ->join('model_has_roles', 'role_has_permissions.role_id', '=', 'model_has_roles.role_id')
            ->where('model_has_roles.model_type', User::class)
            ->where('permissions.guard_name', 'web')
            ->whereIn('permissions.name', $permissionNames)
            ->groupBy('permissions.name')
            ->select([
                'permissions.name',
                DB::raw('COUNT(DISTINCT model_has_roles.model_id) as users_count'),
            ])
            ->get();

        $counts = [];
        foreach ($rows as $row) {
            $counts[(string) $row->name] = (int) $row->users_count;
        }

        return $counts;
    }

    /**
     * @return array<int, string>
     */
    public function roleNamesForPermission(string $permissionName): array
    {
        return Role::query()
            ->where('guard_name', 'web')
            ->whereHas('permissions', fn ($query) => $query->where('name', $permissionName))
            ->orderBy('name')
            ->pluck('name')
            ->all();
    }
}
