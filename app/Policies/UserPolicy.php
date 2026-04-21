<?php

namespace App\Policies;

use App\Models\User;
use App\Support\PermissionRegistry;

class UserPolicy
{
    public function assignRoles(User $user): bool
    {
        return $user->can(PermissionRegistry::USERS_ASSIGN_ROLES);
    }

    public function viewPermissions(User $user): bool
    {
        return $user->can(PermissionRegistry::USERS_VIEW_PERMISSIONS);
    }
}
