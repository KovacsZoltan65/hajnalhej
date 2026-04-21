<?php

namespace App\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Permission\Models\Role;

class RoleRepository
{
    /**
     * @param array<string, mixed> $filters
     */
    public function paginateForAdmin(array $filters): LengthAwarePaginator
    {
        $perPage = (int) ($filters['per_page'] ?? 15);
        $search = trim((string) ($filters['search'] ?? ''));

        return Role::query()
            ->where('guard_name', 'web')
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where('name', 'like', "%{$search}%");
            })
            ->withCount(['permissions', 'users'])
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * @return array<int, string>
     */
    public function existingRoleNames(): array
    {
        return Role::query()
            ->where('guard_name', 'web')
            ->orderBy('name')
            ->pluck('name')
            ->all();
    }

    public function create(array $payload): Role
    {
        return Role::query()->create($payload);
    }

    public function update(Role $role, array $payload): Role
    {
        $role->update($payload);

        return $role->refresh();
    }

    public function delete(Role $role): void
    {
        $role->delete();
    }

    public function withDetails(Role $role): Role
    {
        return $role->loadCount(['permissions', 'users'])
            ->load(['permissions:id,name', 'users:id,name,email']);
    }
}
