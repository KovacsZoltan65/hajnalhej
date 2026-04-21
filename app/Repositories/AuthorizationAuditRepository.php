<?php

namespace App\Repositories;

use App\Models\Order;
use App\Models\User;
use App\Services\Audit\AuthorizationAuditService;
use App\Services\Audit\OrderAuditService;
use App\Services\Audit\UserActivityAuditService;
use App\Support\AuditEventRegistry;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Role;

class AuthorizationAuditRepository
{
    /**
     * @param array<string, mixed> $filters
     */
    public function paginateAuthorizationLogs(array $filters): LengthAwarePaginator
    {
        $perPage = (int) ($filters['per_page'] ?? 20);
        $search = trim((string) ($filters['search'] ?? ''));
        $eventKey = trim((string) ($filters['event_key'] ?? ''));
        $subjectType = trim((string) ($filters['subject_type'] ?? ''));
        $logName = trim((string) ($filters['log_name'] ?? ''));
        $allowedLogNames = array_keys(AuditEventRegistry::logNameLabels());

        return Activity::query()
            ->whereIn('log_name', $allowedLogNames)
            ->when($logName !== '', fn (Builder $query): Builder => $query->where('log_name', $logName))
            ->when($eventKey !== '', fn (Builder $query): Builder => $query->where('event', $eventKey))
            ->when($subjectType !== '', function (Builder $query) use ($subjectType): Builder {
                $map = [
                    'role' => Role::class,
                    'user' => User::class,
                    'order' => Order::class,
                ];

                return $query->where('subject_type', $map[$subjectType] ?? $subjectType);
            })
            ->when($search !== '', function (Builder $query) use ($search): Builder {
                return $query->where(function (Builder $nested) use ($search): void {
                    $nested->where('description', 'like', "%{$search}%")
                        ->orWhere('event', 'like', "%{$search}%")
                        ->orWhere('log_name', 'like', "%{$search}%")
                        ->orWhereHas('causer', function (Builder $causerQuery) use ($search): void {
                            $causerQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        });
                });
            })
            ->with(['causer', 'subject'])
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function findAuthorizationLogById(int $id): Activity
    {
        return Activity::query()
            ->whereIn('log_name', [
                AuthorizationAuditService::LOG_NAME,
                OrderAuditService::LOG_NAME,
                UserActivityAuditService::LOG_NAME,
            ])
            ->with(['causer', 'subject'])
            ->findOrFail($id);
    }
}
