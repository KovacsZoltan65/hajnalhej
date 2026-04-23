<?php

namespace App\Services;

use App\Repositories\ConversionEventRepository;
use App\Support\ConversionEventRegistry;
use Illuminate\Support\Carbon;

class ConversionAnalyticsService
{
    public function __construct(
        private readonly ConversionEventRepository $repository,
        private readonly HeroExperimentService $heroExperimentService,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function buildDashboard(int $days): array
    {
        $funnelDefinitions = $this->funnelDefinitions();
        $funnelStats = $this->buildFunnelStats($days, $funnelDefinitions);
        $dropOff = $this->buildDropOffRanking($funnelStats);
        $heroComparison = $this->buildHeroComparison($days);
        $trend = $this->buildDailyTrend($days);

        $ctaTop = $this->repository->topCtaClicks($days);
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

        $heroViews = $this->repository->countEvents(
            days: $days,
            eventKey: ConversionEventRegistry::HERO_VIEWED,
            source: 'backend',
        );

        $registerCtaClicks = $this->repository->countEvents(
            days: $days,
            eventKey: ConversionEventRegistry::CTA_CLICK,
            source: 'frontend',
            ctaId: 'hero.register_primary',
        ) + $this->repository->countEvents(
            days: $days,
            eventKey: ConversionEventRegistry::CTA_CLICK,
            source: 'frontend',
            ctaId: 'final.register',
        );

        $checkoutCompleted = $this->repository->countEvents(
            days: $days,
            eventKey: ConversionEventRegistry::CHECKOUT_COMPLETED,
            source: 'backend',
        );

        $registrationCompleted = $this->repository->countEvents(
            days: $days,
            eventKey: ConversionEventRegistry::REGISTRATION_COMPLETED,
            source: 'backend',
        );

        return [
            'period_days' => $days,
            'summary' => [
                'total_events' => $this->repository->totalEvents($days),
                'cta_clicks' => $this->repository->countEvents(
                    days: $days,
                    eventKey: ConversionEventRegistry::CTA_CLICK,
                    source: 'frontend',
                ),
                'checkout_completions' => $checkoutCompleted,
                'registration_completions' => $registrationCompleted,
            ],
            'conversion_rates' => [
                [
                    'id' => 'hero_to_register',
                    'label' => 'Hero -> regisztráció kattintás',
                    'numerator' => $registerCtaClicks,
                    'denominator' => $heroViews,
                    'rate' => $this->rate($registerCtaClicks, $heroViews),
                ],
                [
                    'id' => 'checkout_submit_to_complete',
                    'label' => 'Checkout submit -> completed',
                    'numerator' => $checkoutCompleted,
                    'denominator' => $this->repository->countEvents(
                        days: $days,
                        eventKey: ConversionEventRegistry::CHECKOUT_SUBMITTED,
                        source: 'backend',
                    ),
                    'rate' => $this->rate(
                        $checkoutCompleted,
                        $this->repository->countEvents(
                            days: $days,
                            eventKey: ConversionEventRegistry::CHECKOUT_SUBMITTED,
                            source: 'backend',
                        ),
                    ),
                ],
                [
                    'id' => 'registration_view_to_complete',
                    'label' => 'Regisztráció view -> completed',
                    'numerator' => $registrationCompleted,
                    'denominator' => $this->repository->countEvents(
                        days: $days,
                        eventKey: ConversionEventRegistry::REGISTRATION_VIEWED,
                        source: 'backend',
                    ),
                    'rate' => $this->rate(
                        $registrationCompleted,
                        $this->repository->countEvents(
                            days: $days,
                            eventKey: ConversionEventRegistry::REGISTRATION_VIEWED,
                            source: 'backend',
                        ),
                    ),
                ],
            ],
            'trend' => $trend,
            'funnel_stats' => $funnelStats,
            'drop_off_top' => $dropOff,
            'hero_comparison' => $heroComparison,
            'cta_top' => $ctaTop,
            'latest_events' => $latest,
            'event_labels' => ConversionEventRegistry::labels(),
        ];
    }

    /**
     * @return array<int, array{id:string,label:string,steps:array<int,array<string,mixed>>}>
     */
    private function funnelDefinitions(): array
    {
        return [
            [
                'id' => 'landing',
                'label' => 'Landing funnel',
                'steps' => [
                    ['key' => ConversionEventRegistry::HERO_VIEWED, 'label' => 'Hero megtekintés', 'source' => 'backend'],
                    ['key' => ConversionEventRegistry::CTA_CLICK, 'label' => 'Heti menü CTA', 'source' => 'frontend', 'cta_id' => 'hero.weekly_menu_primary'],
                    ['key' => ConversionEventRegistry::CTA_CLICK, 'label' => 'Regisztráció CTA', 'source' => 'frontend', 'cta_id' => 'hero.register_primary'],
                ],
            ],
            [
                'id' => 'cart',
                'label' => 'Kosár funnel',
                'steps' => [
                    ['key' => ConversionEventRegistry::CART_VIEWED, 'label' => 'Kosár oldal', 'source' => 'backend'],
                    ['key' => ConversionEventRegistry::CART_ITEM_ADDED, 'label' => 'Kosárba helyezés', 'source' => 'backend'],
                    ['key' => ConversionEventRegistry::CTA_CLICK, 'label' => 'Tovább a pénztárhoz CTA', 'source' => 'frontend', 'cta_id' => 'cart.proceed_to_checkout'],
                    ['key' => ConversionEventRegistry::CHECKOUT_VIEWED, 'label' => 'Checkout oldal', 'source' => 'backend'],
                    ['key' => ConversionEventRegistry::CHECKOUT_SUBMITTED, 'label' => 'Checkout beküldés', 'source' => 'backend'],
                    ['key' => ConversionEventRegistry::CHECKOUT_COMPLETED, 'label' => 'Checkout completed', 'source' => 'backend'],
                ],
            ],
            [
                'id' => 'registration',
                'label' => 'Regisztráció funnel',
                'steps' => [
                    ['key' => ConversionEventRegistry::REGISTRATION_VIEWED, 'label' => 'Regisztráció megtekintés', 'source' => 'backend'],
                    ['key' => ConversionEventRegistry::REGISTRATION_SUBMITTED, 'label' => 'Regisztráció beküldés', 'source' => 'frontend'],
                    ['key' => ConversionEventRegistry::REGISTRATION_COMPLETED, 'label' => 'Regisztráció completed', 'source' => 'backend'],
                ],
            ],
        ];
    }

    /**
     * @param array<int, array{id:string,label:string,steps:array<int,array<string,mixed>>}> $definitions
     * @return array<int, array{id:string,label:string,steps:array<int,array<string,mixed>>}>
     */
    private function buildFunnelStats(int $days, array $definitions): array
    {
        return array_map(function (array $funnel) use ($days): array {
            $steps = [];
            $entryCount = null;
            $prevCount = null;

            foreach ($funnel['steps'] as $step) {
                $count = $this->repository->countEvents(
                    days: $days,
                    eventKey: (string) $step['key'],
                    source: $step['source'] ?? null,
                    ctaId: $step['cta_id'] ?? null,
                );

                if ($entryCount === null) {
                    $entryCount = $count;
                }

                $steps[] = [
                    'event_key' => $step['key'],
                    'label' => $step['label'],
                    'count' => $count,
                    'conversion_from_entry' => $this->rate($count, $entryCount),
                    'conversion_from_previous' => $prevCount === null ? 100.0 : $this->rate($count, $prevCount),
                    'drop_from_previous' => $prevCount === null ? 0 : max(0, $prevCount - $count),
                ];

                $prevCount = $count;
            }

            return [
                'id' => $funnel['id'],
                'label' => $funnel['label'],
                'steps' => $steps,
            ];
        }, $definitions);
    }

    /**
     * @param array<int, array{id:string,label:string,steps:array<int,array<string,mixed>>}> $funnelStats
     * @return array<int, array<string, mixed>>
     */
    private function buildDropOffRanking(array $funnelStats): array
    {
        $rows = [];

        foreach ($funnelStats as $funnel) {
            /** @var array<int, array<string, mixed>> $steps */
            $steps = $funnel['steps'];
            for ($i = 1; $i < count($steps); $i++) {
                $from = $steps[$i - 1];
                $to = $steps[$i];
                $fromCount = (int) $from['count'];
                $toCount = (int) $to['count'];
                $drop = max(0, $fromCount - $toCount);

                $rows[] = [
                    'funnel' => $funnel['label'],
                    'from' => $from['label'],
                    'to' => $to['label'],
                    'from_count' => $fromCount,
                    'to_count' => $toCount,
                    'drop_count' => $drop,
                    'drop_rate' => $fromCount === 0 ? 0.0 : round(($drop / $fromCount) * 100, 2),
                ];
            }
        }

        usort($rows, static fn (array $a, array $b): int => $b['drop_count'] <=> $a['drop_count']);

        return array_slice($rows, 0, 10);
    }

    /**
     * @return array<string, mixed>
     */
    private function buildDailyTrend(int $days): array
    {
        $dateKeys = $this->dateRangeKeys($days);
        $seriesMap = [
            'cta_clicks' => $this->toDateMap($this->repository->dailyCounts($days, ConversionEventRegistry::CTA_CLICK, 'frontend')),
            'cart_adds' => $this->toDateMap($this->repository->dailyCounts($days, ConversionEventRegistry::CART_ITEM_ADDED, 'backend')),
            'checkout_submitted' => $this->toDateMap($this->repository->dailyCounts($days, ConversionEventRegistry::CHECKOUT_SUBMITTED, 'backend')),
            'checkout_completed' => $this->toDateMap($this->repository->dailyCounts($days, ConversionEventRegistry::CHECKOUT_COMPLETED, 'backend')),
            'registration_completed' => $this->toDateMap($this->repository->dailyCounts($days, ConversionEventRegistry::REGISTRATION_COMPLETED, 'backend')),
        ];

        $points = [];
        foreach ($dateKeys as $date) {
            $submit = (int) ($seriesMap['checkout_submitted'][$date] ?? 0);
            $completed = (int) ($seriesMap['checkout_completed'][$date] ?? 0);
            $points[] = [
                'date' => $date,
                'cta_clicks' => (int) ($seriesMap['cta_clicks'][$date] ?? 0),
                'cart_adds' => (int) ($seriesMap['cart_adds'][$date] ?? 0),
                'checkout_submitted' => $submit,
                'checkout_completed' => $completed,
                'registration_completed' => (int) ($seriesMap['registration_completed'][$date] ?? 0),
                'checkout_submit_to_complete_rate' => $this->rate($completed, $submit),
            ];
        }

        return [
            'points' => $points,
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildHeroComparison(int $days): array
    {
        $variants = $this->heroExperimentService->variants();
        $checkoutOutcome = $this->repository->heroVariantSessionOutcome($days, $variants, ConversionEventRegistry::CHECKOUT_COMPLETED);
        $registrationOutcome = $this->repository->heroVariantSessionOutcome($days, $variants, ConversionEventRegistry::REGISTRATION_COMPLETED);

        $rows = [];
        $totalViews = 0;

        foreach ($variants as $variant) {
            $views = $this->repository->countEvents(
                days: $days,
                eventKey: ConversionEventRegistry::HERO_VIEWED,
                source: 'backend',
                heroVariant: $variant,
            );

            $ctaClicks = $this->repository->countEvents(
                days: $days,
                eventKey: ConversionEventRegistry::CTA_CLICK,
                source: 'frontend',
                heroVariant: $variant,
            );

            $registerClicks = $this->repository->countEvents(
                days: $days,
                eventKey: ConversionEventRegistry::CTA_CLICK,
                source: 'frontend',
                ctaId: 'hero.register_primary',
                heroVariant: $variant,
            ) + $this->repository->countEvents(
                days: $days,
                eventKey: ConversionEventRegistry::CTA_CLICK,
                source: 'frontend',
                ctaId: 'final.register',
                heroVariant: $variant,
            );

            $checkoutSessions = (int) ($checkoutOutcome[$variant]['events'] ?? 0);
            $registrationSessions = (int) ($registrationOutcome[$variant]['events'] ?? 0);

            $rows[] = [
                'variant' => $variant,
                'views' => $views,
                'cta_clicks' => $ctaClicks,
                'register_clicks' => $registerClicks,
                'checkout_sessions' => $checkoutSessions,
                'registration_sessions' => $registrationSessions,
                'cta_ctr' => $this->rate($ctaClicks, $views),
                'register_ctr' => $this->rate($registerClicks, $views),
                'checkout_session_rate' => $this->rate($checkoutSessions, $views),
                'registration_session_rate' => $this->rate($registrationSessions, $views),
            ];

            $totalViews += $views;
        }

        return array_map(function (array $row) use ($totalViews): array {
            $row['view_share'] = $this->rate((int) $row['views'], $totalViews);
            return $row;
        }, $rows);
    }

    /**
     * @param array<int, array{date:string,count:int}> $rows
     * @return array<string, int>
     */
    private function toDateMap(array $rows): array
    {
        $map = [];
        foreach ($rows as $row) {
            $map[$row['date']] = $row['count'];
        }

        return $map;
    }

    /**
     * @return array<int, string>
     */
    private function dateRangeKeys(int $days): array
    {
        $keys = [];
        for ($offset = $days - 1; $offset >= 0; $offset--) {
            $keys[] = Carbon::today()->subDays($offset)->toDateString();
        }

        return $keys;
    }

    private function rate(int $numerator, int $denominator): float
    {
        if ($denominator <= 0) {
            return 0.0;
        }

        return round(($numerator / $denominator) * 100, 2);
    }
}

