<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProfitDashboardIndexRequest;
use App\Models\ConversionEvent;
use App\Services\ProfitDashboardService;
use Inertia\Inertia;
use Inertia\Response;

class ProfitDashboardController extends Controller
{
    /**
     * @param ProfitDashboardService $service
     */
    public function __construct(
        private readonly ProfitDashboardService $service,
    ) {}

    /**
     * @param ProfitDashboardIndexRequest $request
     * @return \Inertia\Response
     */
    public function index(ProfitDashboardIndexRequest $request): Response
    {
        $this->authorize('viewProfitDashboard', ConversionEvent::class);

        $days = (int) ($request->validated('days') ?? 30);

        return Inertia::render('Admin/ProfitDashboard/Index', [
            'filters' => [
                'days' => $days,
            ],
            'dashboard' => $this->service->buildDashboard($days),
        ]);
    }
}

