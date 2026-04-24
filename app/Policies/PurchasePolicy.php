<?php

namespace App\Policies;

use App\Models\Purchase;
use App\Models\User;
use App\Support\PermissionRegistry;

class PurchasePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionRegistry::PURCHASES_VIEW);
    }

    public function view(User $user, Purchase $purchase): bool
    {
        return $user->can(PermissionRegistry::PURCHASES_VIEW);
    }

    public function create(User $user): bool
    {
        return $user->can(PermissionRegistry::PURCHASES_MANAGE);
    }

    public function update(User $user, Purchase $purchase): bool
    {
        return $user->can(PermissionRegistry::PURCHASES_MANAGE);
    }

    public function delete(User $user, Purchase $purchase): bool
    {
        return $user->can(PermissionRegistry::PURCHASES_MANAGE);
    }
}

