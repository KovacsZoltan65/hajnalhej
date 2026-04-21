<?php

namespace App\Repositories;

use App\Support\PermissionRegistry;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Permission;

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
     * @return array<string, array<int, array{name:string,label:string,description:string,dangerous:bool,sort:int}>>
     */
    public function groupedDefinitions(): array
    {
        return PermissionRegistry::groupedDefinitions();
    }
}
