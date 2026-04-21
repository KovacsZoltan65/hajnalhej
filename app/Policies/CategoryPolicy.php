<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\User;
use App\Support\PermissionRegistry;

class CategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionRegistry::CATEGORIES_VIEW);
    }

    public function view(User $user, Category $category): bool
    {
        return $user->can(PermissionRegistry::CATEGORIES_VIEW);
    }

    public function create(User $user): bool
    {
        return $user->can(PermissionRegistry::CATEGORIES_CREATE);
    }

    public function update(User $user, Category $category): bool
    {
        return $user->can(PermissionRegistry::CATEGORIES_UPDATE);
    }

    public function delete(User $user, Category $category): bool
    {
        return $user->can(PermissionRegistry::CATEGORIES_DELETE);
    }

    public function restore(User $user, Category $category): bool
    {
        return $user->can(PermissionRegistry::CATEGORIES_UPDATE);
    }

    public function forceDelete(User $user, Category $category): bool
    {
        return false;
    }
}
