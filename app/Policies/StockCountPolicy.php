<?php

namespace App\Policies;

use App\Models\StockCount;
use App\Models\User;
use App\Support\PermissionRegistry;

class StockCountPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionRegistry::INVENTORY_VIEW) || $user->can(PermissionRegistry::STOCK_COUNTS_MANAGE);
    }

    public function view(User $user, StockCount $stockCount): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->can(PermissionRegistry::STOCK_COUNTS_MANAGE);
    }

    public function update(User $user, StockCount $stockCount): bool
    {
        return $user->can(PermissionRegistry::STOCK_COUNTS_MANAGE);
    }
}

