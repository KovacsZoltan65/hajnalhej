<?php

namespace App\Policies;

use App\Models\Branch;
use App\Models\User;
use App\Support\PermissionRegistry;

class BranchPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionRegistry::BRANCHES_VIEW_ANY);
    }

    public function view(User $user, Branch $branch): bool
    {
        return $user->can(PermissionRegistry::BRANCHES_VIEW);
    }

    public function create(User $user): bool
    {
        return $user->can(PermissionRegistry::BRANCHES_CREATE);
    }

    public function update(User $user, Branch $branch): bool
    {
        return $user->can(PermissionRegistry::BRANCHES_UPDATE);
    }

    public function delete(User $user, Branch $branch): bool
    {
        return $user->can(PermissionRegistry::BRANCHES_DELETE);
    }

    public function deleteAny(User $user): bool
    {
        return $user->can(PermissionRegistry::BRANCHES_DELETE_ANY);
    }

    public function restore(User $user, Branch $branch): bool
    {
        return $user->can(PermissionRegistry::BRANCHES_UPDATE);
    }

    public function forceDelete(User $user, Branch $branch): bool
    {
        return false;
    }
}
