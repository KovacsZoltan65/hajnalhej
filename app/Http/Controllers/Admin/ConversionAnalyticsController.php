<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ConversionAnalyticsIndexRequest;
use App\Models\ConversionEvent;
use App\Services\ConversionAnalyticsService;
use App\Support\InertiaPage;
use Inertia\Response;

class ConversionAnalyticsController extends Controller
{
    public function __construct(private readonly ConversionAnalyticsService $service) {}

    public function index(ConversionAnalyticsIndexRequest $request): Response
    {
        $this->authorize('viewAny', ConversionEvent::class);

        $days = (int) ($request->validated('days') ?? 30);

        return InertiaPage::ADMIN_CONVERSION_ANALYTICS_INDEX->render([
            'filters' => [
                'days' => $days,
            ],
            'analytics' => $this->service->buildDashboard($days),
        ]);
    }
}
