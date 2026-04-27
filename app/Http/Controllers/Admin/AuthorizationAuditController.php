<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AuditLogIndexRequest;
use App\Services\Audit\AuthorizationAuditLogService;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Activitylog\Models\Activity;

class AuthorizationAuditController extends Controller
{
    /**
     * @param AuthorizationAuditLogService $service
     */
    public function __construct(private readonly AuthorizationAuditLogService $service)
    {
    }

    /**
     * @param AuditLogIndexRequest $request
     * @return \Inertia\Response
     */
    public function index(AuditLogIndexRequest $request): Response
    {
        $this->authorize('viewAny', Activity::class);

        $filters = $request->validated();

        $logs = $this->service
            ->paginateForAdmin($filters)
            ->through(fn (Activity $activity): array => $this->service->buildListPayload($activity));

        return Inertia::render('Admin/AuditLogs/Index', [
            'logs' => $logs,
            'filters' => [
                'search' => (string) ($filters['search'] ?? ''),
                'log_name' => (string) ($filters['log_name'] ?? ''),
                'event_key' => (string) ($filters['event_key'] ?? ''),
                'subject_type' => (string) ($filters['subject_type'] ?? ''),
                'per_page' => (int) ($filters['per_page'] ?? 20),
            ],
            'logNameLabels' => $this->service->logNameLabels(),
            'eventOptions' => $this->service->eventKeys(),
            'eventLabels' => $this->service->eventLabels(),
            'subjectTypeLabels' => $this->service->subjectTypeLabels(),
        ]);
    }

    /**
     * @param Activity $activity
     * @return \Inertia\Response
     */
    public function show(Activity $activity): Response
    {
        $this->authorize('view', $activity);

        $activity = $this->service->findById((int) $activity->id);

        return Inertia::render('Admin/AuditLogs/Show', [
            'log' => $this->service->buildDetailPayload($activity),
            'eventLabels' => $this->service->eventLabels(),
        ]);
    }
}
