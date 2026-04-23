<?php

namespace App\Repositories;

use App\Models\InventoryMovement;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class InventoryMovementRepository
{
    /**
     * @param array<string, mixed> $data
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

    /**
     * @param array<string, mixed> $filters
     */
    public function paginateLedger(array $filters): LengthAwarePaginator
    {
        $perPage = (int) ($filters['per_page'] ?? 15);

        return $this->ledgerQuery($filters)
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * @return array{total_stock_value:float,low_stock_count:int,out_of_stock_count:int,weekly_waste_cost:float,weekly_purchase_value:float}
     */
    public function dashboardSummary(int $days): array
    {
        $totalStockValue = (float) DB::table('ingredients')->sum(DB::raw('COALESCE(stock_value, 0)'));
        $lowStockCount = (int) DB::table('ingredients')
            ->whereRaw('current_stock <= COALESCE(reorder_level, minimum_stock)')
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
     * @return Collection<int, array{id:int,name:string,unit:string,current_stock:float,reorder_level:float,is_low_stock:bool}>
     */
    public function lowStockIngredients(int $limit = 12): Collection
    {
        return DB::table('ingredients')
            ->select([
                'id',
                'name',
                'unit',
                DB::raw('current_stock as current_stock'),
                DB::raw('COALESCE(reorder_level, minimum_stock) as reorder_level'),
            ])
            ->whereRaw('current_stock <= COALESCE(reorder_level, minimum_stock)')
            ->orderBy('current_stock')
            ->limit($limit)
            ->get()
            ->map(static fn (object $row): array => [
                'id' => (int) $row->id,
                'name' => (string) $row->name,
                'unit' => (string) $row->unit,
                'current_stock' => round((float) $row->current_stock, 3),
                'reorder_level' => round((float) $row->reorder_level, 3),
                'is_low_stock' => (float) $row->current_stock <= (float) $row->reorder_level,
            ]);
    }

    /**
     * @param array<string, mixed> $filters
     */
    private function ledgerQuery(array $filters): Builder
    {
        $dateFrom = $filters['date_from'] ?? null;
        $dateTo = $filters['date_to'] ?? null;
        $ingredientId = $filters['ingredient_id'] ?? null;
        $movementType = trim((string) ($filters['movement_type'] ?? ''));
        $search = trim((string) ($filters['search'] ?? ''));

        return InventoryMovement::query()
            ->with(['ingredient:id,name,unit', 'creator:id,name,email'])
            ->when($dateFrom !== null && $dateFrom !== '', fn (Builder $query): Builder => $query->whereDate('occurred_at', '>=', (string) $dateFrom))
            ->when($dateTo !== null && $dateTo !== '', fn (Builder $query): Builder => $query->whereDate('occurred_at', '<=', (string) $dateTo))
            ->when($ingredientId !== null && $ingredientId !== '', fn (Builder $query): Builder => $query->where('ingredient_id', (int) $ingredientId))
            ->when($movementType !== '', fn (Builder $query): Builder => $query->where('movement_type', $movementType))
            ->when($search !== '', function (Builder $query) use ($search): void {
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

