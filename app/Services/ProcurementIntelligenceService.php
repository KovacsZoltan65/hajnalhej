<?php

namespace App\Services;

use App\Repositories\ProcurementIntelligenceRepository;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class ProcurementIntelligenceService
{
    private const CONSUMPTION_WINDOW_DAYS = 28;
    private const PRICE_INCREASE_ALERT_PERCENT = 10.0;
    private const STOCKOUT_WARNING_DAYS = 7.0;
    private const MINIMUM_STOCK_TARGET_DAYS = 14.0;
    private const STALE_PURCHASE_DAYS = 90;

    public function __construct(
        private readonly ProcurementIntelligenceRepository $repository,
    ) {
    }

    /**
     * @param array<string, mixed> $filters
     * @return array<string, mixed>
     */
    public function buildDashboard(array $filters): array
    {
        $priceRows = $this->repository->purchasePriceRows($filters);
        $stockRows = $this->repository->ingredientStockRows();
        $consumption28 = $this->consumptionMap(self::CONSUMPTION_WINDOW_DAYS);
        $consumption7 = $this->consumptionMap(7);
        $lastPurchaseDates = $this->lastPurchaseDateMap();

        $supplierPriceTrends = $this->supplierPriceTrends($priceRows);
        $minimumStockRecommendations = $this->filterByUrgency($this->minimumStockRecommendations($stockRows, $consumption28), (string) ($filters['urgency'] ?? ''));
        $weeklyForecast = $this->weeklyConsumptionForecast($stockRows, $consumption28, $consumption7);
        $alerts = $this->filterByAlertType($this->procurementAlerts($stockRows, $supplierPriceTrends, $consumption28, $lastPurchaseDates), (string) ($filters['alert_type'] ?? ''));

        return [
            'defaults' => [
                'consumption_window_days' => self::CONSUMPTION_WINDOW_DAYS,
                'price_increase_alert_percent' => self::PRICE_INCREASE_ALERT_PERCENT,
                'stockout_warning_days' => self::STOCKOUT_WARNING_DAYS,
                'minimum_stock_target_days' => self::MINIMUM_STOCK_TARGET_DAYS,
            ],
            'summary' => [
                'alerts_count' => count($alerts),
                'critical_minimum_stock_count' => count(array_filter($minimumStockRecommendations, static fn (array $row): bool => $row['urgency'] === 'critical')),
                'price_increase_count' => count(array_filter($alerts, static fn (array $row): bool => $row['type'] === 'price_increase')),
                'stockout_risk_count' => count(array_filter($alerts, static fn (array $row): bool => $row['type'] === 'stockout_risk')),
            ],
            'supplier_price_trends' => $supplierPriceTrends,
            'ingredient_cost_trends' => $this->ingredientCostTrends($filters),
            'recent_purchases' => $this->recentPurchases($filters),
            'minimum_stock_recommendations' => $minimumStockRecommendations,
            'weekly_consumption_forecast' => $weeklyForecast,
            'alerts' => $alerts,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function filterOptions(): array
    {
        return [
            'ingredients' => $this->repository->ingredientOptions()->values()->all(),
            'suppliers' => $this->repository->supplierOptions()->values()->all(),
            'days' => [
                ['label' => '7 nap', 'value' => 7],
                ['label' => '30 nap', 'value' => 30],
                ['label' => '90 nap', 'value' => 90],
                ['label' => '180 nap', 'value' => 180],
            ],
            'urgencies' => [
                ['label' => 'Kritikus', 'value' => 'critical'],
                ['label' => 'Magas', 'value' => 'high'],
                ['label' => 'Közepes', 'value' => 'medium'],
                ['label' => 'Alacsony', 'value' => 'low'],
            ],
            'alert_types' => [
                ['label' => 'Minimum készlet alatt', 'value' => 'low_stock'],
                ['label' => '7 napon belül elfogyhat', 'value' => 'stockout_risk'],
                ['label' => '10%+ áremelkedés', 'value' => 'price_increase'],
                ['label' => 'Nincs friss beszerzési adat', 'value' => 'stale_purchase_data'],
                ['label' => 'Hiányzó becsült költség', 'value' => 'missing_estimated_cost'],
                ['label' => 'Hiányzó minimum készlet', 'value' => 'missing_minimum_stock'],
                ['label' => 'BOM-ban használt, de nincs készleten', 'value' => 'bom_no_stock'],
            ],
        ];
    }

    /**
     * @param Collection<int, object> $priceRows
     * @return array<int, array<string, mixed>>
     */
    private function supplierPriceTrends(Collection $priceRows): array
    {
        $pairs = $priceRows->groupBy(static fn (object $row): string => $row->ingredient_id.'-'.$row->supplier_id);
        $latestByIngredient = [];

        foreach ($pairs as $rows) {
            $latest = $rows->first();
            $latestByIngredient[(int) $latest->ingredient_id][] = [
                'supplier_name' => (string) $latest->supplier_name,
                'unit_cost' => (float) $latest->unit_cost,
            ];
        }

        return $pairs
            ->map(function (Collection $rows) use ($latestByIngredient): array {
                $latest = $rows->first();
                $previous = $rows->skip(1)->first();
                $previousPrice = $previous !== null ? (float) $previous->unit_cost : null;
                $lastPrice = (float) $latest->unit_cost;
                $changeAmount = $previousPrice !== null ? $lastPrice - $previousPrice : 0.0;
                $changePercent = $previousPrice !== null && $previousPrice > 0 ? ($changeAmount / $previousPrice) * 100 : 0.0;
                $supplierExtremes = collect($latestByIngredient[(int) $latest->ingredient_id] ?? []);

                return [
                    'ingredient_id' => (int) $latest->ingredient_id,
                    'ingredient_name' => (string) $latest->ingredient_name,
                    'unit' => (string) $latest->ingredient_unit,
                    'supplier_id' => $latest->supplier_id !== null ? (int) $latest->supplier_id : null,
                    'supplier_name' => (string) $latest->supplier_name,
                    'last_unit_cost' => round($lastPrice, 4),
                    'previous_unit_cost' => $previousPrice !== null ? round($previousPrice, 4) : null,
                    'change_amount' => round($changeAmount, 4),
                    'change_percent' => round($changePercent, 2),
                    'cheapest_supplier' => $supplierExtremes->sortBy('unit_cost')->first(),
                    'most_expensive_supplier' => $supplierExtremes->sortByDesc('unit_cost')->first(),
                    'trend' => $this->priceTrendLabel($changePercent),
                ];
            })
            ->sortBy('ingredient_name')
            ->values()
            ->all();
    }

    /**
     * @param array<string, mixed> $filters
     * @return array<int, array<string, mixed>>
     */
    private function ingredientCostTrends(array $filters): array
    {
        return $this->repository->costTrendRows($filters)
            ->map(fn (object $row): array => [
                'period_date' => (string) $row->period_date,
                'ingredient_id' => (int) $row->ingredient_id,
                'ingredient_name' => (string) $row->ingredient_name,
                'unit' => (string) $row->ingredient_unit,
                'supplier_id' => $row->supplier_id !== null ? (int) $row->supplier_id : null,
                'supplier_name' => (string) $row->supplier_name,
                'average_unit_cost' => round((float) $row->average_unit_cost, 4),
                'weighted_average_cost' => round((float) $row->weighted_average_cost, 4),
                'last_unit_cost' => round((float) $row->last_unit_cost, 4),
                'purchased_quantity' => round((float) $row->purchased_quantity, 3),
                'purchases_count' => (int) $row->purchases_count,
                'last_purchase_date' => (string) $row->last_purchase_date,
            ])
            ->values()
            ->all();
    }

    /**
     * @param array<string, mixed> $filters
     * @return array<int, array<string, mixed>>
     */
    private function recentPurchases(array $filters): array
    {
        return $this->repository->recentPurchaseRows($filters)
            ->map(static fn (object $row): array => [
                'ingredient_id' => (int) $row->ingredient_id,
                'ingredient_name' => (string) $row->ingredient_name,
                'supplier_name' => (string) $row->supplier_name,
                'quantity' => round((float) $row->quantity, 3),
                'unit' => (string) $row->unit,
                'unit_cost' => round((float) $row->unit_cost, 4),
                'line_total' => round((float) $row->line_total, 2),
                'purchase_date' => (string) $row->purchase_date,
            ])
            ->values()
            ->all();
    }

    /**
     * @param Collection<int, object> $stockRows
     * @param array<int, float> $consumption28
     * @return array<int, array<string, mixed>>
     */
    private function minimumStockRecommendations(Collection $stockRows, array $consumption28): array
    {
        return $stockRows
            ->map(function (object $row) use ($consumption28): array {
                $weeklyAverage = (($consumption28[(int) $row->id] ?? 0.0) / self::CONSUMPTION_WINDOW_DAYS) * 7;
                $dailyAverage = $weeklyAverage / 7;
                $currentStock = (float) $row->current_stock;
                $minimumStock = (float) $row->minimum_stock;
                $daysOnHand = $dailyAverage > 0 ? $currentStock / $dailyAverage : null;
                $targetStock = max($minimumStock, $dailyAverage * self::MINIMUM_STOCK_TARGET_DAYS);
                $suggestedQuantity = max(0.0, $targetStock - $currentStock);

                return [
                    'ingredient_id' => (int) $row->id,
                    'ingredient_name' => (string) $row->name,
                    'unit' => (string) $row->unit,
                    'current_stock' => round($currentStock, 3),
                    'minimum_stock' => round($minimumStock, 3),
                    'weekly_average_consumption' => round($weeklyAverage, 3),
                    'days_on_hand' => $daysOnHand !== null ? round($daysOnHand, 1) : null,
                    'suggested_order_quantity' => round($suggestedQuantity, 3),
                    'urgency' => $this->stockUrgency($currentStock, $minimumStock, $daysOnHand),
                ];
            })
            ->filter(static fn (array $row): bool => $row['suggested_order_quantity'] > 0 || \in_array($row['urgency'], ['critical', 'high'], true))
            ->sortBy(fn (array $row): int => ['critical' => 1, 'high' => 2, 'medium' => 3, 'low' => 4][$row['urgency']])
            ->values()
            ->all();
    }

    /**
     * @param Collection<int, object> $stockRows
     * @param array<int, float> $consumption28
     * @param array<int, float> $consumption7
     * @return array<int, array<string, mixed>>
     */
    private function weeklyConsumptionForecast(Collection $stockRows, array $consumption28, array $consumption7): array
    {
        return $stockRows
            ->map(static function (object $row) use ($consumption28, $consumption7): array {
                $fourWeekAverage = (($consumption28[(int) $row->id] ?? 0.0) / 4);
                $dailyAverage = $fourWeekAverage / 7;
                $currentStock = (float) $row->current_stock;

                return [
                    'ingredient_id' => (int) $row->id,
                    'ingredient_name' => (string) $row->name,
                    'unit' => (string) $row->unit,
                    'last_week_consumption' => round($consumption7[(int) $row->id] ?? 0.0, 3),
                    'four_week_average' => round($fourWeekAverage, 3),
                    'next_week_forecast' => round($fourWeekAverage, 3),
                    'coverage_days' => $dailyAverage > 0 ? round($currentStock / $dailyAverage, 1) : null,
                ];
            })
            ->filter(static fn (array $row): bool => $row['last_week_consumption'] > 0 || $row['four_week_average'] > 0)
            ->sortBy('ingredient_name')
            ->values()
            ->all();
    }

    /**
     * @param Collection<int, object> $stockRows
     * @param array<int, array<string, mixed>> $priceTrends
     * @param array<int, float> $consumption28
     * @param array<int, string> $lastPurchaseDates
     * @return array<int, array<string, mixed>>
     */
    private function procurementAlerts(Collection $stockRows, array $priceTrends, array $consumption28, array $lastPurchaseDates): array
    {
        $alerts = [];

        foreach ($stockRows as $row) {
            $ingredientId = (int) $row->id;
            $currentStock = (float) $row->current_stock;
            $minimumStock = (float) $row->minimum_stock;
            $weeklyAverage = (($consumption28[$ingredientId] ?? 0.0) / self::CONSUMPTION_WINDOW_DAYS) * 7;
            $dailyAverage = $weeklyAverage / 7;
            $daysOnHand = $dailyAverage > 0 ? $currentStock / $dailyAverage : null;

            if ($currentStock <= $minimumStock) {
                $alerts[] = $this->alert('low_stock', 'high', $row, 'A készlet a minimum készlet alatt van.');
            }

            if ($daysOnHand !== null && $daysOnHand <= self::STOCKOUT_WARNING_DAYS) {
                $alerts[] = $this->alert('stockout_risk', $daysOnHand <= 2 ? 'critical' : 'high', $row, 'A várható fogyás alapján 7 napon belül elfogyhat.');
            }

            if ($row->estimated_unit_cost === null || (float) $row->estimated_unit_cost <= 0) {
                $alerts[] = $this->alert('missing_estimated_cost', 'medium', $row, 'Nincs megadva becsült egységköltség.');
            }

            if ($minimumStock <= 0) {
                $alerts[] = $this->alert('missing_minimum_stock', 'medium', $row, 'Nincs megadva minimum készlet.');
            }

            if ((int) $row->bom_usage_count > 0 && $currentStock <= 0) {
                $alerts[] = $this->alert('bom_no_stock', 'critical', $row, 'BOM-ban használt alapanyag, de nincs készleten.');
            }

            $lastPurchaseDate = $lastPurchaseDates[$ingredientId] ?? null;
            if ($lastPurchaseDate === null || Carbon::parse($lastPurchaseDate)->lt(Carbon::today()->subDays(self::STALE_PURCHASE_DAYS))) {
                $alerts[] = $this->alert('stale_purchase_data', 'low', $row, 'Nincs friss beszerzési adat az elmúlt 90 napból.');
            }
        }

        foreach ($priceTrends as $trend) {
            if ((float) $trend['change_percent'] >= self::PRICE_INCREASE_ALERT_PERCENT) {
                $alerts[] = [
                    'type' => 'price_increase',
                    'severity' => 'high',
                    'ingredient_id' => $trend['ingredient_id'],
                    'ingredient_name' => $trend['ingredient_name'],
                    'message' => 'Az utolsó beszerzési ár legalább 10%-kal emelkedett.',
                    'context' => [
                        'supplier_name' => $trend['supplier_name'],
                        'change_percent' => $trend['change_percent'],
                        'change_amount' => $trend['change_amount'],
                    ],
                ];
            }
        }

        return collect($alerts)
            ->sortBy(fn (array $row): int => ['critical' => 1, 'high' => 2, 'medium' => 3, 'low' => 4][$row['severity']] ?? 5)
            ->values()
            ->all();
    }

    /**
     * @return array<int, float>
     */
    private function consumptionMap(int $days): array
    {
        return $this->repository->consumptionRows($days)
            ->mapWithKeys(static fn (object $row): array => [(int) $row->ingredient_id => (float) $row->consumed_quantity])
            ->all();
    }

    /**
     * @return array<int, string>
     */
    private function lastPurchaseDateMap(): array
    {
        return $this->repository->lastPurchaseDates()
            ->mapWithKeys(static fn (object $row): array => [(int) $row->ingredient_id => (string) $row->last_purchase_date])
            ->all();
    }

    private function priceTrendLabel(float $changePercent): string
    {
        if ($changePercent >= 1.0) {
            return 'emelkedik';
        }

        if ($changePercent <= -1.0) {
            return 'csökken';
        }

        return 'stabil';
    }

    private function stockUrgency(float $currentStock, float $minimumStock, ?float $daysOnHand): string
    {
        if ($currentStock <= 0 || ($daysOnHand !== null && $daysOnHand <= 2)) {
            return 'critical';
        }

        if ($currentStock <= $minimumStock || ($daysOnHand !== null && $daysOnHand <= 7)) {
            return 'high';
        }

        if ($daysOnHand !== null && $daysOnHand <= 14) {
            return 'medium';
        }

        return 'low';
    }

    /**
     * @param object $row
     * @return array<string, mixed>
     */
    private function alert(string $type, string $severity, object $row, string $message): array
    {
        return [
            'type' => $type,
            'severity' => $severity,
            'ingredient_id' => (int) $row->id,
            'ingredient_name' => (string) $row->name,
            'message' => $message,
            'context' => [
                'current_stock' => round((float) $row->current_stock, 3),
                'unit' => (string) $row->unit,
                'minimum_stock' => round((float) $row->minimum_stock, 3),
            ],
        ];
    }

    /**
     * @param array<int, array<string, mixed>> $rows
     * @return array<int, array<string, mixed>>
     */
    private function filterByUrgency(array $rows, string $urgency): array
    {
        if ($urgency === '') {
            return $rows;
        }

        return array_values(array_filter($rows, static fn (array $row): bool => $row['urgency'] === $urgency));
    }

    /**
     * @param array<int, array<string, mixed>> $rows
     * @return array<int, array<string, mixed>>
     */
    private function filterByAlertType(array $rows, string $alertType): array
    {
        if ($alertType === '') {
            return $rows;
        }

        return array_values(array_filter($rows, static fn (array $row): bool => $row['type'] === $alertType));
    }
}
