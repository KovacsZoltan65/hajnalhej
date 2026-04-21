<?php

namespace App\Policies;

use App\Models\User;
use App\Support\PermissionRegistry;
use Spatie\Activitylog\Models\Activity;

class AuthorizationAuditPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionRegistry::AUDIT_LOGS_VIEW);
    }

    public function view(User $user, Activity $activity): bool
    {
        return $user->can(PermissionRegistry::AUDIT_LOGS_VIEW);
    }
}
