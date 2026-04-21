<?php

namespace App\Policies;

use App\Models\Ingredient;
use App\Models\User;
use App\Support\PermissionRegistry;

class IngredientPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionRegistry::INGREDIENTS_VIEW);
    }

    public function view(User $user, Ingredient $ingredient): bool
    {
        return $user->can(PermissionRegistry::INGREDIENTS_VIEW);
    }

    public function create(User $user): bool
    {
        return $user->can(PermissionRegistry::INGREDIENTS_CREATE);
    }

    public function update(User $user, Ingredient $ingredient): bool
    {
        return $user->can(PermissionRegistry::INGREDIENTS_UPDATE);
    }

    public function delete(User $user, Ingredient $ingredient): bool
    {
        return $user->can(PermissionRegistry::INGREDIENTS_DELETE);
    }

    public function restore(User $user, Ingredient $ingredient): bool
    {
        return $user->can(PermissionRegistry::INGREDIENTS_UPDATE);
    }

    public function forceDelete(User $user, Ingredient $ingredient): bool
    {
        return false;
    }
}
