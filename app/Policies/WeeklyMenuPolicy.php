<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WeeklyMenu;
use App\Support\PermissionRegistry;

class WeeklyMenuPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionRegistry::WEEKLY_MENU_VIEW);
    }

    public function view(User $user, WeeklyMenu $weeklyMenu): bool
    {
        return $user->can(PermissionRegistry::WEEKLY_MENU_VIEW);
    }

    public function create(User $user): bool
    {
        return $user->can(PermissionRegistry::WEEKLY_MENU_CREATE);
    }

    public function update(User $user, WeeklyMenu $weeklyMenu): bool
    {
        return $user->can(PermissionRegistry::WEEKLY_MENU_UPDATE);
    }

    public function delete(User $user, WeeklyMenu $weeklyMenu): bool
    {
        return $user->can(PermissionRegistry::WEEKLY_MENU_DELETE);
    }

    public function restore(User $user, WeeklyMenu $weeklyMenu): bool
    {
        return $user->can(PermissionRegistry::WEEKLY_MENU_UPDATE);
    }

    public function forceDelete(User $user, WeeklyMenu $weeklyMenu): bool
    {
        return false;
    }
}
