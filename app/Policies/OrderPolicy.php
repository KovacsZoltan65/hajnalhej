<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;
use App\Support\PermissionRegistry;

class OrderPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionRegistry::ORDERS_VIEW);
    }

    public function view(User $user, Order $order): bool
    {
        return $user->can(PermissionRegistry::ORDERS_VIEW) || $order->user_id === $user->id;
    }

    public function update(User $user, Order $order): bool
    {
        return $user->can(PermissionRegistry::ORDERS_UPDATE);
    }
}
