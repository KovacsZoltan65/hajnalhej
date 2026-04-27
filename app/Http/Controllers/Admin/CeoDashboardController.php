<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CeoDashboardIndexRequest;
use App\Models\ConversionEvent;
use App\Services\CeoDashboardService;
use Inertia\Inertia;
use Inertia\Response;

class CeoDashboardController extends Controller
{
    /**
     * @param CeoDashboardService $service
     */
    public function __construct(
        private readonly CeoDashboardService $service,
    ) {
    }

    /**
     * @param CeoDashboardIndexRequest $request
     * @return \Inertia\Response
     */
    public function index(CeoDashboardIndexRequest $request): Response
    {
        $this->authorize('viewCeoDashboard', ConversionEvent::class);

        $days = (int) ($request->validated('days') ?? 30);

        return Inertia::render('Admin/CeoDashboard/Index', [
            'filters' => [
                'days' => $days,
            ],
            'dashboard' => $this->service->buildDashboard($days),
        ]);
    }
}

