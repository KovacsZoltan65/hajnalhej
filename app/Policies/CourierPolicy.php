<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Courier;
use App\Models\User;
use App\Support\PermissionRegistry;

class CourierPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionRegistry::COURIERS_VIEW_ANY);
    }

    public function view(User $user, Courier $courier): bool
    {
        return $user->can(PermissionRegistry::COURIERS_VIEW);
    }

    public function create(User $user): bool
    {
        return $user->can(PermissionRegistry::COURIERS_CREATE);
    }

    public function update(User $user, Courier $courier): bool
    {
        return $user->can(PermissionRegistry::COURIERS_UPDATE);
    }

    public function delete(User $user, Courier $courier): bool
    {
        return $user->can(PermissionRegistry::COURIERS_DELETE);
    }

    public function restore(User $user, Courier $courier): bool
    {
        return $user->can(PermissionRegistry::COURIERS_UPDATE);
    }
}
