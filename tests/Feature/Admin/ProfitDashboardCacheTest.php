<?php

use App\Models\Ingredient;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\Cache\CacheKeyService;
use App\Services\Cache\CacheNamespaces;
use App\Services\ProfitDashboardService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

beforeEach(function (): void {
    Cache::flush();
});

function profitDashboardCacheKey(int $days): string
{
    return CacheKeyService::make(CacheNamespaces::DASHBOARD_PROFIT, 1, [
        'days' => $days,
        'locale' => app()->getLocale(),
        'timezone' => config('app.timezone'),
    ]);
}

function createProfitDashboardOrder(float $total = 3600): Order
{
    $customer = User::factory()->customer()->create();
    $ingredient = Ingredient::factory()->create([
        'estimated_unit_cost' => 2.5,
        'unit' => 'g',
    ]);
    $product = Product::factory()->create([
        'name' => 'Vajas kalács',
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

it('creates a profit dashboard cache key after the first build', function (): void {
    $service = app(ProfitDashboardService::class);
    $key = profitDashboardCacheKey(30);

    expect(Cache::has($key))->toBeFalse();

    $dashboard = $service->buildDashboard(30);

    expect(Cache::has($key))->toBeTrue()
        ->and($dashboard['period_days'])->toBe(30);
});

it('serves profit dashboard from cache for the same payload', function (): void {
    createProfitDashboardOrder();

    $service = app(ProfitDashboardService::class);
    $queries = [];

    DB::listen(function ($query) use (&$queries): void {
        if (
            str_contains($query->sql, 'products')
            || str_contains($query->sql, 'orders')
            || str_contains($query->sql, 'inventory_movements')
        ) {
            $queries[] = $query->sql;
        }
    });

    expect($service->buildDashboard(30)['summary']['period_revenue'])->toBe(3600.0);
    expect($queries)->not->toBeEmpty();

    $queries = [];

    expect($service->buildDashboard(30)['summary']['period_revenue'])->toBe(3600.0);
    expect($queries)->toBeEmpty();
});

it('uses different profit dashboard cache keys for different payloads', function (): void {
    $service = app(ProfitDashboardService::class);
    $thirtyDaysKey = profitDashboardCacheKey(30);
    $sevenDaysKey = profitDashboardCacheKey(7);

    expect($thirtyDaysKey)->not->toBe($sevenDaysKey);

    $service->buildDashboard(30);

    expect(Cache::has($thirtyDaysKey))->toBeTrue()
        ->and(Cache::has($sevenDaysKey))->toBeFalse();

    $service->buildDashboard(7);

    expect(Cache::has($sevenDaysKey))->toBeTrue();
});

it('keeps the profit dashboard response structure unchanged', function (): void {
    $dashboard = app(ProfitDashboardService::class)->buildDashboard(30);

    expect($dashboard)->toHaveKeys([
        'period_days',
        'summary',
        'product_margins',
        'top_profit_products',
        'order_profit_trend',
    ])
        ->and($dashboard['summary'])->toHaveKeys([
            'estimated_cost_total',
            'catalog_value_total',
            'potential_margin_total',
            'products_with_recipe',
            'period_revenue',
            'period_estimated_cost',
            'period_estimated_profit',
            'period_actual_material_cost',
            'period_waste_cost',
            'period_gross_profit',
            'period_margin_rate',
            'estimated_vs_actual_delta',
        ])
        ->and($dashboard['order_profit_trend'])->toHaveKey('points');
});

it('rebuilds profit dashboard data after the cache ttl expires', function (): void {
    $service = app(ProfitDashboardService::class);

    expect($service->buildDashboard(30)['summary']['period_revenue'])->toBe(0.0);

    createProfitDashboardOrder();

    expect($service->buildDashboard(30)['summary']['period_revenue'])->toBe(0.0);

    $this->travel(6)->minutes();

    expect($service->buildDashboard(30)['summary']['period_revenue'])->toBe(3600.0);
});
