<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\RoleRepository;
use App\Repositories\UserRepository;
use App\Support\PermissionRegistry;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

class UserRoleAssignmentService
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly RoleRepository $roleRepository,
    ) {
    }

    /**
     * @param array<string, mixed> $filters
     */
    public function paginateUsers(array $filters): LengthAwarePaginator
    {
        return $this->userRepository->paginateForRoleManagement($filters);
    }

    /**
     * @param array<int, string> $roleNames
     */
    public function syncUserRoles(User $targetUser, array $roleNames, User $actingUser): void
    {
        $existingRoleNames = $this->roleRepository->existingRoleNames();
        $invalid = array_values(array_diff($roleNames, $existingRoleNames));

        if ($invalid !== []) {
            throw ValidationException::withMessages([
                'roles' => 'Ismeretlen szerepkor szerepel a listaban.',
            ]);
        }

        $targetCurrentRoles = $this->userRepository->roleNames($targetUser);
        $isRemovingAdmin = \in_array(PermissionRegistry::ROLE_ADMIN, $targetCurrentRoles, true)
            && ! \in_array(PermissionRegistry::ROLE_ADMIN, $roleNames, true);

        if ($isRemovingAdmin) {
            $adminUsersCount = $this->userRepository->countUsersWithRole(PermissionRegistry::ROLE_ADMIN);

            if ($adminUsersCount <= 1) {
                throw ValidationException::withMessages([
                    'roles' => 'Az utolso admin szerepkor nem veheto le.',
                ]);
            }

            if ($actingUser->id === $targetUser->id) {
                throw ValidationException::withMessages([
                    'roles' => 'Sajat magadrol nem veheted le az admin szerepkort.',
                ]);
            }
        }

        $this->userRepository->syncRoles($targetUser, $roleNames);
    }

    /**
     * @return array<int, array{name:string,is_system_role:bool}>
     */
    public function roleOptions(): array
    {
        return collect($this->roleRepository->existingRoleNames())
            ->map(fn (string $name): array => [
                'name' => $name,
                'is_system_role' => PermissionRegistry::isSystemRole($name),
            ])
            ->values()
            ->all();
    }
}
