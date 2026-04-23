<?php

namespace App\Services;

use App\Repositories\CeoDashboardRepository;
use App\Repositories\ProfitDashboardRepository;
use Illuminate\Support\Carbon;

class CeoDashboardService
{
    public function __construct(
        private readonly CeoDashboardRepository $repository,
        private readonly ProfitDashboardRepository $profitDashboardRepository,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function buildDashboard(int $days): array
    {
        $business = $this->repository->businessKpis($days);
        $conversion = $this->repository->conversionKpis($days);
        $comparisons = $this->buildComparisons();
        $kpiInsights = $this->buildKpiInsights($business, $conversion, $comparisons);
        $securityAlerts = $this->enrichSecurityAlerts($this->repository->securityAlerts());

        return [
            'period_days' => $days,
            'summary' => [
                'revenue' => $business['revenue'],
                'estimated_profit' => $business['estimated_profit'],
                'estimated_margin_rate' => $business['estimated_margin_rate'],
                'repeat_customer_rate' => $business['repeat_customer_rate'],
                'orders_count' => $business['orders_count'],
                'ltv' => $business['ltv'],
                'checkout_conversion_rate' => $conversion['checkout_conversion_rate'],
            ],
            'kpi_insights' => $kpiInsights,
            'comparisons' => $comparisons,
            'conversion' => $conversion,
            'top_products' => $this->repository->topProducts($days, 8),
            'security_alerts' => $securityAlerts,
            'audit_highlights' => $this->repository->auditHighlights($days, 8),
            'order_profit_trend' => [
                'points' => $this->fillProfitTrendDates(
                    $days,
                    $this->profitDashboardRepository->orderProfitTrend($days),
                ),
            ],
        ];
    }

    /**
     * @return array{
     *  wow:array<string, array{current:float,previous:float,absolute:float,percent:float,direction:string}>,
     *  mom:array<string, array{current:float,previous:float,absolute:float,percent:float,direction:string}>
     * }
     */
    private function buildComparisons(): array
    {
        $now = Carbon::now()->toImmutable();

        $wowCurrentFrom = $now->subDays(6)->startOfDay();
        $wowCurrentTo = $now->endOfDay();
        $wowPreviousFrom = $wowCurrentFrom->subDays(7);
        $wowPreviousTo = $wowCurrentFrom->subSecond();

        $momCurrentFrom = $now->subDays(29)->startOfDay();
        $momCurrentTo = $now->endOfDay();
        $momPreviousFrom = $momCurrentFrom->subDays(30);
        $momPreviousTo = $momCurrentFrom->subSecond();

        $wowBusinessCurrent = $this->repository->businessKpisForRange($wowCurrentFrom, $wowCurrentTo);
        $wowBusinessPrevious = $this->repository->businessKpisForRange($wowPreviousFrom, $wowPreviousTo);
        $wowConversionCurrent = $this->repository->conversionKpisForRange($wowCurrentFrom, $wowCurrentTo);
        $wowConversionPrevious = $this->repository->conversionKpisForRange($wowPreviousFrom, $wowPreviousTo);

        $momBusinessCurrent = $this->repository->businessKpisForRange($momCurrentFrom, $momCurrentTo);
        $momBusinessPrevious = $this->repository->businessKpisForRange($momPreviousFrom, $momPreviousTo);
        $momConversionCurrent = $this->repository->conversionKpisForRange($momCurrentFrom, $momCurrentTo);
        $momConversionPrevious = $this->repository->conversionKpisForRange($momPreviousFrom, $momPreviousTo);

        return [
            'wow' => [
                'revenue' => $this->buildDelta((float) $wowBusinessCurrent['revenue'], (float) $wowBusinessPrevious['revenue']),
                'estimated_profit' => $this->buildDelta((float) $wowBusinessCurrent['estimated_profit'], (float) $wowBusinessPrevious['estimated_profit']),
                'checkout_conversion_rate' => $this->buildDelta((float) $wowConversionCurrent['checkout_conversion_rate'], (float) $wowConversionPrevious['checkout_conversion_rate']),
                'repeat_customer_rate' => $this->buildDelta((float) $wowBusinessCurrent['repeat_customer_rate'], (float) $wowBusinessPrevious['repeat_customer_rate']),
                'ltv' => $this->buildDelta((float) $wowBusinessCurrent['ltv'], (float) $wowBusinessPrevious['ltv']),
            ],
            'mom' => [
                'revenue' => $this->buildDelta((float) $momBusinessCurrent['revenue'], (float) $momBusinessPrevious['revenue']),
                'estimated_profit' => $this->buildDelta((float) $momBusinessCurrent['estimated_profit'], (float) $momBusinessPrevious['estimated_profit']),
                'checkout_conversion_rate' => $this->buildDelta((float) $momConversionCurrent['checkout_conversion_rate'], (float) $momConversionPrevious['checkout_conversion_rate']),
                'repeat_customer_rate' => $this->buildDelta((float) $momBusinessCurrent['repeat_customer_rate'], (float) $momBusinessPrevious['repeat_customer_rate']),
                'ltv' => $this->buildDelta((float) $momBusinessCurrent['ltv'], (float) $momBusinessPrevious['ltv']),
            ],
        ];
    }

    /**
     * @param array{
     *  wow:array<string, array{current:float,previous:float,absolute:float,percent:float,direction:string}>,
     *  mom:array<string, array{current:float,previous:float,absolute:float,percent:float,direction:string}>
     * } $comparisons
     * @return array<string, array{
     *  rag:string,
     *  trend:string,
     *  wow:array{current:float,previous:float,absolute:float,percent:float,direction:string},
     *  mom:array{current:float,previous:float,absolute:float,percent:float,direction:string}
     * }>
     */
    private function buildKpiInsights(array $business, array $conversion, array $comparisons): array
    {
        $metrics = [
            'revenue' => (float) $business['revenue'],
            'estimated_profit' => (float) $business['estimated_profit'],
            'checkout_conversion_rate' => (float) $conversion['checkout_conversion_rate'],
            'repeat_customer_rate' => (float) $business['repeat_customer_rate'],
            'ltv' => (float) $business['ltv'],
        ];

        $insights = [];
        foreach ($metrics as $metric => $value) {
            $wow = $comparisons['wow'][$metric];
            $mom = $comparisons['mom'][$metric];
            $rag = $this->ragFromPercent($wow['percent'], true);
            $trend = $wow['direction'] !== 'flat' ? $wow['direction'] : $mom['direction'];

            $insights[$metric] = [
                'value' => $value,
                'rag' => $rag,
                'trend' => $trend,
                'wow' => $wow,
                'mom' => $mom,
            ];
        }

        return $insights;
    }

    /**
     * @param array{critical_alerts:int,orphan_permissions:int,dangerous_permissions:int,high_risk_users:int} $alerts
     * @return array{
     *  critical_alerts:int,
     *  orphan_permissions:int,
     *  dangerous_permissions:int,
     *  high_risk_users:int,
     *  states:array{critical_alerts:string,orphan_permissions:string,dangerous_permissions:string,high_risk_users:string}
     * }
     */
    private function enrichSecurityAlerts(array $alerts): array
    {
        return [
            ...$alerts,
            'states' => [
                'critical_alerts' => $this->ragFromAbsolute((int) $alerts['critical_alerts'], 1, 3),
                'orphan_permissions' => $this->ragFromAbsolute((int) $alerts['orphan_permissions'], 1, 5),
                'dangerous_permissions' => $this->ragFromAbsolute((int) $alerts['dangerous_permissions'], 5, 12),
                'high_risk_users' => $this->ragFromAbsolute((int) $alerts['high_risk_users'], 1, 3),
            ],
        ];
    }

    /**
     * @return array{current:float,previous:float,absolute:float,percent:float,direction:string}
     */
    private function buildDelta(float $current, float $previous): array
    {
        $absolute = round($current - $previous, 2);
        $percent = $previous != 0.0
            ? round(($absolute / $previous) * 100, 2)
            : ($current != 0.0 ? 100.0 : 0.0);

        $direction = 'flat';
        if ($absolute > 0) {
            $direction = 'up';
        } elseif ($absolute < 0) {
            $direction = 'down';
        }

        return [
            'current' => round($current, 2),
            'previous' => round($previous, 2),
            'absolute' => $absolute,
            'percent' => $percent,
            'direction' => $direction,
        ];
    }

    private function ragFromPercent(float $percent, bool $higherIsBetter): string
    {
        if ($higherIsBetter) {
            if ($percent >= 5.0) {
                return 'green';
            }
            if ($percent <= -5.0) {
                return 'red';
            }

            return 'amber';
        }

        if ($percent <= -5.0) {
            return 'green';
        }
        if ($percent >= 5.0) {
            return 'red';
        }

        return 'amber';
    }

    private function ragFromAbsolute(int $value, int $amberThreshold, int $redThreshold): string
    {
        if ($value >= $redThreshold) {
            return 'red';
        }
        if ($value >= $amberThreshold) {
            return 'amber';
        }

        return 'green';
    }

    /**
     * @param array<int, array{date:string,revenue:float,estimated_cost:float,estimated_profit:float,margin_rate:float,orders_count:int}> $rows
     * @return array<int, array{date:string,revenue:float,estimated_cost:float,estimated_profit:float,margin_rate:float,orders_count:int}>
     */
    private function fillProfitTrendDates(int $days, array $rows): array
    {
        $map = [];
        foreach ($rows as $row) {
            $map[$row['date']] = $row;
        }

        $result = [];
        for ($offset = $days - 1; $offset >= 0; $offset--) {
            $date = Carbon::today()->subDays($offset)->toDateString();
            $row = $map[$date] ?? null;

            $result[] = [
                'date' => $date,
                'revenue' => (float) ($row['revenue'] ?? 0.0),
                'estimated_cost' => (float) ($row['estimated_cost'] ?? 0.0),
                'estimated_profit' => (float) ($row['estimated_profit'] ?? 0.0),
                'margin_rate' => (float) ($row['margin_rate'] ?? 0.0),
                'orders_count' => (int) ($row['orders_count'] ?? 0),
            ];
        }

        return $result;
    }
}
