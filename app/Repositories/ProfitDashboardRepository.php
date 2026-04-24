<?php

namespace App\Repositories;

use App\Models\Order;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\DB;

class ProfitDashboardRepository
{
    /**
     * @return array{estimated_cost_total:float,catalog_value_total:float,potential_margin_total:float,products_with_recipe:int}
     */
    public function catalogSummary(): array
    {
        $rows = $this->productMarginRowsQuery()->get();

        $estimatedCostTotal = round((float) $rows->sum('estimated_unit_cost'), 2);
        $catalogValueTotal = round((float) $rows->sum('product_price'), 2);
        $potentialMarginTotal = round($catalogValueTotal - $estimatedCostTotal, 2);

        return [
            'estimated_cost_total' => $estimatedCostTotal,
            'catalog_value_total' => $catalogValueTotal,
            'potential_margin_total' => $potentialMarginTotal,
            'products_with_recipe' => $rows->count(),
        ];
    }

    /**
     * @return array<int, array{product_id:int,product_name:string,product_price:float,estimated_unit_cost:float,margin_amount:float,margin_rate:float,bom_items:int}>
     */
    public function productMargins(int $limit = 25): array
    {
        $rows = $this->productMarginRowsQuery()
            ->orderByDesc('margin_amount')
            ->orderBy('product_name')
            ->limit($limit)
            ->get();

        return collect($rows)->map(static function (object $row): array {
            $productPrice = round((float) $row->product_price, 2);
            $estimatedUnitCost = round((float) $row->estimated_unit_cost, 2);
            $marginAmount = round((float) $row->margin_amount, 2);

            return [
                'product_id' => (int) $row->product_id,
                'product_name' => (string) $row->product_name,
                'product_price' => $productPrice,
                'estimated_unit_cost' => $estimatedUnitCost,
                'margin_amount' => $marginAmount,
                'margin_rate' => $productPrice > 0 ? round(($marginAmount / $productPrice) * 100, 2) : 0.0,
                'bom_items' => (int) $row->bom_items,
            ];
        })->all();
    }

    /**
     * @return array<int, array{product_id:int,product_name:string,revenue:float,estimated_cost:float,estimated_profit:float,margin_rate:float,quantity:int,orders:int}>
     */
    public function topProfitProducts(int $days, int $limit = 10): array
    {
        $rows = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->leftJoinSub($this->productCostPerUnitQuery(), 'product_costs', function ($join): void {
                $join->on('product_costs.product_id', '=', 'order_items.product_id');
            })
            ->whereNotNull('orders.placed_at')
            ->where('orders.placed_at', '>=', now()->subDays($days))
            ->where('orders.status', '!=', Order::STATUS_CANCELLED)
            ->whereNotNull('order_items.product_id')
            ->selectRaw('order_items.product_id as product_id, MAX(order_items.product_name_snapshot) as product_name, SUM(order_items.line_total) as revenue_total, SUM(order_items.quantity * COALESCE(product_costs.estimated_unit_cost, 0)) as estimated_cost_total, SUM(order_items.quantity) as quantity_total, COUNT(DISTINCT orders.id) as orders_count')
            ->groupBy('order_items.product_id')
            ->orderByRaw('(SUM(order_items.line_total) - SUM(order_items.quantity * COALESCE(product_costs.estimated_unit_cost, 0))) DESC')
            ->limit($limit)
            ->get();

        return collect($rows)->map(static function (object $row): array {
            $revenue = round((float) $row->revenue_total, 2);
            $estimatedCost = round((float) $row->estimated_cost_total, 2);
            $estimatedProfit = round($revenue - $estimatedCost, 2);

            return [
                'product_id' => (int) $row->product_id,
                'product_name' => (string) $row->product_name,
                'revenue' => $revenue,
                'estimated_cost' => $estimatedCost,
                'estimated_profit' => $estimatedProfit,
                'margin_rate' => $revenue > 0 ? round(($estimatedProfit / $revenue) * 100, 2) : 0.0,
                'quantity' => (int) $row->quantity_total,
                'orders' => (int) $row->orders_count,
            ];
        })->all();
    }

    /**
     * @return array<int, array{date:string,revenue:float,estimated_cost:float,actual_material_cost:float,gross_profit:float,estimated_profit:float,margin_rate:float,orders_count:int}>
     */
    public function orderProfitTrend(int $days): array
    {
        $rows = DB::table('orders')
            ->join('order_items', 'order_items.order_id', '=', 'orders.id')
            ->leftJoinSub($this->productCostPerUnitQuery(), 'product_costs', function ($join): void {
                $join->on('product_costs.product_id', '=', 'order_items.product_id');
            })
            ->whereNotNull('orders.placed_at')
            ->where('orders.placed_at', '>=', now()->subDays($days))
            ->where('orders.status', '!=', Order::STATUS_CANCELLED)
            ->selectRaw('DATE(orders.placed_at) as metric_date, SUM(order_items.line_total) as revenue_total, SUM(order_items.quantity * COALESCE(product_costs.estimated_unit_cost, 0)) as estimated_cost_total, COUNT(DISTINCT orders.id) as orders_count')
            ->groupByRaw('DATE(orders.placed_at)')
            ->orderByRaw('DATE(orders.placed_at)')
            ->get();

        $actualRows = DB::table('orders')
            ->whereNotNull('placed_at')
            ->where('placed_at', '>=', now()->subDays($days))
            ->where('status', '!=', Order::STATUS_CANCELLED)
            ->selectRaw('DATE(placed_at) as metric_date, SUM(COALESCE(material_cost_total, 0)) as actual_material_cost_total')
            ->groupByRaw('DATE(placed_at)')
            ->get()
            ->keyBy('metric_date');

        return collect($rows)->map(static function (object $row) use ($actualRows): array {
            $revenue = round((float) $row->revenue_total, 2);
            $estimatedCost = round((float) $row->estimated_cost_total, 2);
            $estimatedProfit = round($revenue - $estimatedCost, 2);
            $actualMaterialCost = round((float) ($actualRows[(string) $row->metric_date]->actual_material_cost_total ?? 0), 2);
            $grossProfit = round($revenue - $actualMaterialCost, 2);

            return [
                'date' => (string) $row->metric_date,
                'revenue' => $revenue,
                'estimated_cost' => $estimatedCost,
                'actual_material_cost' => $actualMaterialCost,
                'gross_profit' => $grossProfit,
                'estimated_profit' => $estimatedProfit,
                'margin_rate' => $revenue > 0 ? round(($grossProfit / $revenue) * 100, 2) : 0.0,
                'orders_count' => (int) $row->orders_count,
            ];
        })->all();
    }

    /**
     * @return array{revenue:float,estimated_cost:float,estimated_profit:float,actual_material_cost:float,waste_cost:float,gross_profit:float,margin_rate:float,estimated_vs_actual_delta:float}
     */
    public function periodSummary(int $days): array
    {
        $rows = $this->orderProfitTrend($days);
        $revenue = round(collect($rows)->sum('revenue'), 2);
        $estimatedCost = round(collect($rows)->sum('estimated_cost'), 2);
        $estimatedProfit = round($revenue - $estimatedCost, 2);
        $actualMaterialCost = round((float) DB::table('orders')
            ->whereNotNull('placed_at')
            ->where('placed_at', '>=', now()->subDays($days))
            ->where('status', '!=', Order::STATUS_CANCELLED)
            ->sum(DB::raw('COALESCE(material_cost_total, 0)')), 2);
        $wasteCost = round((float) DB::table('inventory_movements')
            ->where('movement_type', 'waste_out')
            ->where('occurred_at', '>=', now()->subDays($days))
            ->sum(DB::raw('COALESCE(total_cost, 0)')), 2);
        $grossProfit = round($revenue - $actualMaterialCost, 2);

        return [
            'revenue' => $revenue,
            'estimated_cost' => $estimatedCost,
            'estimated_profit' => $estimatedProfit,
            'actual_material_cost' => $actualMaterialCost,
            'waste_cost' => $wasteCost,
            'gross_profit' => $grossProfit,
            'margin_rate' => $revenue > 0 ? round(($grossProfit / $revenue) * 100, 2) : 0.0,
            'estimated_vs_actual_delta' => round($estimatedCost - $actualMaterialCost, 2),
        ];
    }

    private function productCostPerUnitQuery(): QueryBuilder
    {
        return DB::table('products')
            ->leftJoin('product_ingredients', 'product_ingredients.product_id', '=', 'products.id')
            ->leftJoin('ingredients', 'ingredients.id', '=', 'product_ingredients.ingredient_id')
            ->selectRaw('products.id as product_id, COALESCE(SUM(product_ingredients.quantity * COALESCE(ingredients.estimated_unit_cost, 0)), 0) as estimated_unit_cost')
            ->groupBy('products.id');
    }

    private function productMarginRowsQuery(): QueryBuilder
    {
        return DB::table('products')
            ->leftJoin('product_ingredients', 'product_ingredients.product_id', '=', 'products.id')
            ->leftJoin('ingredients', 'ingredients.id', '=', 'product_ingredients.ingredient_id')
            ->where('products.is_active', true)
            ->whereNull('products.deleted_at')
            ->selectRaw('products.id as product_id, products.name as product_name, products.price as product_price, COUNT(product_ingredients.id) as bom_items, COALESCE(SUM(product_ingredients.quantity * COALESCE(ingredients.estimated_unit_cost, 0)), 0) as estimated_unit_cost, (products.price - COALESCE(SUM(product_ingredients.quantity * COALESCE(ingredients.estimated_unit_cost, 0)), 0)) as margin_amount')
            ->groupBy('products.id', 'products.name', 'products.price');
    }
}
