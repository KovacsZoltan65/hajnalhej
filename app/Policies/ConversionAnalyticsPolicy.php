<?php

namespace App\Policies;

use App\Models\User;
use App\Support\PermissionRegistry;

class ConversionAnalyticsPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionRegistry::CONVERSION_ANALYTICS_VIEW);
    }

    public function viewProfitDashboard(User $user): bool
    {
        return $user->can(PermissionRegistry::PROFIT_DASHBOARD_VIEW);
    }

    public function viewCeoDashboard(User $user): bool
    {
        return $user->can(PermissionRegistry::CEO_DASHBOARD_VIEW);
    }
}
