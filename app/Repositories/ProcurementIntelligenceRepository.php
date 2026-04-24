<?php

namespace App\Repositories;

use App\Models\InventoryMovement;
use App\Models\Purchase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ProcurementIntelligenceRepository
{
    /**
     * @param array<string, mixed> $filters
     * @return Collection<int, object>
     */
    public function purchasePriceRows(array $filters): Collection
    {
        $since = Carbon::today()->subDays((int) ($filters['days'] ?? 30) - 1);

        return DB::table('purchase_items')
            ->join('purchases', 'purchases.id', '=', 'purchase_items.purchase_id')
            ->leftJoin('suppliers', 'suppliers.id', '=', 'purchases.supplier_id')
            ->join('ingredients', 'ingredients.id', '=', 'purchase_items.ingredient_id')
            ->select([
                'purchase_items.id',
                'purchase_items.ingredient_id',
                'purchase_items.quantity',
                'purchase_items.unit',
                'purchase_items.unit_cost',
                'purchase_items.line_total',
                'purchases.supplier_id',
                'purchases.purchase_date',
                'ingredients.name as ingredient_name',
                'ingredients.unit as ingredient_unit',
                DB::raw("COALESCE(suppliers.name, 'Nincs beszállító') as supplier_name"),
            ])
            ->where('purchases.status', Purchase::STATUS_POSTED)
            ->whereDate('purchases.purchase_date', '>=', $since->toDateString())
            ->when($filters['ingredient_id'] ?? null, fn ($query, $ingredientId) => $query->where('purchase_items.ingredient_id', (int) $ingredientId))
            ->when($filters['supplier_id'] ?? null, fn ($query, $supplierId) => $query->where('purchases.supplier_id', (int) $supplierId))
            ->orderByDesc('purchases.purchase_date')
            ->orderByDesc('purchase_items.id')
            ->get();
    }

    /**
     * @param array<string, mixed> $filters
     * @return Collection<int, object>
     */
    public function costTrendRows(array $filters): Collection
    {
        $since = Carbon::today()->subDays((int) ($filters['days'] ?? 30) - 1);

        return DB::table('purchase_items')
            ->join('purchases', 'purchases.id', '=', 'purchase_items.purchase_id')
            ->leftJoin('suppliers', 'suppliers.id', '=', 'purchases.supplier_id')
            ->join('ingredients', 'ingredients.id', '=', 'purchase_items.ingredient_id')
            ->select([
                DB::raw('DATE(purchases.purchase_date) as period_date'),
                'purchase_items.ingredient_id',
                'purchases.supplier_id',
                'ingredients.name as ingredient_name',
                'ingredients.unit as ingredient_unit',
                DB::raw("COALESCE(suppliers.name, 'Nincs beszállító') as supplier_name"),
                DB::raw('AVG(purchase_items.unit_cost) as average_unit_cost'),
                DB::raw('SUM(purchase_items.line_total) / NULLIF(SUM(purchase_items.quantity), 0) as weighted_average_cost'),
                DB::raw('SUM(purchase_items.quantity) as purchased_quantity'),
                DB::raw('COUNT(*) as purchases_count'),
                DB::raw('MAX(purchase_items.unit_cost) as last_unit_cost'),
                DB::raw('MAX(purchases.purchase_date) as last_purchase_date'),
            ])
            ->where('purchases.status', Purchase::STATUS_POSTED)
            ->whereDate('purchases.purchase_date', '>=', $since->toDateString())
            ->when($filters['ingredient_id'] ?? null, fn ($query, $ingredientId) => $query->where('purchase_items.ingredient_id', (int) $ingredientId))
            ->when($filters['supplier_id'] ?? null, fn ($query, $supplierId) => $query->where('purchases.supplier_id', (int) $supplierId))
            ->groupBy([
                DB::raw('DATE(purchases.purchase_date)'),
                'purchase_items.ingredient_id',
                'purchases.supplier_id',
                'ingredients.name',
                'ingredients.unit',
                'suppliers.name',
            ])
            ->orderByDesc('period_date')
            ->limit(120)
            ->get();
    }

    /**
     * @param array<string, mixed> $filters
     * @return Collection<int, object>
     */
    public function recentPurchaseRows(array $filters): Collection
    {
        return DB::table('purchase_items')
            ->join('purchases', 'purchases.id', '=', 'purchase_items.purchase_id')
            ->leftJoin('suppliers', 'suppliers.id', '=', 'purchases.supplier_id')
            ->join('ingredients', 'ingredients.id', '=', 'purchase_items.ingredient_id')
            ->select([
                'purchase_items.ingredient_id',
                'ingredients.name as ingredient_name',
                'purchase_items.quantity',
                'purchase_items.unit',
                'purchase_items.unit_cost',
                'purchase_items.line_total',
                'purchases.purchase_date',
                DB::raw("COALESCE(suppliers.name, 'Nincs beszállító') as supplier_name"),
            ])
            ->where('purchases.status', Purchase::STATUS_POSTED)
            ->when($filters['ingredient_id'] ?? null, fn ($query, $ingredientId) => $query->where('purchase_items.ingredient_id', (int) $ingredientId))
            ->when($filters['supplier_id'] ?? null, fn ($query, $supplierId) => $query->where('purchases.supplier_id', (int) $supplierId))
            ->orderByDesc('purchases.purchase_date')
            ->orderByDesc('purchase_items.id')
            ->limit(5)
            ->get();
    }

    /**
     * @return Collection<int, object>
     */
    public function ingredientStockRows(): Collection
    {
        return DB::table('ingredients')
            ->leftJoinSub(
                DB::table('product_ingredients')
                    ->select('ingredient_id', DB::raw('COUNT(*) as bom_usage_count'))
                    ->groupBy('ingredient_id'),
                'bom_usage',
                'bom_usage.ingredient_id',
                '=',
                'ingredients.id'
            )
            ->select([
                'ingredients.id',
                'ingredients.name',
                'ingredients.unit',
                'ingredients.current_stock',
                'ingredients.minimum_stock',
                'ingredients.estimated_unit_cost',
                'ingredients.average_unit_cost',
                DB::raw('COALESCE(bom_usage.bom_usage_count, 0) as bom_usage_count'),
            ])
            ->whereNull('ingredients.deleted_at')
            ->where('ingredients.is_active', true)
            ->orderBy('ingredients.name')
            ->get();
    }

    /**
     * @return Collection<int, object>
     */
    public function consumptionRows(int $days): Collection
    {
        $since = Carbon::today()->subDays($days - 1)->startOfDay();

        return DB::table('inventory_movements')
            ->select([
                'ingredient_id',
                DB::raw('SUM(quantity) as consumed_quantity'),
            ])
            ->where('movement_type', InventoryMovement::TYPE_PRODUCTION_OUT)
            ->where('occurred_at', '>=', $since)
            ->groupBy('ingredient_id')
            ->get();
    }

    /**
     * @return Collection<int, object>
     */
    public function lastPurchaseDates(): Collection
    {
        return DB::table('purchase_items')
            ->join('purchases', 'purchases.id', '=', 'purchase_items.purchase_id')
            ->select([
                'purchase_items.ingredient_id',
                DB::raw('MAX(purchases.purchase_date) as last_purchase_date'),
            ])
            ->where('purchases.status', Purchase::STATUS_POSTED)
            ->groupBy('purchase_items.ingredient_id')
            ->get();
    }

    /**
     * @param array<int, int> $ingredientIds
     * @return Collection<int, object>
     */
    public function latestPurchaseRowsForIngredients(array $ingredientIds): Collection
    {
        if ($ingredientIds === []) {
            return collect();
        }

        $rankedRows = DB::table('purchase_items')
            ->join('purchases', 'purchases.id', '=', 'purchase_items.purchase_id')
            ->select([
                'purchase_items.ingredient_id',
                'purchases.supplier_id',
                'suppliers.name as supplier_name',
                'suppliers.lead_time_days as supplier_lead_time_days',
                'purchase_items.unit_cost',
                'purchases.purchase_date',
                DB::raw('ROW_NUMBER() OVER (PARTITION BY purchase_items.ingredient_id ORDER BY purchases.purchase_date DESC, purchase_items.id DESC) as row_number'),
            ])
            ->leftJoin('suppliers', 'suppliers.id', '=', 'purchases.supplier_id')
            ->where('purchases.status', Purchase::STATUS_POSTED)
            ->whereIn('purchase_items.ingredient_id', $ingredientIds);

        return DB::query()
            ->fromSub($rankedRows, 'latest_purchase_rows')
            ->where('row_number', 1)
            ->get();
    }

    /**
     * @param array<int, int> $ingredientIds
     * @return Collection<int, object>
     */
    public function cheapestFreshSupplierRows(array $ingredientIds, int $days): Collection
    {
        if ($ingredientIds === []) {
            return collect();
        }

        $since = Carbon::today()->subDays($days - 1)->toDateString();
        $rankedRows = DB::table('purchase_items')
            ->join('purchases', 'purchases.id', '=', 'purchase_items.purchase_id')
            ->select([
                'purchase_items.ingredient_id',
                'purchases.supplier_id',
                'suppliers.name as supplier_name',
                'suppliers.lead_time_days as supplier_lead_time_days',
                'purchase_items.unit_cost',
                'purchases.purchase_date',
                DB::raw('ROW_NUMBER() OVER (PARTITION BY purchase_items.ingredient_id ORDER BY purchase_items.unit_cost ASC, purchases.purchase_date DESC, purchase_items.id DESC) as row_number'),
            ])
            ->join('suppliers', 'suppliers.id', '=', 'purchases.supplier_id')
            ->where('purchases.status', Purchase::STATUS_POSTED)
            ->whereNotNull('purchases.supplier_id')
            ->whereDate('purchases.purchase_date', '>=', $since)
            ->whereIn('purchase_items.ingredient_id', $ingredientIds);

        return DB::query()
            ->fromSub($rankedRows, 'cheapest_fresh_supplier_rows')
            ->where('row_number', 1)
            ->get();
    }

    /**
     * @param array<int, int> $ingredientIds
     * @return Collection<int, object>
     */
    public function supplierTermRowsForIngredients(array $ingredientIds): Collection
    {
        if ($ingredientIds === []) {
            return collect();
        }

        $rankedSupplierCosts = DB::table('purchase_items')
            ->join('purchases', 'purchases.id', '=', 'purchase_items.purchase_id')
            ->select([
                'purchase_items.ingredient_id',
                'purchases.supplier_id',
                'purchase_items.unit_cost',
                DB::raw('ROW_NUMBER() OVER (PARTITION BY purchase_items.ingredient_id, purchases.supplier_id ORDER BY purchases.purchase_date DESC, purchase_items.id DESC) as row_number'),
            ])
            ->where('purchases.status', Purchase::STATUS_POSTED)
            ->whereNotNull('purchases.supplier_id')
            ->whereIn('purchase_items.ingredient_id', $ingredientIds);

        $latestSupplierCosts = DB::query()
            ->fromSub($rankedSupplierCosts, 'ranked_supplier_costs')
            ->where('row_number', 1);

        return DB::table('ingredient_supplier_terms')
            ->join('suppliers', 'suppliers.id', '=', 'ingredient_supplier_terms.supplier_id')
            ->leftJoinSub($latestSupplierCosts, 'latest_supplier_costs', function ($join): void {
                $join->on('latest_supplier_costs.ingredient_id', '=', 'ingredient_supplier_terms.ingredient_id')
                    ->on('latest_supplier_costs.supplier_id', '=', 'ingredient_supplier_terms.supplier_id');
            })
            ->select([
                'ingredient_supplier_terms.ingredient_id',
                'ingredient_supplier_terms.supplier_id',
                'suppliers.name as supplier_name',
                'suppliers.lead_time_days as supplier_lead_time_days',
                'ingredient_supplier_terms.lead_time_days',
                'ingredient_supplier_terms.minimum_order_quantity',
                'ingredient_supplier_terms.pack_size',
                'ingredient_supplier_terms.preferred',
                'ingredient_supplier_terms.unit_cost_override',
                'latest_supplier_costs.unit_cost as latest_unit_cost',
            ])
            ->whereIn('ingredient_supplier_terms.ingredient_id', $ingredientIds)
            ->orderByDesc('ingredient_supplier_terms.preferred')
            ->orderBy('suppliers.name')
            ->get();
    }

    /**
     * @return Collection<int, array{label:string,value:int}>
     */
    public function ingredientOptions(): Collection
    {
        return DB::table('ingredients')
            ->whereNull('deleted_at')
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(static fn (object $row): array => [
                'label' => (string) $row->name,
                'value' => (int) $row->id,
            ]);
    }

    /**
     * @return Collection<int, array{label:string,value:int}>
     */
    public function supplierOptions(): Collection
    {
        return DB::table('suppliers')
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(static fn (object $row): array => [
                'label' => (string) $row->name,
                'value' => (int) $row->id,
            ]);
    }
}
