<?php

namespace App\Services;

use App\Repositories\PermissionRepository;
use App\Repositories\RoleRepository;
use App\Support\PermissionRegistry;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

class RoleManagementService
{
    public function __construct(
        private readonly RoleRepository $roleRepository,
        private readonly PermissionRepository $permissionRepository,
    ) {
    }

    /**
     * @param array<string, mixed> $filters
     */
    public function paginateForAdmin(array $filters): LengthAwarePaginator
    {
        return $this->roleRepository->paginateForAdmin($filters);
    }

    /**
     * @return array<string, array<int, array{name:string,label:string,description:string,dangerous:bool,sort:int}>>
     */
    public function permissionGroups(): array
    {
        return $this->permissionRepository->groupedDefinitions();
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function create(array $payload): Role
    {
        return $this->roleRepository->create([
            'name' => trim((string) $payload['name']),
            'guard_name' => 'web',
        ]);
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function update(Role $role, array $payload): Role
    {
        $name = trim((string) $payload['name']);

        if (PermissionRegistry::isSystemRole($role->name) && $name !== $role->name) {
            throw ValidationException::withMessages([
                'name' => 'Rendszerszerepkor neve nem modosithato.',
            ]);
        }

        return $this->roleRepository->update($role, ['name' => $name]);
    }

    public function delete(Role $role): void
    {
        if (PermissionRegistry::isSystemRole($role->name)) {
            throw ValidationException::withMessages([
                'role' => 'Rendszerszerepkor nem torolheto.',
            ]);
        }

        $this->roleRepository->delete($role);
    }

    /**
     * @param array<int, string> $permissionNames
     */
    public function syncPermissions(Role $role, array $permissionNames): Role
    {
        $allowed = $this->permissionRepository->allowedPermissionNames();
        $invalid = array_values(array_diff($permissionNames, $allowed));

        if ($invalid !== []) {
            throw ValidationException::withMessages([
                'permissions' => 'Ismeretlen vagy nem engedelyezett jogosultsag szerepel a listaban.',
            ]);
        }

        if ($role->guard_name !== 'web') {
            throw ValidationException::withMessages([
                'permissions' => 'Csak web guard szerepkor kezelheto ezen a feluleten.',
            ]);
        }

        if ($role->name === PermissionRegistry::ROLE_ADMIN) {
            $missingCritical = array_values(array_diff(PermissionRegistry::criticalAdminPermissions(), $permissionNames));

            if ($missingCritical !== []) {
                throw ValidationException::withMessages([
                    'permissions' => 'Az admin szerepkorrol nem veheto le kritikus jogosultsag.',
                ]);
            }
        }

        $permissions = $this->permissionRepository->findByNames($permissionNames)
            ->where('guard_name', 'web');

        $role->syncPermissions($permissions);

        return $this->roleRepository->withDetails($role);
    }

    public function details(Role $role): Role
    {
        return $this->roleRepository->withDetails($role);
    }

    /**
     * @return array<int, string>
     */
    public function systemRoles(): array
    {
        return PermissionRegistry::systemRoles();
    }

    public function isSystemRole(string $roleName): bool
    {
        return PermissionRegistry::isSystemRole($roleName);
    }

    /**
     * @return array<string, mixed>
     */
    public function buildRolePayload(Role $role): array
    {
        $role = $this->details($role);
        $permissionNames = $role->permissions->pluck('name')->sort()->values()->all();

        return [
            'id' => $role->id,
            'name' => $role->name,
            'guard_name' => $role->guard_name,
            'permissions_count' => $role->permissions_count ?? $role->permissions->count(),
            'users_count' => $role->users_count ?? $role->users->count(),
            'is_system_role' => PermissionRegistry::isSystemRole($role->name),
            'permissions' => $permissionNames,
        ];
    }
}
