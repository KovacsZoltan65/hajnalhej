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
}

