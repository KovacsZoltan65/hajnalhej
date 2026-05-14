<?php

declare(strict_types=1);

namespace App\Exports;

use App\Contracts\Export\Exporter;
use App\Exports\Concerns\FormatsExportValues;
use App\Models\InventoryMovement;
use Illuminate\Database\Eloquent\Builder;

class InventoryMovementsExport implements Exporter
{
    use FormatsExportValues;

    public function headings(): array
    {
        return ['ID', 'Ingredient', 'Movement Type', 'Direction', 'Quantity', 'Reference Type', 'Reference ID', 'Created By', 'Created At'];
    }

    public function query(array $filters): Builder
    {
        return InventoryMovement::query()
            ->select(['id', 'ingredient_id', 'movement_type', 'direction', 'quantity', 'reference_type', 'reference_id', 'created_by', 'created_at'])
            ->with(['ingredient:id,name', 'creator:id,name'])
            ->when($filters['ingredient_id'] ?? null, fn (Builder $query, mixed $ingredientId): Builder => $query->where('ingredient_id', (int) $ingredientId))
            ->when($filters['movement_type'] ?? null, fn (Builder $query, string $type): Builder => $query->where('movement_type', $type))
            ->when($filters['direction'] ?? null, fn (Builder $query, string $direction): Builder => $query->where('direction', $direction))
            ->when($filters['date_from'] ?? null, fn (Builder $query, string $date): Builder => $query->whereDate('created_at', '>=', $date))
            ->when($filters['date_to'] ?? null, fn (Builder $query, string $date): Builder => $query->whereDate('created_at', '<=', $date))
            ->orderByDesc('id');
    }

    public function map(mixed $row): array
    {
        return [
            $row->id,
            $row->ingredient?->name ?? '',
            $this->humanStatus($row->movement_type),
            $this->humanStatus($row->direction),
            $this->decimal($row->quantity),
            $row->reference_type ?? '',
            $row->reference_id ?? '',
            $row->creator?->name ?? '',
            $this->dateTime($row->created_at),
        ];
    }

    public function filename(array $filters = []): string
    {
        return 'inventory_movements';
    }
}
