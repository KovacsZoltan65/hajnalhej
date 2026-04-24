<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProcurementIntelligenceIndexRequest;
use App\Services\ProcurementIntelligenceService;
use Inertia\Inertia;
use Inertia\Response;

class ProcurementIntelligenceController extends Controller
{
    public function __construct(
        private readonly ProcurementIntelligenceService $service,
    ) {
    }

    /**
     * @param ProcurementIntelligenceIndexRequest $request
     * @return \Inertia\Response
     */
    public function index(ProcurementIntelligenceIndexRequest $request): Response
    {
        $this->authorize('viewProcurementIntelligence');

        $filters = $request->validated();
        $days = (int) ($filters['days'] ?? 30);

        return Inertia::render('Admin/ProcurementIntelligence/Index', [
            'filters' => [
                'days' => $days,
                'ingredient_id' => $filters['ingredient_id'] ?? null,
                'supplier_id' => $filters['supplier_id'] ?? null,
                'urgency' => $filters['urgency'] ?? '',
                'alert_type' => $filters['alert_type'] ?? '',
            ],
            'dashboard' => $this->service->buildDashboard($filters + ['days' => $days]),
            'filter_options' => $this->service->filterOptions(),
        ]);
    }
}
