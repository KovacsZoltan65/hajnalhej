<?php

namespace App\Services;

use App\Repositories\ProfitDashboardRepository;
use Illuminate\Support\Carbon;

class ProfitDashboardService
{
    public function __construct(
        private readonly ProfitDashboardRepository $repository,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function buildDashboard(int $days): array
    {
        $catalog = $this->repository->catalogSummary();
        $period = $this->repository->periodSummary($days);

        return [
            'period_days' => $days,
            'summary' => [
                'estimated_cost_total' => $catalog['estimated_cost_total'],
                'catalog_value_total' => $catalog['catalog_value_total'],
                'potential_margin_total' => $catalog['potential_margin_total'],
                'products_with_recipe' => $catalog['products_with_recipe'],
                'period_revenue' => $period['revenue'],
                'period_estimated_cost' => $period['estimated_cost'],
                'period_estimated_profit' => $period['estimated_profit'],
                'period_margin_rate' => $period['margin_rate'],
            ],
            'product_margins' => $this->repository->productMargins(30),
            'top_profit_products' => $this->repository->topProfitProducts($days, 10),
            'order_profit_trend' => [
                'points' => $this->fillTrendDates($days, $this->repository->orderProfitTrend($days)),
            ],
        ];
    }

    /**
     * @param array<int, array{date:string,revenue:float,estimated_cost:float,estimated_profit:float,margin_rate:float,orders_count:int}> $rows
     * @return array<int, array{date:string,revenue:float,estimated_cost:float,estimated_profit:float,margin_rate:float,orders_count:int}>
     */
    private function fillTrendDates(int $days, array $rows): array
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

