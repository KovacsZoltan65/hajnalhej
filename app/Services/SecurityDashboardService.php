<?php

namespace App\Services;

use App\Repositories\SecurityDashboardRepository;
use Carbon\CarbonImmutable;
use Spatie\Activitylog\Models\Activity;

class SecurityDashboardService
{
    public function __construct(
        private readonly SecurityDashboardRepository $repository,
    ) {
    }

    /**
     * @param array<string, mixed> $filters
     * @return array<string, mixed>
     */
    public function dashboardPayload(array $filters): array
    {
        $window = (string) ($filters['window'] ?? '7d');
        $riskLevel = (string) ($filters['risk_level'] ?? 'all');
        $logName = (string) ($filters['log_name'] ?? 'all');
        $dangerousOnly = (bool) ($filters['dangerous_only'] ?? false);
        $orphanLimit = (int) ($filters['orphan_limit'] ?? 12);
        $usersLimit = (int) ($filters['users_limit'] ?? 10);
        $eventsLimit = (int) ($filters['events_limit'] ?? 15);

        $permissionRows = $this->repository->permissionRows();
        $permissionRisk = $this->repository->permissionRiskStats($permissionRows);
        $orphans = $this->repository->orphanPermissionInsights($permissionRows, $dangerousOnly, $orphanLimit);
        $privilegedUsers = $this->repository->privilegedUsers($usersLimit, $riskLevel, $dangerousOnly);
        $recentEvents = $this->repository->recentCriticalAuditEvents(
            $this->windowStart($window),
            $logName === 'all' ? null : $logName,
            $riskLevel,
            $eventsLimit,
        );

        return [
            'summary_cards' => [
                [
                    'title' => 'Dangerous permissions',
                    'value' => (string) $permissionRisk['dangerous_permissions'],
                    'tone' => (int) $permissionRisk['dangerous_permissions'] > 0 ? 'high' : 'low',
                ],
                [
                    'title' => 'Orphan permissions',
                    'value' => (string) $permissionRisk['orphan_permissions'],
                    'tone' => (int) $permissionRisk['orphan_permissions'] > 0 ? 'high' : 'low',
                ],
                [
                    'title' => 'Privileged users',
                    'value' => (string) $privilegedUsers->count(),
                    'tone' => $privilegedUsers->contains(fn (array $user): bool => \in_array($user['risk_level'], ['critical', 'high'], true))
                        ? 'high'
                        : 'medium',
                ],
                [
                    'title' => 'Critical audit events',
                    'value' => (string) $recentEvents->count(),
                    'tone' => $recentEvents->contains(fn (array $event): bool => \in_array($event['severity'], ['critical', 'high'], true))
                        ? 'high'
                        : 'info',
                ],
            ],
            'permission_risk' => $permissionRisk,
            'orphan_permissions' => $orphans->values()->all(),
            'privileged_users' => $privilegedUsers->values()->all(),
            'recent_critical_events' => $recentEvents->values()->all(),
            'filters' => [
                'window' => $window,
                'risk_level' => $riskLevel,
                'log_name' => $logName,
                'dangerous_only' => $dangerousOnly,
                'orphan_limit' => $orphanLimit,
                'users_limit' => $usersLimit,
                'events_limit' => $eventsLimit,
            ],
            'filter_options' => [
                'windows' => [
                    ['label' => '24h', 'value' => '24h'],
                    ['label' => '7d', 'value' => '7d'],
                    ['label' => '30d', 'value' => '30d'],
                ],
                'risk_levels' => [
                    ['label' => 'All', 'value' => 'all'],
                    ['label' => 'Critical', 'value' => 'critical'],
                    ['label' => 'High', 'value' => 'high'],
                    ['label' => 'Medium', 'value' => 'medium'],
                    ['label' => 'Low', 'value' => 'low'],
                    ['label' => 'Info', 'value' => 'info'],
                ],
                'log_names' => [
                    ['label' => 'Minden domain', 'value' => 'all'],
                    ['label' => 'Jogosultságkezelés', 'value' => 'authorization'],
                    ['label' => 'Rendelések', 'value' => 'orders'],
                    ['label' => 'Felhasználói aktivitás', 'value' => 'user-activity'],
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function activityDetailPayload(Activity $activity): array
    {
        return [
            'id' => $activity->id,
            'log_name' => (string) $activity->log_name,
            'event_key' => (string) ($activity->event ?? ''),
            'description' => (string) $activity->description,
            'created_at' => $activity->created_at?->toIso8601String(),
            'causer' => $activity->causer ? [
                'type' => class_basename($activity->causer),
                'id' => method_exists($activity->causer, 'getKey') ? $activity->causer->getKey() : null,
                'label' => $activity->causer->name ?? (class_basename($activity->causer).'#'.(method_exists($activity->causer, 'getKey') ? $activity->causer->getKey() : '?')),
            ] : null,
            'subject' => $activity->subject ? [
                'type' => class_basename($activity->subject),
                'id' => method_exists($activity->subject, 'getKey') ? $activity->subject->getKey() : null,
            ] : null,
            'properties' => $activity->properties?->toArray() ?? [],
        ];
    }

    private function windowStart(string $window): CarbonImmutable
    {
        return match ($window) {
            '24h' => CarbonImmutable::now()->subDay(),
            '30d' => CarbonImmutable::now()->subDays(30),
            default => CarbonImmutable::now()->subDays(7),
        };
    }
}



