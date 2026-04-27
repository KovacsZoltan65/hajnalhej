<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ConversionAnalyticsIndexRequest;
use App\Models\ConversionEvent;
use App\Services\ConversionAnalyticsService;
use Inertia\Inertia;
use Inertia\Response;

class ConversionAnalyticsController extends Controller
{
    /**
     * @param ConversionAnalyticsService $service
     */
    public function __construct(private readonly ConversionAnalyticsService $service)
    {
    }

    /**
     * @param ConversionAnalyticsIndexRequest $request
     * @return \Inertia\Response
     */
    public function index(ConversionAnalyticsIndexRequest $request): Response
    {
        $this->authorize('viewAny', ConversionEvent::class);

        $days = (int) ($request->validated('days') ?? 30);

        return Inertia::render('Admin/ConversionAnalytics/Index', [
            'filters' => [
                'days' => $days,
            ],
            'analytics' => $this->service->buildDashboard($days),
        ]);
    }
}

