<?php

namespace App\Repositories;

use App\Models\ConversionEvent;
use App\Support\ConversionEventRegistry;
use Illuminate\Database\Eloquent\Builder;
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

    public function countEvents(
        int $days,
        ?string $eventKey = null,
        ?string $source = null,
        ?string $funnel = null,
        ?string $ctaId = null,
        ?string $heroVariant = null,
    ): int {
        return $this->basePeriodQuery($days)
            ->when($eventKey !== null, fn (Builder $query): Builder => $query->where('event_key', $eventKey))
            ->when($source !== null, fn (Builder $query): Builder => $query->where('source', $source))
            ->when($funnel !== null, fn (Builder $query): Builder => $query->where('funnel', $funnel))
            ->when($ctaId !== null, fn (Builder $query): Builder => $query->where('cta_id', $ctaId))
            ->when($heroVariant !== null, fn (Builder $query): Builder => $query->where('hero_variant', $heroVariant))
            ->count();
    }

    /**
     * @return array<int, array{date:string,count:int}>
     */
    public function dailyCounts(
        int $days,
        ?string $eventKey = null,
        ?string $source = null,
        ?string $ctaId = null,
        ?string $heroVariant = null,
    ): array {
        $rows = $this->basePeriodQuery($days)
            ->selectRaw('DATE(occurred_at) as event_date, COUNT(*) as aggregate_count')
            ->when($eventKey !== null, fn (Builder $query): Builder => $query->where('event_key', $eventKey))
            ->when($source !== null, fn (Builder $query): Builder => $query->where('source', $source))
            ->when($ctaId !== null, fn (Builder $query): Builder => $query->where('cta_id', $ctaId))
            ->when($heroVariant !== null, fn (Builder $query): Builder => $query->where('hero_variant', $heroVariant))
            ->groupByRaw('DATE(occurred_at)')
            ->orderByRaw('DATE(occurred_at)')
            ->get();

        return $rows->map(static fn (object $row): array => [
            'date' => (string) $row->event_date,
            'count' => (int) $row->aggregate_count,
        ])->all();
    }

    /**
     * @param array<int, string> $eventKeys
     * @return array<int, array{event_key:string,count:int}>
     */
    public function countsByEventKeys(int $days, array $eventKeys, ?string $source = null): array
    {
        if ($eventKeys === []) {
            return [];
        }

        $rows = $this->basePeriodQuery($days)
            ->selectRaw('event_key, COUNT(*) as aggregate_count')
            ->whereIn('event_key', $eventKeys)
            ->when($source !== null, fn (Builder $query): Builder => $query->where('source', $source))
            ->groupBy('event_key')
            ->get();

        return $rows->map(static fn (object $row): array => [
            'event_key' => (string) $row->event_key,
            'count' => (int) $row->aggregate_count,
        ])->all();
    }

    /**
     * @param array<int, string> $variants
     * @return array<string, array{sessions:int,events:int}>
     */
    public function heroVariantSessionOutcome(int $days, array $variants, string $outcomeEventKey): array
    {
        $variantSessions = ConversionEvent::query()
            ->select(['session_id', 'hero_variant'])
            ->where('event_key', ConversionEventRegistry::HERO_VIEWED)
            ->whereNotNull('session_id')
            ->whereNotNull('hero_variant')
            ->whereIn('hero_variant', $variants)
            ->where('occurred_at', '>=', now()->subDays($days))
            ->orderBy('occurred_at')
            ->get();

        $sessionToVariant = [];
        foreach ($variantSessions as $row) {
            $sessionId = (string) $row->session_id;
            if (! isset($sessionToVariant[$sessionId])) {
                $sessionToVariant[$sessionId] = (string) $row->hero_variant;
            }
        }

        if ($sessionToVariant === []) {
            return collect($variants)->mapWithKeys(static fn (string $variant): array => [
                $variant => ['sessions' => 0, 'events' => 0],
            ])->all();
        }

        $eventSessionRows = ConversionEvent::query()
            ->select('session_id')
            ->where('event_key', $outcomeEventKey)
            ->whereNotNull('session_id')
            ->whereIn('session_id', array_keys($sessionToVariant))
            ->where('occurred_at', '>=', now()->subDays($days))
            ->distinct()
            ->get()
            ->pluck('session_id')
            ->map(static fn (mixed $sessionId): string => (string) $sessionId)
            ->all();

        $result = collect($variants)->mapWithKeys(static fn (string $variant): array => [
            $variant => ['sessions' => 0, 'events' => 0],
        ])->all();

        foreach ($sessionToVariant as $sessionId => $variant) {
            $result[$variant]['sessions']++;
        }

        foreach ($eventSessionRows as $sessionId) {
            $variant = $sessionToVariant[$sessionId] ?? null;
            if ($variant !== null) {
                $result[$variant]['events']++;
            }
        }

        return $result;
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

    private function basePeriodQuery(int $days): Builder
    {
        return ConversionEvent::query()
            ->where('occurred_at', '>=', now()->subDays($days));
    }
}
