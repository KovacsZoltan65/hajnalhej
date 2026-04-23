<?php

namespace App\Services;

use App\Repositories\ConversionEventRepository;
use App\Support\ConversionEventRegistry;

class ConversionAnalyticsService
{
    public function __construct(private readonly ConversionEventRepository $repository)
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function buildDashboard(int $days): array
    {
        $funnel = $this->repository->funnelStepCounts($days);
        $ctaTop = $this->repository->topCtaClicks($days);
        $hero = $this->repository->heroVariantViews($days);
        $latest = $this->repository->latestEvents($days)->map(function ($event): array {
            return [
                'id' => $event->id,
                'event_key' => $event->event_key,
                'event_label' => ConversionEventRegistry::labels()[$event->event_key] ?? $event->event_key,
                'funnel' => $event->funnel,
                'step' => $event->step,
                'cta_id' => $event->cta_id,
                'hero_variant' => $event->hero_variant,
                'source' => $event->source,
                'occurred_at' => $event->occurred_at?->toIso8601String(),
                'user' => $event->user === null ? null : [
                    'id' => $event->user->id,
                    'name' => $event->user->name,
                    'email' => $event->user->email,
                ],
            ];
        })->all();

        return [
            'period_days' => $days,
            'summary' => [
                'total_events' => $this->repository->totalEvents($days),
                'cta_clicks' => $this->repository->countByEventKey(ConversionEventRegistry::CTA_CLICK, $days),
                'checkout_completions' => $this->repository->countByEventKey(ConversionEventRegistry::CHECKOUT_COMPLETED, $days),
                'registration_completions' => $this->repository->countByEventKey(ConversionEventRegistry::REGISTRATION_COMPLETED, $days),
            ],
            'funnel_steps' => $funnel,
            'cta_top' => $ctaTop,
            'hero_variant_views' => $hero,
            'latest_events' => $latest,
            'event_labels' => ConversionEventRegistry::labels(),
        ];
    }
}
