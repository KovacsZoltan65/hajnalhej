<?php

namespace App\Policies;

use App\Models\Supplier;
use App\Models\User;
use App\Support\PermissionRegistry;

class SupplierPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionRegistry::SUPPLIERS_VIEW);
    }

    public function view(User $user, Supplier $supplier): bool
    {
        return $user->can(PermissionRegistry::SUPPLIERS_VIEW);
    }

    public function create(User $user): bool
    {
        return $user->can(PermissionRegistry::SUPPLIERS_MANAGE);
    }

    public function update(User $user, Supplier $supplier): bool
    {
        return $user->can(PermissionRegistry::SUPPLIERS_MANAGE);
    }

    public function delete(User $user, Supplier $supplier): bool
    {
        return $user->can(PermissionRegistry::SUPPLIERS_MANAGE);
    }
}

