<?php

use App\Models\Ingredient;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\Cache\CacheKeyService;
use App\Services\Cache\CacheNamespaces;
use App\Services\CeoDashboardService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

beforeEach(function (): void {
    Cache::flush();
});

function ceoDashboardCacheKey(int $days): string
{
    return CacheKeyService::make(CacheNamespaces::DASHBOARD_CEO, 1, [
        'days' => $days,
        'locale' => app()->getLocale(),
        'timezone' => config('app.timezone'),
    ]);
}

function createCeoDashboardOrder(float $total = 3600): Order
{
    $customer = User::factory()->customer()->create();
    $ingredient = Ingredient::factory()->create([
        'estimated_unit_cost' => 2.5,
        'unit' => 'g',
    ]);
    $product = Product::factory()->create([
        'name' => 'Kovászos cipó',
        'price' => 1200,
        'is_active' => true,
    ]);

    $product->productIngredients()->create([
        'ingredient_id' => $ingredient->id,
        'quantity' => 100,
        'sort_order' => 1,
    ]);

    $order = Order::factory()->create([
        'user_id' => $customer->id,
        'customer_email' => $customer->email,
        'status' => Order::STATUS_COMPLETED,
        'placed_at' => now()->subDay(),
        'subtotal' => $total,
        'total' => $total,
    ]);

    $order->items()->create([
        'product_id' => $product->id,
        'product_name_snapshot' => $product->name,
        'unit_price' => 1200,
        'quantity' => 3,
        'line_total' => $total,
    ]);

    return $order;
}

it('creates a ceo dashboard cache key after the first build', function (): void {
    $service = app(CeoDashboardService::class);
    $key = ceoDashboardCacheKey(30);

    expect(Cache::has($key))->toBeFalse();

    $dashboard = $service->buildDashboard(30);

    expect(Cache::has($key))->toBeTrue()
        ->and($dashboard['period_days'])->toBe(30);
});

it('serves ceo dashboard from cache for the same payload', function (): void {
    createCeoDashboardOrder();

    $service = app(CeoDashboardService::class);
    $queries = [];

    DB::listen(function ($query) use (&$queries): void {
        if (
            str_contains($query->sql, 'orders')
            || str_contains($query->sql, 'order_items')
            || str_contains($query->sql, 'conversion_events')
            || str_contains($query->sql, 'permissions')
            || str_contains($query->sql, 'products')
        ) {
            $queries[] = $query->sql;
        }
    });

    expect($service->buildDashboard(30)['summary']['revenue'])->toBe(3600.0);
    expect($queries)->not->toBeEmpty();

    $queries = [];

    expect($service->buildDashboard(30)['summary']['revenue'])->toBe(3600.0);
    expect($queries)->toBeEmpty();
});

it('uses different ceo dashboard cache keys for different payloads', function (): void {
    $service = app(CeoDashboardService::class);
    $thirtyDaysKey = ceoDashboardCacheKey(30);
    $sevenDaysKey = ceoDashboardCacheKey(7);

    expect($thirtyDaysKey)->not->toBe($sevenDaysKey);

    $service->buildDashboard(30);

    expect(Cache::has($thirtyDaysKey))->toBeTrue()
        ->and(Cache::has($sevenDaysKey))->toBeFalse();

    $service->buildDashboard(7);

    expect(Cache::has($sevenDaysKey))->toBeTrue();
});

it('keeps the ceo dashboard response structure unchanged', function (): void {
    $dashboard = app(CeoDashboardService::class)->buildDashboard(30);

    expect($dashboard)->toHaveKeys([
        'period_days',
        'summary',
        'kpi_insights',
        'comparisons',
        'conversion',
        'top_products',
        'security_alerts',
        'audit_highlights',
        'order_profit_trend',
    ])
        ->and($dashboard['summary'])->toHaveKeys([
            'revenue',
            'estimated_profit',
            'estimated_margin_rate',
            'repeat_customer_rate',
            'orders_count',
            'ltv',
            'checkout_conversion_rate',
        ])
        ->and($dashboard['comparisons'])->toHaveKeys(['wow', 'mom'])
        ->and($dashboard['order_profit_trend'])->toHaveKey('points');
});

it('rebuilds ceo dashboard data after the cache ttl expires', function (): void {
    $service = app(CeoDashboardService::class);

    expect($service->buildDashboard(30)['summary']['revenue'])->toBe(0.0);

    createCeoDashboardOrder();

    expect($service->buildDashboard(30)['summary']['revenue'])->toBe(0.0);

    $this->travel(6)->minutes();

    expect($service->buildDashboard(30)['summary']['revenue'])->toBe(3600.0);
});
