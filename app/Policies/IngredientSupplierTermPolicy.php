<?php

namespace App\Policies;

use App\Models\IngredientSupplierTerm;
use App\Models\User;
use App\Support\PermissionRegistry;

class IngredientSupplierTermPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionRegistry::SUPPLIERS_VIEW);
    }

    public function view(User $user, IngredientSupplierTerm $term): bool
    {
        return $user->can(PermissionRegistry::SUPPLIERS_VIEW);
    }

    public function create(User $user): bool
    {
        return $user->can(PermissionRegistry::SUPPLIERS_MANAGE);
    }

    public function update(User $user, IngredientSupplierTerm $term): bool
    {
        return $user->can(PermissionRegistry::SUPPLIERS_MANAGE);
    }

    public function delete(User $user, IngredientSupplierTerm $term): bool
    {
        return $user->can(PermissionRegistry::SUPPLIERS_MANAGE);
    }
}
