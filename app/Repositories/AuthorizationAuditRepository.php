<?php

namespace App\Repositories;

use App\Models\User;
use App\Services\Audit\AuthorizationAuditService;
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

        return Activity::query()
            ->where('log_name', AuthorizationAuditService::LOG_NAME)
            ->when($eventKey !== '', fn (Builder $query): Builder => $query->where('event', $eventKey))
            ->when($subjectType !== '', function (Builder $query) use ($subjectType): Builder {
                $map = [
                    'role' => Role::class,
                    'user' => User::class,
                ];

                return $query->where('subject_type', $map[$subjectType] ?? $subjectType);
            })
            ->when($search !== '', function (Builder $query) use ($search): Builder {
                return $query->where(function (Builder $nested) use ($search): void {
                    $nested->where('description', 'like', "%{$search}%")
                        ->orWhere('event', 'like', "%{$search}%")
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
            ->where('log_name', AuthorizationAuditService::LOG_NAME)
            ->with(['causer', 'subject'])
            ->findOrFail($id);
    }
}
