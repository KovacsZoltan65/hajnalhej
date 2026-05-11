<?php

namespace App\Repositories;

use App\Data\Inventory\InventoryLedgerIndexData;
use App\Models\InventoryMovement;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class InventoryMovementRepository
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): InventoryMovement
    {
        return InventoryMovement::query()->create($data);
    }

    public function existsForReference(string $referenceType, int $referenceId, string $movementType): bool
    {
        return InventoryMovement::query()
            ->where('reference_type', $referenceType)
            ->where('reference_id', $referenceId)
            ->where('movement_type', $movementType)
            ->exists();
    }

    public function paginateLedger(InventoryLedgerIndexData $filters): LengthAwarePaginator
    {
        return $this->ledgerQuery($filters)
            ->paginate($filters->per_page)
            ->withQueryString();
    }

    /**
     * @return array{total_stock_value:float,low_stock_count:int,out_of_stock_count:int,weekly_waste_cost:float,weekly_purchase_value:float}
     */
    public function dashboardSummary(int $days): array
    {
        $totalStockValue = (float) DB::table('ingredients')->sum(DB::raw('COALESCE(stock_value, 0)'));
        $lowStockCount = (int) DB::table('ingredients')
            ->whereColumn('current_stock', '<=', 'minimum_stock')
            ->count();
        $outOfStockCount = (int) DB::table('ingredients')
            ->where('current_stock', '<=', 0)
            ->count();

        $since = now()->subDays($days);

        $weeklyWasteCost = (float) InventoryMovement::query()
            ->where('movement_type', InventoryMovement::TYPE_WASTE_OUT)
            ->where('occurred_at', '>=', $since)
            ->sum(DB::raw('COALESCE(total_cost, 0)'));

        $weeklyPurchaseValue = (float) InventoryMovement::query()
            ->where('movement_type', InventoryMovement::TYPE_PURCHASE_IN)
            ->where('occurred_at', '>=', $since)
            ->sum(DB::raw('COALESCE(total_cost, 0)'));

        return [
            'total_stock_value' => round($totalStockValue, 2),
            'low_stock_count' => $lowStockCount,
            'out_of_stock_count' => $outOfStockCount,
            'weekly_waste_cost' => round($weeklyWasteCost, 2),
            'weekly_purchase_value' => round($weeklyPurchaseValue, 2),
        ];
    }

    /**
     * @return Collection<int, array{id:int,name:string,unit:string,current_stock:float,minimum_stock:float,is_low_stock:bool}>
     */
    public function lowStockIngredients(int $limit = 12): Collection
    {
        return DB::table('ingredients')
            ->select([
                'id',
                'name',
                'unit',
                DB::raw('current_stock as current_stock'),
                DB::raw('minimum_stock as minimum_stock'),
            ])
            ->whereColumn('current_stock', '<=', 'minimum_stock')
            ->orderBy('current_stock')
            ->limit($limit)
            ->get()
            ->map(static fn (object $row): array => [
                'id' => (int) $row->id,
                'name' => (string) $row->name,
                'unit' => (string) $row->unit,
                'current_stock' => round((float) $row->current_stock, 3),
                'minimum_stock' => round((float) $row->minimum_stock, 3),
                'is_low_stock' => (float) $row->current_stock <= (float) $row->minimum_stock,
            ]);
    }

    private function ledgerQuery(InventoryLedgerIndexData $filters): Builder
    {
        return InventoryMovement::query()
            ->with(['ingredient:id,name,unit', 'creator:id,name,email'])
            ->when($filters->date_from !== null, fn (Builder $query): Builder => $query->whereDate('occurred_at', '>=', $filters->date_from))
            ->when($filters->date_to !== null, fn (Builder $query): Builder => $query->whereDate('occurred_at', '<=', $filters->date_to))
            ->when($filters->ingredient_id !== null, fn (Builder $query): Builder => $query->where('ingredient_id', $filters->ingredient_id))
            ->when($filters->movement_type !== null, fn (Builder $query): Builder => $query->where('movement_type', $filters->movement_type))
            ->when($filters->search !== null, function (Builder $query) use ($filters): void {
                $search = $filters->search;

                $query->where(function (Builder $inner) use ($search): void {
                    $inner
                        ->where('notes', 'like', "%{$search}%")
                        ->orWhere('reference_type', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('occurred_at')
            ->orderByDesc('id');
    }
}
