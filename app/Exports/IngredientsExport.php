<?php

declare(strict_types=1);

namespace App\Exports;

use App\Contracts\Export\Exporter;
use App\Exports\Concerns\FormatsExportValues;
use App\Models\Ingredient;
use Illuminate\Database\Eloquent\Builder;

class IngredientsExport implements Exporter
{
    use FormatsExportValues;

    public function headings(): array
    {
        return ['ID', 'Name', 'SKU', 'Unit', 'Current Stock', 'Minimum Stock', 'Estimated Unit Cost', 'Status', 'Created At', 'Updated At'];
    }

    public function query(array $filters): Builder
    {
        return Ingredient::query()
            ->select(['id', 'name', 'sku', 'unit', 'current_stock', 'minimum_stock', 'estimated_unit_cost', 'is_active', 'created_at', 'updated_at'])
            ->when(array_key_exists('status', $filters) && $filters['status'] !== null && $filters['status'] !== '', function (Builder $query) use ($filters): Builder {
                return $query->where('is_active', filter_var($filters['status'], FILTER_VALIDATE_BOOL));
            })
            ->when(($filters['low_stock'] ?? null) !== null && $filters['low_stock'] !== '', function (Builder $query) use ($filters): Builder {
                if (filter_var($filters['low_stock'], FILTER_VALIDATE_BOOL)) {
                    return $query->whereColumn('current_stock', '<=', 'minimum_stock');
                }

                return $query;
            })
            ->when($filters['search'] ?? null, function (Builder $query, string $search): void {
                $query->where(function (Builder $inner) use ($search): void {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->orderBy('id');
    }

    public function map(mixed $row): array
    {
        return [
            $row->id,
            $row->name,
            $row->sku,
            $row->unit,
            $this->decimal($row->current_stock),
            $this->decimal($row->minimum_stock),
            $this->decimal($row->estimated_unit_cost, 4),
            $row->is_active ? 'Active' : 'Inactive',
            $this->dateTime($row->created_at),
            $this->dateTime($row->updated_at),
        ];
    }

    public function filename(array $filters = []): string
    {
        return 'ingredients';
    }
}
