<?php

declare(strict_types=1);

namespace App\Exports;

use App\Contracts\Export\Exporter;
use App\Exports\Concerns\FormatsExportValues;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;

class ProductsExport implements Exporter
{
    use FormatsExportValues;

    public function headings(): array
    {
        return ['ID', 'Name', 'Slug', 'Category', 'SKU', 'Price', 'Status', 'Sort Order', 'Created At', 'Updated At'];
    }

    public function query(array $filters): Builder
    {
        return Product::query()
            ->select(['id', 'category_id', 'name', 'slug', 'price', 'is_active', 'sort_order', 'created_at', 'updated_at'])
            ->with('category:id,name')
            ->when($filters['category_id'] ?? null, fn (Builder $query, mixed $categoryId): Builder => $query->where('category_id', (int) $categoryId))
            ->when(array_key_exists('status', $filters) && $filters['status'] !== null && $filters['status'] !== '', function (Builder $query) use ($filters): Builder {
                return $query->where('is_active', filter_var($filters['status'], FILTER_VALIDATE_BOOL));
            })
            ->when(array_key_exists('is_active', $filters) && $filters['is_active'] !== null && $filters['is_active'] !== '', function (Builder $query) use ($filters): Builder {
                return $query->where('is_active', filter_var($filters['is_active'], FILTER_VALIDATE_BOOL));
            })
            ->when($filters['search'] ?? null, function (Builder $query, string $search): void {
                $query->where(function (Builder $inner) use ($search): void {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%");
                });
            })
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function map(mixed $row): array
    {
        return [
            $row->id,
            $row->name,
            $row->slug,
            $row->category?->name ?? '',
            '',
            $this->money($row->price),
            $row->is_active ? 'Active' : 'Inactive',
            $row->sort_order,
            $this->dateTime($row->created_at),
            $this->dateTime($row->updated_at),
        ];
    }

    public function filename(array $filters = []): string
    {
        return 'products';
    }
}
