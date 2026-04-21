<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\RoleRepository;
use App\Repositories\UserRepository;
use App\Services\Audit\AuthorizationAuditService;
use App\Support\PermissionRegistry;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

class UserRoleAssignmentService
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly RoleRepository $roleRepository,
        private readonly AuthorizationAuditService $auditService,
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
        $targetCurrentRoles = $this->userRepository->roleNames($targetUser);

        if ($invalid !== []) {
            throw ValidationException::withMessages([
                'roles' => 'Ismeretlen szerepkor szerepel a listaban.',
            ]);
        }

        $isRemovingAdmin = \in_array(PermissionRegistry::ROLE_ADMIN, $targetCurrentRoles, true)
            && ! \in_array(PermissionRegistry::ROLE_ADMIN, $roleNames, true);

        if ($isRemovingAdmin) {
            $adminUsersCount = $this->userRepository->countUsersWithRole(PermissionRegistry::ROLE_ADMIN);

            if ($adminUsersCount <= 1) {
                $this->auditService->logUserRolesSyncBlocked(
                    $actingUser,
                    $targetUser,
                    'last_admin_role_removal_forbidden',
                    $targetCurrentRoles,
                    $roleNames,
                );

                throw ValidationException::withMessages([
                    'roles' => 'Az utolso admin szerepkor nem veheto le.',
                ]);
            }

            if ($actingUser->id === $targetUser->id) {
                $this->auditService->logUserRolesSyncBlocked(
                    $actingUser,
                    $targetUser,
                    'self_admin_role_removal_forbidden',
                    $targetCurrentRoles,
                    $roleNames,
                );

                throw ValidationException::withMessages([
                    'roles' => 'Sajat magadrol nem veheted le az admin szerepkort.',
                ]);
            }
        }

        $this->userRepository->syncRoles($targetUser, $roleNames);
        $afterRoles = $this->userRepository->roleNames($targetUser->refresh());

        $this->auditService->logUserRolesSynced($actingUser, $targetUser, $targetCurrentRoles, $afterRoles);
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
