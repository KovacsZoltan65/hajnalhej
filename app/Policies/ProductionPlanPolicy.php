<?php

namespace App\Policies;

use App\Models\ProductionPlan;
use App\Models\User;
use App\Support\PermissionRegistry;

class ProductionPlanPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionRegistry::PRODUCTION_PLANS_VIEW);
    }

    public function view(User $user, ProductionPlan $productionPlan): bool
    {
        return $user->can(PermissionRegistry::PRODUCTION_PLANS_VIEW);
    }

    public function create(User $user): bool
    {
        return $user->can(PermissionRegistry::PRODUCTION_PLANS_CREATE);
    }

    public function update(User $user, ProductionPlan $productionPlan): bool
    {
        return $user->can(PermissionRegistry::PRODUCTION_PLANS_UPDATE);
    }

    public function delete(User $user, ProductionPlan $productionPlan): bool
    {
        return $user->can(PermissionRegistry::PRODUCTION_PLANS_DELETE);
    }
}
