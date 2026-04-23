<?php

namespace App\Policies;

use App\Models\InventoryMovement;
use App\Models\User;
use App\Support\PermissionRegistry;

class InventoryMovementPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionRegistry::INVENTORY_VIEW);
    }

    public function view(User $user, InventoryMovement $inventoryMovement): bool
    {
        return $user->can(PermissionRegistry::INVENTORY_VIEW);
    }

    public function create(User $user): bool
    {
        return $user->can(PermissionRegistry::INVENTORY_ADJUST) || $user->can(PermissionRegistry::WASTE_MANAGE);
    }
}

