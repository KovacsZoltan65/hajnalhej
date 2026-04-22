<?php

namespace App\Support;

class SecurityRiskRegistry
{
    /**
     * @return array<int, string>
     */
    public static function riskLevels(): array
    {
        return ['critical', 'high', 'medium', 'low', 'info'];
    }

    /**
     * @return array<int, string>
     */
    public static function securityCriticalPermissions(): array
    {
        return [
            PermissionRegistry::ADMIN_PANEL_ACCESS,
            PermissionRegistry::PERMISSIONS_SYNC,
            PermissionRegistry::ROLES_ASSIGN_PERMISSIONS,
            PermissionRegistry::USERS_ASSIGN_ROLES,
            PermissionRegistry::SECURITY_DASHBOARD_VIEW,
        ];
    }

    public static function permissionRiskLevel(array $permissionRow): string
    {
        $name = (string) ($permissionRow['name'] ?? '');
        $registryState = (string) ($permissionRow['registry_state'] ?? 'synced');
        $dangerous = (bool) ($permissionRow['dangerous'] ?? false);
        $auditSensitive = (bool) ($permissionRow['audit_sensitive'] ?? false);
        $rolesCount = (int) ($permissionRow['roles_count'] ?? 0);
        $usersCount = (int) ($permissionRow['users_count'] ?? 0);
        $isSecurityCritical = in_array($name, self::securityCriticalPermissions(), true);

        if ($registryState !== 'synced') {
            return 'high';
        }

        if ($dangerous && $isSecurityCritical && ($rolesCount > 1 || $usersCount > 3)) {
            return 'critical';
        }

        if ($dangerous && $auditSensitive) {
            return 'high';
        }

        if ($dangerous || $isSecurityCritical) {
            return 'medium';
        }

        if ($auditSensitive) {
            return 'low';
        }

        return 'info';
    }

    /**
     * @return array<string, array{label:string,severity:string,critical:bool}>
     */
    public static function auditEventMap(): array
    {
        return [
            'authorization:role.delete_blocked' => ['label' => 'Role torles blokkolva', 'severity' => 'high', 'critical' => true],
            'authorization:role.update_blocked' => ['label' => 'Role frissites blokkolva', 'severity' => 'high', 'critical' => true],
            'authorization:role.permissions.synced' => ['label' => 'Role permission sync', 'severity' => 'high', 'critical' => true],
            'authorization:user.roles.sync_blocked' => ['label' => 'User role sync blokkolva', 'severity' => 'high', 'critical' => true],
            'authorization:permissions.synced' => ['label' => 'Permission registry sync', 'severity' => 'high', 'critical' => true],
            'orders:order.cancelled' => ['label' => 'Rendeles torolve', 'severity' => 'high', 'critical' => true],
            'orders:order.status.updated' => ['label' => 'Rendeles statusz frissitve', 'severity' => 'medium', 'critical' => true],
            'orders:order.pickup.updated' => ['label' => 'Atveteli adat modositva', 'severity' => 'medium', 'critical' => true],
            'orders:order.internal_note.updated' => ['label' => 'Belső jegyzet frissitve', 'severity' => 'medium', 'critical' => true],
            'user-activity:user.login' => ['label' => 'Felhasznaloi bejelentkezes', 'severity' => 'info', 'critical' => true],
            'user-activity:user.email.verified' => ['label' => 'Email megerositve', 'severity' => 'info', 'critical' => true],
        ];
    }

    /**
     * @return array{label:string,severity:string,critical:bool}
     */
    public static function auditEventMeta(string $logName, ?string $event): array
    {
        $key = $logName.':'.($event ?? '');

        return self::auditEventMap()[$key] ?? [
            'label' => $event ?: 'Ismeretlen esemeny',
            'severity' => 'low',
            'critical' => false,
        ];
    }
}

