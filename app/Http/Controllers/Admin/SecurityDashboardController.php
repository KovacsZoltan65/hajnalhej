<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SecurityDashboardRequest;
use App\Services\SecurityDashboardService;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Activitylog\Models\Activity;

class SecurityDashboardController extends Controller
{
    public function __construct(
        private readonly SecurityDashboardService $service,
    ) {
    }

    public function index(SecurityDashboardRequest $request): Response
    {
        $this->authorize('viewAny', Activity::class);

        return Inertia::render('Admin/SecurityDashboard/Index', [
            ...$this->service->dashboardPayload($request->validated()),
            'links' => [
                'permissions' => '/admin/permissions',
                'roles' => '/admin/roles',
                'user_roles' => '/admin/user-roles',
            ],
        ]);
    }

    public function showEvent(Activity $activity): Response
    {
        $this->authorize('view', Activity::class);

        return Inertia::render('Admin/SecurityDashboard/Event', [
            'event' => $this->service->activityDetailPayload($activity),
        ]);
    }
}

