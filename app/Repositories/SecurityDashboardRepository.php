<?php

namespace App\Repositories;

use App\Models\User;
use App\Support\PermissionRegistry;
use App\Support\SecurityRiskRegistry;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Permission;

class SecurityDashboardRepository
{
    public function __construct(
        private readonly PermissionRepository $permissionRepository,
    ) {
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function permissionRows(): Collection
    {
        $definitions = $this->permissionRepository->definitionsByName();
        $dbPermissions = $this->permissionRepository->allWithUsage()->keyBy('name');
        $usageCounts = $this->permissionRepository->userUsageCountByPermissionNames($dbPermissions->keys()->all());
        $rows = collect();

        foreach ($definitions as $name => $definition) {
            /** @var Permission|null $permission */
            $permission = $dbPermissions->get($name);
            $roleNames = $permission?->roles?->pluck('name')->sort()->values()->all() ?? [];

            $row = [
                'name' => $name,
                'label' => $definition['label'],
                'description' => $definition['description'],
                'module' => $definition['module'],
                'dangerous' => (bool) $definition['dangerous'],
                'system' => (bool) $definition['system'],
                'audit_sensitive' => (bool) $definition['audit_sensitive'],
                'guard_name' => $permission?->guard_name ?? 'web',
                'is_registry_defined' => true,
                'exists_in_database' => $permission instanceof Permission,
                'registry_state' => $permission instanceof Permission ? 'synced' : 'missing_in_db',
                'roles_count' => count($roleNames),
                'users_count' => (int) ($usageCounts[$name] ?? 0),
                'role_names' => $roleNames,
            ];

            $row['risk_level'] = SecurityRiskRegistry::permissionRiskLevel($row);
            $rows->push($row);
        }

        foreach ($dbPermissions as $name => $permission) {
            if (isset($definitions[$name])) {
                continue;
            }

            $roleNames = $permission->roles->pluck('name')->sort()->values()->all();
            $row = [
                'name' => $name,
                'label' => $name,
                'description' => 'Csak adatbázisban letezo permission (registry drift).',
                'module' => 'Orphan / Custom',
                'dangerous' => false,
                'system' => false,
                'audit_sensitive' => false,
                'guard_name' => $permission->guard_name,
                'is_registry_defined' => false,
                'exists_in_database' => true,
                'registry_state' => 'orphan_db_only',
                'roles_count' => count($roleNames),
                'users_count' => (int) ($usageCounts[$name] ?? 0),
                'role_names' => $roleNames,
            ];

            $row['risk_level'] = SecurityRiskRegistry::permissionRiskLevel($row);
            $rows->push($row);
        }

        return $rows->values();
    }

    /**
     * @param Collection<int, array<string, mixed>> $permissionRows
     * @return array<string, mixed>
     */
    public function permissionRiskStats(Collection $permissionRows): array
    {
        $totalPermissions = $permissionRows->count();
        $dangerousRows = $permissionRows->where('dangerous', true);
        $auditSensitiveRows = $permissionRows->where('audit_sensitive', true);
        $dangerousPermissionNames = $dangerousRows->pluck('name')->values()->all();
        $securityCriticalPermissionNames = SecurityRiskRegistry::securityCriticalPermissions();

        $distribution = collect(SecurityRiskRegistry::riskLevels())
            ->mapWithKeys(fn (string $level): array => [
                $level => $permissionRows->where('risk_level', $level)->count(),
            ])
            ->all();

        return [
            'total_permissions' => $totalPermissions,
            'dangerous_permissions' => $dangerousRows->count(),
            'audit_sensitive_permissions' => $auditSensitiveRows->count(),
            'roles_with_dangerous_permissions' => $this->rolesWithPermissionsCount($dangerousPermissionNames),
            'users_with_dangerous_permissions' => $this->usersWithPermissionsCount($dangerousPermissionNames),
            'orphan_permissions' => $permissionRows->where('registry_state', 'orphan_db_only')->count(),
            'unused_permissions' => $permissionRows->where('roles_count', 0)->count(),
            'db_without_registry' => $permissionRows->where('registry_state', 'orphan_db_only')->count(),
            'registry_missing_in_db' => $permissionRows->where('registry_state', 'missing_in_db')->count(),
            'critical_security_permissions_spread' => $this->rolesWithPermissionsCount($securityCriticalPermissionNames),
            'risk_distribution' => $distribution,
        ];
    }

    /**
     * @param Collection<int, array<string, mixed>> $permissionRows
     * @return Collection<int, array<string, mixed>>
     */
    public function orphanPermissionInsights(Collection $permissionRows, bool $dangerousOnly, int $limit): Collection
    {
        $rows = $permissionRows
            ->filter(function (array $row): bool {
                $criticalSpread = in_array((string) $row['name'], SecurityRiskRegistry::securityCriticalPermissions(), true)
                    && (int) $row['roles_count'] > 1;

                return in_array((string) $row['registry_state'], ['orphan_db_only', 'missing_in_db'], true)
                    || (int) $row['roles_count'] === 0
                    || ((bool) $row['dangerous'] && ! (bool) $row['audit_sensitive'])
                    || $criticalSpread;
            });

        if ($dangerousOnly) {
            $rows = $rows->where('dangerous', true);
        }

        return $rows
            ->map(function (array $row): array {
                $issue = $this->orphanIssueForRow($row);

                return [
                    ...$row,
                    'issue' => $issue['issue'],
                    'suggested_action' => $issue['suggested_action'],
                ];
            })
            ->sortByDesc(fn (array $row): int => $this->riskWeight((string) $row['risk_level']))
            ->take($limit)
            ->values();
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function privilegedUsers(int $limit, string $riskFilter, bool $dangerousOnly): Collection
    {
        $stats = DB::table('model_has_roles')
            ->leftJoin('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->leftJoin('role_has_permissions', 'role_has_permissions.role_id', '=', 'roles.id')
            ->leftJoin('permissions', 'permissions.id', '=', 'role_has_permissions.permission_id')
            ->where('model_has_roles.model_type', User::class)
            ->where('roles.guard_name', 'web')
            ->groupBy('model_has_roles.model_id')
            ->select([
                'model_has_roles.model_id as user_id',
                DB::raw('COUNT(DISTINCT permissions.id) as effective_permissions_count'),
                DB::raw(sprintf(
                    "COUNT(DISTINCT CASE WHEN permissions.name IN ('%s') THEN permissions.id END) as dangerous_permissions_count",
                    implode("','", $this->dangerousPermissionNames())
                )),
                DB::raw(sprintf(
                    "COUNT(DISTINCT CASE WHEN permissions.name IN ('%s') THEN permissions.id END) as security_critical_permissions_count",
                    implode("','", SecurityRiskRegistry::securityCriticalPermissions())
                )),
            ])
            ->get()
            ->keyBy('user_id');

        /** @var EloquentCollection<int, User> $users */
        $users = User::query()
            ->whereIn('id', array_map('intval', array_keys($stats->all())))
            ->with('roles:id,name')
            ->get();

        $lastActivityMap = $this->lastRelevantActivityMap($users->pluck('id')->all());

        $rows = $users->map(function (User $user) use ($stats, $lastActivityMap): array {
            $stat = $stats->get($user->id);
            $effective = (int) ($stat->effective_permissions_count ?? 0);
            $dangerous = (int) ($stat->dangerous_permissions_count ?? 0);
            $securityCritical = (int) ($stat->security_critical_permissions_count ?? 0);
            $risk = $this->userRiskLevel($effective, $dangerous, $securityCritical, $user->hasRole(PermissionRegistry::ROLE_ADMIN));

            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->roles->pluck('name')->sort()->values()->all(),
                'is_admin' => $user->hasRole(PermissionRegistry::ROLE_ADMIN),
                'effective_permissions_count' => $effective,
                'dangerous_permissions_count' => $dangerous,
                'security_critical_permissions_count' => $securityCritical,
                'risk_level' => $risk,
                'last_relevant_activity_at' => $lastActivityMap[$user->id] ?? null,
            ];
        });

        if ($dangerousOnly) {
            $rows = $rows->where('dangerous_permissions_count', '>', 0)->values();
        }

        if ($riskFilter !== 'all') {
            $rows = $rows->where('risk_level', $riskFilter)->values();
        }

        return $rows
            ->sortBy([
                ['dangerous_permissions_count', 'desc'],
                ['security_critical_permissions_count', 'desc'],
                ['effective_permissions_count', 'desc'],
            ])
            ->take($limit)
            ->values();
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function recentCriticalAuditEvents(CarbonInterface $since, ?string $logName, string $riskFilter, int $limit): Collection
    {
        $query = Activity::query()
            ->with(['causer', 'subject'])
            ->where('created_at', '>=', $since)
            ->orderByDesc('created_at');

        if ($logName !== null) {
            $query->where('log_name', $logName);
        } else {
            $query->whereIn('log_name', ['authorization', 'orders', 'user-activity']);
        }

        $eventMap = SecurityRiskRegistry::auditEventMap();
        $allowedByLog = collect(array_keys($eventMap))
            ->map(fn (string $key): array => explode(':', $key, 2))
            ->groupBy(fn (array $parts): string => $parts[0])
            ->map(fn (Collection $items): array => $items->pluck(1)->all())
            ->all();

        $query->where(function ($subQuery) use ($allowedByLog): void {
            foreach ($allowedByLog as $allowedLog => $events) {
                $subQuery->orWhere(function ($logQuery) use ($allowedLog, $events): void {
                    $logQuery->where('log_name', $allowedLog)->whereIn('event', $events);
                });
            }
        });

        $rows = $query->limit($limit * 3)->get()->map(function (Activity $activity): array {
            $meta = SecurityRiskRegistry::auditEventMeta((string) $activity->log_name, $activity->event);
            $properties = $activity->properties?->toArray() ?? [];

            return [
                'id' => $activity->id,
                'timestamp' => $activity->created_at?->toIso8601String(),
                'log_name' => (string) $activity->log_name,
                'event_key' => (string) ($activity->event ?? data_get($properties, 'event_key', '')),
                'label' => $meta['label'],
                'severity' => $meta['severity'],
                'causer' => $this->actorLabel($activity),
                'subject' => $this->subjectLabel($activity),
                'summary' => $this->eventSummary($activity, $properties),
            ];
        });

        if ($riskFilter !== 'all') {
            $rows = $rows->where('severity', $riskFilter)->values();
        }

        return $rows->take($limit)->values();
    }

    public function findActivityById(int $id): ?Activity
    {
        return Activity::query()
            ->with(['causer', 'subject'])
            ->find($id);
    }

    /**
     * @return array{issue:string,suggested_action:string}
     */
    private function orphanIssueForRow(array $row): array
    {
        if ((string) $row['registry_state'] === 'orphan_db_only') {
            return [
                'issue' => 'DB-ben van, registry-ben nincs',
                'suggested_action' => 'Vizsgald felul a custom permissiont vagy vezesd be a registry-be.',
            ];
        }

        if ((string) $row['registry_state'] === 'missing_in_db') {
            return [
                'issue' => 'Registry-ben van, DB-ben hianyzik',
                'suggested_action' => 'Futtass permission registry syncet.',
            ];
        }

        if ((bool) $row['dangerous'] && ! (bool) $row['audit_sensitive']) {
            return [
                'issue' => 'Dangerous permission audit-sensitive jeloles nelkul',
                'suggested_action' => 'Jelold audit-sensitive-kent a registryben.',
            ];
        }

        if (in_array((string) $row['name'], SecurityRiskRegistry::securityCriticalPermissions(), true) && (int) $row['roles_count'] > 1) {
            return [
                'issue' => 'System-critical permission tul sok role-ra osztva',
                'suggested_action' => 'Szukitsd a role-hozzarendelest a legszuksegesebb korre.',
            ];
        }

        return [
            'issue' => 'Nincs role-hoz rendelve',
            'suggested_action' => 'Ellenorizd, szukseges-e megtartani ezt a permissiont.',
        ];
    }

    private function rolesWithPermissionsCount(array $permissionNames): int
    {
        if ($permissionNames === []) {
            return 0;
        }

        return (int) DB::table('role_has_permissions')
            ->join('permissions', 'permissions.id', '=', 'role_has_permissions.permission_id')
            ->whereIn('permissions.name', $permissionNames)
            ->where('permissions.guard_name', 'web')
            ->distinct('role_has_permissions.role_id')
            ->count('role_has_permissions.role_id');
    }

    private function usersWithPermissionsCount(array $permissionNames): int
    {
        if ($permissionNames === []) {
            return 0;
        }

        return (int) DB::table('permissions')
            ->join('role_has_permissions', 'permissions.id', '=', 'role_has_permissions.permission_id')
            ->join('model_has_roles', 'role_has_permissions.role_id', '=', 'model_has_roles.role_id')
            ->where('model_has_roles.model_type', User::class)
            ->where('permissions.guard_name', 'web')
            ->whereIn('permissions.name', $permissionNames)
            ->distinct('model_has_roles.model_id')
            ->count('model_has_roles.model_id');
    }

    /**
     * @return array<int, string>
     */
    private function dangerousPermissionNames(): array
    {
        return collect(PermissionRegistry::definitions())
            ->filter(fn (array $definition): bool => (bool) $definition['dangerous'])
            ->pluck('name')
            ->values()
            ->all();
    }

    /**
     * @param array<int, int> $userIds
     * @return array<int, string>
     */
    private function lastRelevantActivityMap(array $userIds): array
    {
        if ($userIds === []) {
            return [];
        }

        $rows = DB::table('activity_log')
            ->where('causer_type', User::class)
            ->whereIn('causer_id', $userIds)
            ->groupBy('causer_id')
            ->select([
                'causer_id',
                DB::raw('MAX(created_at) as last_at'),
            ])
            ->get();

        $map = [];
        foreach ($rows as $row) {
            $map[(int) $row->causer_id] = (string) $row->last_at;
        }

        return $map;
    }

    private function userRiskLevel(int $effective, int $dangerous, int $securityCritical, bool $isAdmin): string
    {
        if ($dangerous >= 3 || $securityCritical >= 2) {
            return 'critical';
        }

        if ($dangerous >= 1 || $isAdmin) {
            return 'high';
        }

        if ($effective >= 8) {
            return 'medium';
        }

        return 'low';
    }

    private function actorLabel(Activity $activity): string
    {
        if ($activity->causer instanceof User) {
            return "{$activity->causer->name} ({$activity->causer->email})";
        }

        if ($activity->causer !== null && method_exists($activity->causer, 'getKey')) {
            return class_basename($activity->causer).'#'.$activity->causer->getKey();
        }

        return 'System';
    }

    private function subjectLabel(Activity $activity): string
    {
        if ($activity->subject === null) {
            return 'N/A';
        }

        if (method_exists($activity->subject, 'getKey')) {
            return class_basename($activity->subject).'#'.$activity->subject->getKey();
        }

        return class_basename($activity->subject);
    }

    /**
     * @param array<string, mixed> $properties
     */
    private function eventSummary(Activity $activity, array $properties): string
    {
        if ($activity->event === 'order.status.updated') {
            $from = (string) data_get($properties, 'before.status', '-');
            $to = (string) data_get($properties, 'after.status', '-');

            return "Státusz: {$from} -> {$to}";
        }

        if ($activity->event === 'order.pickup.updated') {
            $date = (string) data_get($properties, 'after.pickup_date', '-');
            $slot = (string) data_get($properties, 'after.pickup_time_slot', '-');

            return "Átvétel: {$date} / {$slot}";
        }

        if ($activity->event === 'permissions.synced') {
            $created = (int) count((array) data_get($properties, 'created_permissions', []));
            $orphan = (int) count((array) data_get($properties, 'orphan_permissions', []));

            return "Szinkron eredmény: +{$created} létrehozva, {$orphan} árva.";
        }

        return (string) ($activity->description ?: ($activity->event ?? 'Audit esemeny'));
    }

    private function riskWeight(string $riskLevel): int
    {
        return match ($riskLevel) {
            'critical' => 50,
            'high' => 40,
            'medium' => 30,
            'low' => 20,
            default => 10,
        };
    }
}



