<?php

namespace App\Policies;

use App\Models\User;
use App\Support\PermissionRegistry;
use Spatie\Permission\Models\Permission;

class PermissionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionRegistry::PERMISSIONS_VIEW);
    }

    public function view(User $user, Permission|string|null $permission = null): bool
    {
        return $user->can(PermissionRegistry::PERMISSIONS_VIEW);
    }

    public function viewUsage(User $user, Permission|string|null $permission = null): bool
    {
        return $user->can(PermissionRegistry::PERMISSIONS_VIEW_USAGE);
    }

    public function sync(User $user, Permission|string|null $permission = null): bool
    {
        return $user->can(PermissionRegistry::PERMISSIONS_SYNC);
    }
}
