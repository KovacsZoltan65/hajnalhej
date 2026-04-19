<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WeeklyMenu;

class WeeklyMenuPolicy
{
    public function viewAny(User $user): bool
    {
        return $user !== null;
    }

    public function view(User $user, WeeklyMenu $weeklyMenu): bool
    {
        return $user !== null;
    }

    public function create(User $user): bool
    {
        return $user !== null;
    }

    public function update(User $user, WeeklyMenu $weeklyMenu): bool
    {
        return $user !== null;
    }

    public function delete(User $user, WeeklyMenu $weeklyMenu): bool
    {
        return $user !== null;
    }

    public function restore(User $user, WeeklyMenu $weeklyMenu): bool
    {
        return $user !== null;
    }

    public function forceDelete(User $user, WeeklyMenu $weeklyMenu): bool
    {
        return false;
    }
}
