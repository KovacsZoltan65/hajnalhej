<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Permission\Models\Role;

class UserRepository
{
    /**
     * @param array<string, mixed> $filters
     */
    public function paginateForRoleManagement(array $filters): LengthAwarePaginator
    {
        $search = trim((string) ($filters['search'] ?? ''));
        $perPage = (int) ($filters['per_page'] ?? 15);

        return User::query()
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $inner) use ($search): void {
                    $inner
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->with(['roles:id,name', 'roles.permissions:id,name', 'permissions:id,name'])
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * @param array<int, string> $roleNames
     */
    public function syncRoles(User $user, array $roleNames): void
    {
        $user->syncRoles($roleNames);
    }

    public function countUsersWithRole(string $roleName): int
    {
        $role = Role::query()
            ->where('guard_name', 'web')
            ->where('name', $roleName)
            ->first();

        if ($role === null) {
            return 0;
        }

        return $role->users()->count();
    }

    /**
     * @return array<int, string>
     */
    public function roleNames(User $user): array
    {
        return $user->roles()->pluck('name')->all();
    }
}
