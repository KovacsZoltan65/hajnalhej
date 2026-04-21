<?php

namespace App\Policies;

use App\Models\User;
use App\Support\PermissionRegistry;
use Spatie\Permission\Models\Role;

class RolePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionRegistry::ROLES_VIEW);
    }

    public function view(User $user, Role $role): bool
    {
        return $user->can(PermissionRegistry::ROLES_VIEW);
    }

    public function create(User $user): bool
    {
        return $user->can(PermissionRegistry::ROLES_CREATE);
    }

    public function update(User $user, Role $role): bool
    {
        return $user->can(PermissionRegistry::ROLES_UPDATE);
    }

    public function delete(User $user, Role $role): bool
    {
        return $user->can(PermissionRegistry::ROLES_DELETE);
    }

    public function syncPermissions(User $user, Role $role): bool
    {
        return $user->can(PermissionRegistry::ROLES_ASSIGN_PERMISSIONS);
    }
}
