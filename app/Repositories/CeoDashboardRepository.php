<?php

namespace App\Repositories;

use App\Models\ConversionEvent;
use App\Models\Order;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CeoDashboardRepository
{
    public function __construct(
        private readonly ProfitDashboardRepository $profitDashboardRepository,
        private readonly SecurityDashboardRepository $securityDashboardRepository,
    ) {
    }

    /**
     * @return array{revenue:float,estimated_profit:float,estimated_margin_rate:float,repeat_customer_rate:float,orders_count:int,ltv:float}
     */
    public function businessKpis(int $days): array
    {
        $now = CarbonImmutable::now();
        $from = $now->subDays(max($days - 1, 0))->startOfDay();
        $to = $now->endOfDay();

        return $this->businessKpisForRange($from, $to);
    }

    /**
     * @return array{revenue:float,estimated_profit:float,estimated_margin_rate:float,repeat_customer_rate:float,orders_count:int,ltv:float}
     */
    public function businessKpisForRange(CarbonImmutable $from, CarbonImmutable $to): array
    {
        /** @var Collection<int, Order> $orders */
        $orders = Order::query()
            ->whereNotNull('placed_at')
            ->whereBetween('placed_at', [$from, $to])
            ->where('status', '!=', Order::STATUS_CANCELLED)
            ->select(['id', 'user_id', 'customer_email', 'total'])
            ->get();

        $revenue = round((float) $orders->sum(static fn (Order $order): float => (float) $order->total), 2);
        $ordersCount = $orders->count();
        $groups = $this->groupOrdersByCustomer($orders);
        $uniqueCustomers = $groups->count();
        $repeatCustomers = $groups->filter(static fn (Collection $items): bool => $items->count() >= 2)->count();
        $estimatedProfit = $this->estimatedProfitForRange($from, $to);

        return [
            'revenue' => $revenue,
            'estimated_profit' => $estimatedProfit,
            'estimated_margin_rate' => $revenue > 0 ? round(($estimatedProfit / $revenue) * 100, 2) : 0.0,
            'repeat_customer_rate' => $uniqueCustomers > 0 ? round(($repeatCustomers / $uniqueCustomers) * 100, 2) : 0.0,
            'orders_count' => $ordersCount,
            'ltv' => $uniqueCustomers > 0 ? round($revenue / $uniqueCustomers, 2) : 0.0,
        ];
    }

    /**
     * @return array{checkout_submitted:int,checkout_completed:int,checkout_conversion_rate:float,registration_submitted:int,registration_completed:int,registration_conversion_rate:float}
     */
    public function conversionKpis(int $days): array
    {
        $now = CarbonImmutable::now();
        $from = $now->subDays(max($days - 1, 0))->startOfDay();
        $to = $now->endOfDay();

        return $this->conversionKpisForRange($from, $to);
    }

    /**
     * @return array{checkout_submitted:int,checkout_completed:int,checkout_conversion_rate:float,registration_submitted:int,registration_completed:int,registration_conversion_rate:float}
     */
    public function conversionKpisForRange(CarbonImmutable $from, CarbonImmutable $to): array
    {
        $checkoutSubmitted = $this->countConversionEvents(
            $from,
            $to,
            \App\Support\ConversionEventRegistry::CHECKOUT_SUBMITTED,
            'backend',
        );
        $checkoutCompleted = $this->countConversionEvents(
            $from,
            $to,
            \App\Support\ConversionEventRegistry::CHECKOUT_COMPLETED,
            'backend',
        );
        $registrationSubmitted = $this->countConversionEvents(
            $from,
            $to,
            \App\Support\ConversionEventRegistry::REGISTRATION_SUBMITTED,
            'frontend',
        );
        $registrationCompleted = $this->countConversionEvents(
            $from,
            $to,
            \App\Support\ConversionEventRegistry::REGISTRATION_COMPLETED,
            'backend',
        );

        return [
            'checkout_submitted' => $checkoutSubmitted,
            'checkout_completed' => $checkoutCompleted,
            'checkout_conversion_rate' => $this->rate($checkoutCompleted, $checkoutSubmitted),
            'registration_submitted' => $registrationSubmitted,
            'registration_completed' => $registrationCompleted,
            'registration_conversion_rate' => $this->rate($registrationCompleted, $registrationSubmitted),
        ];
    }

    /**
     * @return array<int, array{product_id:int,product_name:string,revenue:float,estimated_cost:float,estimated_profit:float,margin_rate:float,quantity:int,orders:int}>
     */
    public function topProducts(int $days, int $limit = 8): array
    {
        return $this->profitDashboardRepository->topProfitProducts($days, $limit);
    }

    /**
     * @return array{critical_alerts:int,orphan_permissions:int,dangerous_permissions:int,high_risk_users:int}
     */
    public function securityAlerts(): array
    {
        $permissionRows = $this->securityDashboardRepository->permissionRows();
        $risk = $this->securityDashboardRepository->permissionRiskStats($permissionRows);
        $highRiskUsers = $this->securityDashboardRepository
            ->privilegedUsers(100, 'all', false)
            ->filter(static fn (array $user): bool => \in_array((string) $user['risk_level'], ['critical', 'high'], true))
            ->count();

        return [
            'critical_alerts' => ((int) $risk['orphan_permissions']) + ((int) $risk['registry_missing_in_db']),
            'orphan_permissions' => (int) $risk['orphan_permissions'],
            'dangerous_permissions' => (int) $risk['dangerous_permissions'],
            'high_risk_users' => $highRiskUsers,
        ];
    }

    /**
     * @return array<int, array{id:int,timestamp:?string,log_name:string,event_key:string,label:string,severity:string,causer:string,subject:string,summary:string}>
     */
    public function auditHighlights(int $days, int $limit = 8): array
    {
        return $this->securityDashboardRepository
            ->recentCriticalAuditEvents(
                since: CarbonImmutable::now()->subDays($days),
                logName: null,
                riskFilter: 'all',
                limit: $limit,
            )
            ->values()
            ->all();
    }

    private function rate(int $numerator, int $denominator): float
    {
        if ($denominator <= 0) {
            return 0.0;
        }

        return round(($numerator / $denominator) * 100, 2);
    }

    private function countConversionEvents(
        CarbonImmutable $from,
        CarbonImmutable $to,
        string $eventKey,
        ?string $source = null,
    ): int {
        return ConversionEvent::query()
            ->where('event_key', $eventKey)
            ->whereBetween('occurred_at', [$from, $to])
            ->when($source !== null, fn ($query) => $query->where('source', $source))
            ->count();
    }

    private function estimatedProfitForRange(CarbonImmutable $from, CarbonImmutable $to): float
    {
        $row = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->leftJoinSub($this->productCostPerUnitQuery(), 'product_costs', function ($join): void {
                $join->on('product_costs.product_id', '=', 'order_items.product_id');
            })
            ->whereNotNull('orders.placed_at')
            ->whereBetween('orders.placed_at', [$from, $to])
            ->where('orders.status', '!=', Order::STATUS_CANCELLED)
            ->selectRaw('SUM(order_items.line_total) as revenue_total, SUM(order_items.quantity * COALESCE(product_costs.estimated_unit_cost, 0)) as estimated_cost_total')
            ->first();

        $revenue = round((float) ($row->revenue_total ?? 0), 2);
        $estimatedCost = round((float) ($row->estimated_cost_total ?? 0), 2);

        return round($revenue - $estimatedCost, 2);
    }

    private function productCostPerUnitQuery()
    {
        return DB::table('products')
            ->leftJoin('product_ingredients', 'product_ingredients.product_id', '=', 'products.id')
            ->leftJoin('ingredients', 'ingredients.id', '=', 'product_ingredients.ingredient_id')
            ->selectRaw('products.id as product_id, COALESCE(SUM(product_ingredients.quantity * COALESCE(ingredients.estimated_unit_cost, 0)), 0) as estimated_unit_cost')
            ->groupBy('products.id');
    }

    /**
     * @param Collection<int, Order> $orders
     * @return Collection<string, Collection<int, Order>>
     */
    private function groupOrdersByCustomer(Collection $orders): Collection
    {
        return $orders->groupBy(static function (Order $order): string {
            if ($order->user_id !== null) {
                return 'u:'.$order->user_id;
            }

            return 'e:'.mb_strtolower(trim((string) $order->customer_email));
        });
    }
}
