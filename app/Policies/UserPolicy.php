<?php

namespace App\Policies;

use App\Models\User;
use App\Support\PermissionRegistry;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionRegistry::ADMIN_USERS_VIEW);
    }

    public function view(User $user, User $targetUser): bool
    {
        return $user->can(PermissionRegistry::ADMIN_USERS_VIEW);
    }

    public function create(User $user): bool
    {
        return $user->can(PermissionRegistry::ADMIN_USERS_CREATE);
    }

    public function update(User $user, User $targetUser): bool
    {
        return $user->can(PermissionRegistry::ADMIN_USERS_UPDATE);
    }

    public function delete(User $user, User $targetUser): bool
    {
        return $user->can(PermissionRegistry::ADMIN_USERS_DELETE);
    }

    public function manageRoles(User $user): bool
    {
        return $user->can(PermissionRegistry::ADMIN_USERS_MANAGE_ROLES)
            || $user->can(PermissionRegistry::USERS_ASSIGN_ROLES);
    }

    public function manageTemporaryPermissions(User $user): bool
    {
        return $user->can(PermissionRegistry::ADMIN_USERS_MANAGE_TEMPORARY_PERMISSIONS);
    }

    public function manageDiscounts(User $user): bool
    {
        return $user->can(PermissionRegistry::ADMIN_USERS_MANAGE_DISCOUNTS);
    }

    public function viewOrders(User $user): bool
    {
        return $user->can(PermissionRegistry::ADMIN_USERS_VIEW_ORDERS);
    }

    public function assignRoles(User $user): bool
    {
        return $user->can(PermissionRegistry::USERS_ASSIGN_ROLES);
    }

    public function viewPermissions(User $user): bool
    {
        return $user->can(PermissionRegistry::USERS_VIEW_PERMISSIONS);
    }
}
