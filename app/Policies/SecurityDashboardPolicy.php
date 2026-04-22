<?php

namespace App\Policies;

use App\Models\User;
use App\Support\PermissionRegistry;
use Spatie\Activitylog\Models\Activity;

class SecurityDashboardPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionRegistry::SECURITY_DASHBOARD_VIEW);
    }

    public function view(User $user, Activity|string|null $activity = null): bool
    {
        return $user->can(PermissionRegistry::SECURITY_DASHBOARD_VIEW);
    }
}

