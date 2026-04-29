<?php

namespace App\Repositories;

use App\Models\User;
use App\Models\UserTemporaryPermission;
use Illuminate\Support\Collection;

class UserTemporaryPermissionRepository
{
    public function create(User $user, array $data): UserTemporaryPermission
    {
        return $user->temporaryPermissions()->create($data);
    }

    public function revoke(UserTemporaryPermission $temporaryPermission): UserTemporaryPermission
    {
        $temporaryPermission->update(['revoked_at' => now()]);

        return $temporaryPermission->refresh();
    }

    public function revokeExpired(): int
    {
        return UserTemporaryPermission::query()
            ->whereNull('revoked_at')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->update(['revoked_at' => now()]);
    }

    public function currentForUser(User $user): Collection
    {
        return $user->temporaryPermissions()
            ->currentlyValid()
            ->orderBy('permission_name')
            ->get();
    }
}
