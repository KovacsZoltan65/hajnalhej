<?php

namespace App\Repositories;

use App\Models\ConversionEvent;
use App\Support\ConversionEventRegistry;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ConversionEventRepository
{
    /**
     * @param array<string, mixed> $payload
     */
    public function create(array $payload): ConversionEvent
    {
        return ConversionEvent::query()->create($payload);
    }

    /**
     * @return array<int, array{funnel:string,step:string,count:int}>
     */
    public function funnelStepCounts(int $days): array
    {
        $rows = ConversionEvent::query()
            ->select(['funnel', 'step', DB::raw('COUNT(*) as aggregate_count')])
            ->whereNotNull('funnel')
            ->whereNotNull('step')
            ->where('occurred_at', '>=', now()->subDays($days))
            ->groupBy(['funnel', 'step'])
            ->orderBy('funnel')
            ->orderBy('step')
            ->get();

        return $rows
            ->map(static fn (ConversionEvent $event): array => [
                'funnel' => (string) $event->getAttribute('funnel'),
                'step' => (string) $event->getAttribute('step'),
                'count' => (int) $event->getAttribute('aggregate_count'),
            ])
            ->all();
    }

    /**
     * @return array<int, array{cta_id:string,count:int}>
     */
    public function topCtaClicks(int $days, int $limit = 10): array
    {
        $rows = ConversionEvent::query()
            ->select(['cta_id', DB::raw('COUNT(*) as aggregate_count')])
            ->where('event_key', ConversionEventRegistry::CTA_CLICK)
            ->whereNotNull('cta_id')
            ->where('occurred_at', '>=', now()->subDays($days))
            ->groupBy('cta_id')
            ->orderByDesc('aggregate_count')
            ->limit($limit)
            ->get();

        return $rows
            ->map(static fn (ConversionEvent $event): array => [
                'cta_id' => (string) $event->getAttribute('cta_id'),
                'count' => (int) $event->getAttribute('aggregate_count'),
            ])
            ->all();
    }

    /**
     * @return array<int, array{hero_variant:string,count:int}>
     */
    public function heroVariantViews(int $days): array
    {
        $rows = ConversionEvent::query()
            ->select(['hero_variant', DB::raw('COUNT(*) as aggregate_count')])
            ->where('event_key', ConversionEventRegistry::HERO_VIEWED)
            ->whereNotNull('hero_variant')
            ->where('occurred_at', '>=', now()->subDays($days))
            ->groupBy('hero_variant')
            ->orderBy('hero_variant')
            ->get();

        return $rows
            ->map(static fn (ConversionEvent $event): array => [
                'hero_variant' => (string) $event->getAttribute('hero_variant'),
                'count' => (int) $event->getAttribute('aggregate_count'),
            ])
            ->all();
    }

    public function totalEvents(int $days): int
    {
        return ConversionEvent::query()
            ->where('occurred_at', '>=', now()->subDays($days))
            ->count();
    }

    public function countByEventKey(string $eventKey, int $days): int
    {
        return ConversionEvent::query()
            ->where('event_key', $eventKey)
            ->where('occurred_at', '>=', now()->subDays($days))
            ->count();
    }

    /**
     * @return Collection<int, ConversionEvent>
     */
    public function latestEvents(int $days, int $limit = 25): Collection
    {
        return ConversionEvent::query()
            ->with('user:id,name,email')
            ->where('occurred_at', '>=', now()->subDays($days))
            ->latest('occurred_at')
            ->limit($limit)
            ->get();
    }
}
