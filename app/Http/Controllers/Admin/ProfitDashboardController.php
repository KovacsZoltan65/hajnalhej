<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProfitDashboardIndexRequest;
use App\Models\ConversionEvent;
use App\Services\ProfitDashboardService;
use App\Support\InertiaPage;
use Inertia\Response;

class ProfitDashboardController extends Controller
{
    public function __construct(
        private readonly ProfitDashboardService $service,
    ) {}

    public function index(ProfitDashboardIndexRequest $request): Response
    {
        $this->authorize('viewProfitDashboard', ConversionEvent::class);

        $days = (int) ($request->validated('days') ?? 30);

        return InertiaPage::ADMIN_PROFIT_DASHBOARD_INDEX->render([
            'filters' => [
                'days' => $days,
            ],
            'dashboard' => $this->service->buildDashboard($days),
        ]);
    }
}
