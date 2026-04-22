<?php

namespace App\Services\Audit;

use App\Models\User;

class PermissionAuditService
{
    public const LOG_NAME = 'authorization';
    public const PERMISSIONS_SYNCED = 'permissions.synced';

    /**
     * @param array<int, string> $createdPermissions
     * @param array<int, string> $existingPermissions
     * @param array<int, string> $orphanPermissions
     * @param array<string, mixed> $context
     */
    public function logPermissionsSynced(
        User $actor,
        array $createdPermissions,
        array $existingPermissions,
        array $orphanPermissions,
        array $context = [],
    ): void {
        activity()
            ->useLog(self::LOG_NAME)
            ->causedBy($actor)
            ->event(self::PERMISSIONS_SYNCED)
            ->withProperties([
                'event_key' => self::PERMISSIONS_SYNCED,
                'before' => [],
                'after' => [
                    'created_permissions' => $createdPermissions,
                    'existing_permissions' => $existingPermissions,
                    'orphan_permissions' => $orphanPermissions,
                ],
                'context' => $context,
                'actor_snapshot' => [
                    'id' => $actor->id,
                    'name' => $actor->name,
                    'email' => $actor->email,
                    'roles' => $actor->getRoleNames()->values()->all(),
                ],
                'created_permissions' => $createdPermissions,
                'existing_permissions' => $existingPermissions,
                'orphan_permissions' => $orphanPermissions,
            ])
            ->log('Permissions synced from registry');
    }
}
