<?php

use App\Models\ConversionEvent;
use App\Services\Cache\CacheKeyService;
use App\Services\Cache\CacheNamespaces;
use App\Services\ConversionAnalyticsService;
use App\Support\ConversionEventRegistry;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

beforeEach(function (): void {
    Cache::flush();
});

function conversionAnalyticsCacheKey(int $days): string
{
    return CacheKeyService::make(CacheNamespaces::DASHBOARD_CONVERSION_ANALYTICS, 1, [
        'days' => $days,
        'locale' => app()->getLocale(),
        'timezone' => config('app.timezone'),
    ]);
}

function createConversionAnalyticsEvent(): ConversionEvent
{
    return ConversionEvent::query()->create([
        'event_key' => ConversionEventRegistry::HERO_VIEWED,
        'funnel' => 'landing',
        'step' => 'hero_view',
        'hero_variant' => 'artisan_story',
        'source' => 'backend',
        'occurred_at' => now(),
    ]);
}

it('creates a conversion analytics dashboard cache key after the first build', function (): void {
    $service = app(ConversionAnalyticsService::class);
    $key = conversionAnalyticsCacheKey(30);

    expect(Cache::has($key))->toBeFalse();

    $dashboard = $service->buildDashboard(30);

    expect(Cache::has($key))->toBeTrue()
        ->and($dashboard['period_days'])->toBe(30);
});

it('serves conversion analytics dashboard from cache for the same days value', function (): void {
    createConversionAnalyticsEvent();

    $service = app(ConversionAnalyticsService::class);
    $queries = [];

    DB::listen(function ($query) use (&$queries): void {
        if (str_contains($query->sql, 'conversion_events') || str_contains($query->sql, 'orders')) {
            $queries[] = $query->sql;
        }
    });

    expect($service->buildDashboard(30)['summary']['total_events'])->toBe(1);
    expect($queries)->not->toBeEmpty();

    $queries = [];

    expect($service->buildDashboard(30)['summary']['total_events'])->toBe(1);
    expect($queries)->toBeEmpty();
});

it('uses different conversion analytics cache keys for different days values', function (): void {
    $service = app(ConversionAnalyticsService::class);
    $thirtyDaysKey = conversionAnalyticsCacheKey(30);
    $sevenDaysKey = conversionAnalyticsCacheKey(7);

    expect($thirtyDaysKey)->not->toBe($sevenDaysKey);

    $service->buildDashboard(30);

    expect(Cache::has($thirtyDaysKey))->toBeTrue()
        ->and(Cache::has($sevenDaysKey))->toBeFalse();

    $service->buildDashboard(7);

    expect(Cache::has($sevenDaysKey))->toBeTrue();
});

it('keeps the conversion analytics dashboard response structure unchanged', function (): void {
    $dashboard = app(ConversionAnalyticsService::class)->buildDashboard(30);

    expect($dashboard)->toHaveKeys([
        'period_days',
        'summary',
        'conversion_rates',
        'trend',
        'commerce',
        'commerce_trend',
        'top_product_revenue',
        'funnel_stats',
        'drop_off_top',
        'hero_comparison',
        'cta_top',
        'latest_events',
        'event_labels',
    ])
        ->and($dashboard['summary'])->toHaveKeys([
            'total_events',
            'cta_clicks',
            'checkout_completions',
            'registration_completions',
            'revenue_total',
            'average_cart_value',
        ])
        ->and($dashboard['trend'])->toHaveKey('points')
        ->and($dashboard['commerce_trend'])->toHaveKey('points');
});

it('rebuilds conversion analytics dashboard data after the cache ttl expires', function (): void {
    $service = app(ConversionAnalyticsService::class);

    expect($service->buildDashboard(30)['summary']['total_events'])->toBe(0);

    createConversionAnalyticsEvent();

    expect($service->buildDashboard(30)['summary']['total_events'])->toBe(0);

    $this->travel(6)->minutes();

    expect($service->buildDashboard(30)['summary']['total_events'])->toBe(1);
});
