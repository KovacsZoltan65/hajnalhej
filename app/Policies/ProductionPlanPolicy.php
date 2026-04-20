<?php

namespace App\Policies;

use App\Models\ProductionPlan;
use App\Models\User;

class ProductionPlanPolicy
{
    public function viewAny(User $user): bool
    {
        return $user !== null;
    }

    public function view(User $user, ProductionPlan $productionPlan): bool
    {
        return $user !== null;
    }

    public function create(User $user): bool
    {
        return $user !== null;
    }

    public function update(User $user, ProductionPlan $productionPlan): bool
    {
        return $user !== null;
    }

    public function delete(User $user, ProductionPlan $productionPlan): bool
    {
        return $user !== null;
    }
}

