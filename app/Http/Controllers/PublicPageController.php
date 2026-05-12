<?php

namespace App\Http\Controllers;

use App\Services\ConversionTrackingService;
use App\Services\HeroExperimentService;
use App\Services\WeeklyMenuService;
use App\Support\ConversionEventRegistry;
use App\Support\InertiaPage;
use Illuminate\Http\Request;
use Inertia\Response;

class PublicPageController extends Controller
{
    public function __construct(
        private readonly WeeklyMenuService $weeklyMenuService,
        private readonly HeroExperimentService $heroExperimentService,
        private readonly ConversionTrackingService $conversionTrackingService,
    ) {}

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

        return InertiaPage::HOME->render([
            'heroExperiment' => [
                'variant' => $variant,
            ],
        ]);
    }

    public function weeklyMenu(): Response
    {
        return InertiaPage::WEEKLY_MENU->render($this->weeklyMenuService->getPublicWeeklyMenuPayload());
    }

    public function about(): Response
    {
        return InertiaPage::ABOUT->render();
    }
}
