<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;
use App\Support\PermissionRegistry;

class ProductPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionRegistry::PRODUCTS_VIEW);
    }

    public function view(User $user, Product $product): bool
    {
        return $user->can(PermissionRegistry::PRODUCTS_VIEW);
    }

    public function create(User $user): bool
    {
        return $user->can(PermissionRegistry::PRODUCTS_CREATE);
    }

    public function update(User $user, Product $product): bool
    {
        return $user->can(PermissionRegistry::PRODUCTS_UPDATE);
    }

    public function delete(User $user, Product $product): bool
    {
        return $user->can(PermissionRegistry::PRODUCTS_DELETE);
    }

    public function restore(User $user, Product $product): bool
    {
        return $user->can(PermissionRegistry::PRODUCTS_UPDATE);
    }

    public function forceDelete(User $user, Product $product): bool
    {
        return false;
    }
}
