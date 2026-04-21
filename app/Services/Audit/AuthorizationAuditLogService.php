<?php

namespace App\Services\Audit;

use App\Models\Order;
use App\Repositories\AuthorizationAuditRepository;
use App\Support\AuditEventRegistry;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Role;
use App\Models\User;

class AuthorizationAuditLogService
{
    public function __construct(private readonly AuthorizationAuditRepository $repository)
    {
    }

    /**
     * @param array<string, mixed> $filters
     */
    public function paginateForAdmin(array $filters): LengthAwarePaginator
    {
        return $this->repository->paginateAuthorizationLogs($filters);
    }

    public function findById(int $id): Activity
    {
        return $this->repository->findAuthorizationLogById($id);
    }

    /**
     * @return array<int, string>
     */
    public function eventKeys(): array
    {
        return AuditEventRegistry::eventKeys();
    }

    /**
     * @return array<string, string>
     */
    public function eventLabels(): array
    {
        return AuditEventRegistry::eventLabels();
    }

    /**
     * @return array<string, string>
     */
    public function logNameLabels(): array
    {
        return AuditEventRegistry::logNameLabels();
    }

    /**
     * @return array<string, string>
     */
    public function subjectTypeLabels(): array
    {
        return [
            'role' => 'Role',
            'user' => 'User',
            'order' => 'Order',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function buildListPayload(Activity $activity): array
    {
        return [
            'id' => $activity->id,
            'event' => $activity->event,
            'description' => $activity->description,
            'log_name' => $activity->log_name,
            'created_at' => $activity->created_at?->toDateTimeString(),
            'causer' => [
                'id' => $activity->causer?->id,
                'name' => $activity->causer?->name,
                'email' => $activity->causer?->email,
            ],
            'subject' => $this->subjectPayload($activity),
            'event_key' => (string) Arr::get($activity->properties?->toArray() ?? [], 'event_key', $activity->event),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function buildDetailPayload(Activity $activity): array
    {
        $properties = $activity->properties?->toArray() ?? [];

        return [
            'id' => $activity->id,
            'event' => $activity->event,
            'description' => $activity->description,
            'log_name' => $activity->log_name,
            'created_at' => $activity->created_at?->toDateTimeString(),
            'causer' => [
                'id' => $activity->causer?->id,
                'name' => $activity->causer?->name,
                'email' => $activity->causer?->email,
            ],
            'subject' => $this->subjectPayload($activity),
            'properties' => [
                'event_key' => Arr::get($properties, 'event_key'),
                'before' => Arr::get($properties, 'before', []),
                'after' => Arr::get($properties, 'after', []),
                'context' => Arr::get($properties, 'context', []),
                'added_permissions' => Arr::get($properties, 'added_permissions', []),
                'removed_permissions' => Arr::get($properties, 'removed_permissions', []),
                'added_roles' => Arr::get($properties, 'added_roles', []),
                'removed_roles' => Arr::get($properties, 'removed_roles', []),
                'status_transition' => Arr::get($properties, 'status_transition'),
                'pickup_transition' => Arr::get($properties, 'pickup_transition'),
                'role' => Arr::get($properties, 'role'),
                'order' => Arr::get($properties, 'order'),
                'customer_snapshot' => Arr::get($properties, 'customer_snapshot'),
                'totals_snapshot' => Arr::get($properties, 'totals_snapshot'),
                'items_summary' => Arr::get($properties, 'items_summary', []),
                'pickup_snapshot' => Arr::get($properties, 'pickup_snapshot'),
                'target_user' => Arr::get($properties, 'target_user'),
                'note_summary' => Arr::get($properties, 'note_summary'),
                'blocked_reason' => Arr::get($properties, 'blocked_reason'),
                'actor_snapshot' => Arr::get($properties, 'actor_snapshot'),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function subjectPayload(Activity $activity): array
    {
        $subject = $activity->subject;
        $subjectType = match ($activity->subject_type) {
            Role::class => 'role',
            User::class => 'user',
            Order::class => 'order',
            default => 'unknown',
        };

        $subjectLabel = match ($subjectType) {
            'order' => (string) ($subject?->order_number ?? 'Order'),
            'user' => (string) ($subject?->email ?? $subject?->name ?? 'User'),
            default => (string) ($subject?->name ?? $subject?->email ?? ($activity->subject_type ? class_basename($activity->subject_type) : 'n/a')),
        };

        return [
            'id' => $activity->subject_id,
            'type' => $subjectType,
            'label' => $subjectLabel,
            'class' => $activity->subject_type,
        ];
    }
}
