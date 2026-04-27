<?php

namespace App\Http\Controllers;

use App\Services\ConversionTrackingService;
use App\Services\HeroExperimentService;
use App\Services\WeeklyMenuService;
use App\Support\ConversionEventRegistry;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PublicPageController extends Controller
{
    /**
     * @param WeeklyMenuService $weeklyMenuService
     * @param HeroExperimentService $heroExperimentService
     * @param ConversionTrackingService $conversionTrackingService
     */
    public function __construct(
        private readonly WeeklyMenuService $weeklyMenuService,
        private readonly HeroExperimentService $heroExperimentService,
        private readonly ConversionTrackingService $conversionTrackingService,
    ) {}

    /**
     * @return \Inertia\Response
     */
    public function home(Request $request): Response
    {
        $experiment = $this->heroExperimentService->resolveVariant($request);
        $variant = $experiment['variant'];

        if ($experiment['is_new_assignment']) {
            $this->conversionTrackingService->trackBackendEvent(
                eventKey: ConversionEventRegistry::HERO_ASSIGNED,
                request: $request,
                funnel: 'landing',
                step: 'assigned',
                heroVariant: $variant,
            );
        }

        $this->conversionTrackingService->trackBackendEvent(
            eventKey: ConversionEventRegistry::HERO_VIEWED,
            request: $request,
            funnel: 'landing',
            step: 'hero_view',
            heroVariant: $variant,
        );

        return Inertia::render('Home', [
            'heroExperiment' => [
                'variant' => $variant,
            ],
        ]);
    }

    /**
     * @return \Inertia\Response
     */
    public function weeklyMenu(): Response
    {
        return Inertia::render('WeeklyMenu', $this->weeklyMenuService->getPublicWeeklyMenuPayload());
    }

    public function about(): Response
    {
        return Inertia::render('About');
    }
}
