<?php

namespace App\Services\Audit;

use App\Models\User;
use Spatie\Permission\Models\Role;

class AuthorizationAuditService extends BaseAuditService
{
    public const LOG_NAME = 'authorization';

    public const ROLE_CREATED = 'role.created';
    public const ROLE_UPDATED = 'role.updated';
    public const ROLE_DELETED = 'role.deleted';
    public const ROLE_UPDATE_BLOCKED = 'role.update_blocked';
    public const ROLE_DELETE_BLOCKED = 'role.delete_blocked';
    public const ROLE_PERMISSIONS_SYNCED = 'role.permissions.synced';
    public const ROLE_PERMISSIONS_SYNC_BLOCKED = 'role.permissions.sync_blocked';
    public const USER_ROLES_SYNCED = 'user.roles.synced';
    public const USER_ROLES_SYNC_BLOCKED = 'user.roles.sync_blocked';

    /**
     * @return array<int, string>
     */
    public static function eventKeys(): array
    {
        return [
            self::ROLE_CREATED,
            self::ROLE_UPDATED,
            self::ROLE_DELETED,
            self::ROLE_UPDATE_BLOCKED,
            self::ROLE_DELETE_BLOCKED,
            self::ROLE_PERMISSIONS_SYNCED,
            self::ROLE_PERMISSIONS_SYNC_BLOCKED,
            self::USER_ROLES_SYNCED,
            self::USER_ROLES_SYNC_BLOCKED,
        ];
    }

    public function logRoleCreated(User $actor, Role $role): void
    {
        $this->log(
            logName: self::LOG_NAME,
            eventKey: self::ROLE_CREATED,
            description: 'Role created',
            actor: $actor,
            subject: $role,
            before: ['role' => null],
            after: ['role' => $this->roleSnapshot($role)],
            context: ['operation' => 'role.create']
        );
    }

    public function logRoleUpdated(User $actor, Role $role, string $beforeName, string $afterName): void
    {
        $this->log(
            logName: self::LOG_NAME,
            eventKey: self::ROLE_UPDATED,
            description: 'Role updated',
            actor: $actor,
            subject: $role,
            before: ['role' => ['name' => $beforeName]],
            after: ['role' => ['name' => $afterName]],
            context: ['operation' => 'role.update']
        );
    }

    public function logRoleDeleted(User $actor, Role $role): void
    {
        $this->log(
            logName: self::LOG_NAME,
            eventKey: self::ROLE_DELETED,
            description: 'Role deleted',
            actor: $actor,
            subject: $role,
            before: ['role' => $this->roleSnapshot($role)],
            after: ['role' => null],
            context: ['operation' => 'role.delete']
        );
    }

    public function logRoleUpdateBlocked(User $actor, Role $role, string $blockedReason): void
    {
        $this->log(
            logName: self::LOG_NAME,
            eventKey: self::ROLE_UPDATE_BLOCKED,
            description: 'Role update blocked',
            actor: $actor,
            subject: $role,
            before: ['role' => $this->roleSnapshot($role)],
            after: ['role' => $this->roleSnapshot($role)],
            context: [
                'operation' => 'role.update',
                'blocked_reason' => $blockedReason,
            ]
        );
    }

    public function logRoleDeleteBlocked(User $actor, Role $role, string $blockedReason): void
    {
        $this->log(
            logName: self::LOG_NAME,
            eventKey: self::ROLE_DELETE_BLOCKED,
            description: 'Role delete blocked',
            actor: $actor,
            subject: $role,
            before: ['role' => $this->roleSnapshot($role)],
            after: ['role' => $this->roleSnapshot($role)],
            context: [
                'operation' => 'role.delete',
                'blocked_reason' => $blockedReason,
            ]
        );
    }

    public function logRolePermissionsSyncBlocked(User $actor, Role $role, string $blockedReason): void
    {
        $this->log(
            logName: self::LOG_NAME,
            eventKey: self::ROLE_PERMISSIONS_SYNC_BLOCKED,
            description: 'Role permissions sync blocked',
            actor: $actor,
            subject: $role,
            before: ['role' => $this->roleSnapshot($role)],
            after: ['role' => $this->roleSnapshot($role)],
            context: [
                'operation' => 'role.permissions.sync',
                'blocked_reason' => $blockedReason,
            ]
        );
    }

    /**
     * @param array<int, string> $beforePermissions
     * @param array<int, string> $afterPermissions
     */
    public function logRolePermissionsSynced(User $actor, Role $role, array $beforePermissions, array $afterPermissions): void
    {
        $before = $this->normalizeList($beforePermissions);
        $after = $this->normalizeList($afterPermissions);
        $added = array_values(array_diff($after, $before));
        $removed = array_values(array_diff($before, $after));

        $this->log(
            logName: self::LOG_NAME,
            eventKey: self::ROLE_PERMISSIONS_SYNCED,
            description: 'Role permissions synced',
            actor: $actor,
            subject: $role,
            before: ['permissions' => $before],
            after: ['permissions' => $after],
            context: ['operation' => 'role.permissions.sync'],
            extraProperties: [
                'role' => $this->roleSnapshot($role),
                'added_permissions' => $added,
                'removed_permissions' => $removed,
            ]
        );
    }

    /**
     * @param array<int, string> $beforeRoles
     * @param array<int, string> $afterRoles
     */
    public function logUserRolesSynced(User $actor, User $targetUser, array $beforeRoles, array $afterRoles): void
    {
        $before = $this->normalizeList($beforeRoles);
        $after = $this->normalizeList($afterRoles);
        $added = array_values(array_diff($after, $before));
        $removed = array_values(array_diff($before, $after));

        $this->log(
            logName: self::LOG_NAME,
            eventKey: self::USER_ROLES_SYNCED,
            description: 'User roles synced',
            actor: $actor,
            subject: $targetUser,
            before: ['roles' => $before],
            after: ['roles' => $after],
            context: ['operation' => 'user.roles.sync'],
            extraProperties: [
                'target_user' => $this->userSnapshot($targetUser),
                'added_roles' => $added,
                'removed_roles' => $removed,
            ]
        );
    }

    /**
     * @param array<int, string> $beforeRoles
     * @param array<int, string> $attemptedRoles
     */
    public function logUserRolesSyncBlocked(
        User $actor,
        User $targetUser,
        string $blockedReason,
        array $beforeRoles,
        array $attemptedRoles,
    ): void {
        $this->log(
            logName: self::LOG_NAME,
            eventKey: self::USER_ROLES_SYNC_BLOCKED,
            description: 'User roles sync blocked',
            actor: $actor,
            subject: $targetUser,
            before: ['roles' => $this->normalizeList($beforeRoles)],
            after: ['roles' => $this->normalizeList($beforeRoles)],
            context: [
                'operation' => 'user.roles.sync',
                'blocked_reason' => $blockedReason,
                'attempted_roles' => $this->normalizeList($attemptedRoles),
            ],
            extraProperties: [
                'target_user' => $this->userSnapshot($targetUser),
                'blocked_reason' => $blockedReason,
            ]
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function roleSnapshot(Role $role): array
    {
        return [
            'id' => $role->id,
            'name' => $role->name,
            'guard_name' => $role->guard_name,
        ];
    }

}
