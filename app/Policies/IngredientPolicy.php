<?php

namespace App\Policies;

use App\Models\Ingredient;
use App\Models\User;

class IngredientPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, Ingredient $ingredient): bool
    {
        return $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Ingredient $ingredient): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Ingredient $ingredient): bool
    {
        return $user->isAdmin();
    }

    public function restore(User $user, Ingredient $ingredient): bool
    {
        return $user->isAdmin();
    }

    public function forceDelete(User $user, Ingredient $ingredient): bool
    {
        return false;
    }
}
