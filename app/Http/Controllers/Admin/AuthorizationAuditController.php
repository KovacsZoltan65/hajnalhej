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
    public function __construct(private readonly AuthorizationAuditLogService $service)
    {
    }

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
                'event_key' => (string) ($filters['event_key'] ?? ''),
                'subject_type' => (string) ($filters['subject_type'] ?? ''),
                'per_page' => (int) ($filters['per_page'] ?? 20),
            ],
            'eventOptions' => $this->service->eventKeys(),
            'eventLabels' => $this->service->eventLabels(),
            'subjectTypeLabels' => $this->service->subjectTypeLabels(),
        ]);
    }

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
