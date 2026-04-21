<?php

namespace App\Repositories;

use App\Models\Ingredient;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class IngredientRepository
{
    /**
     * @param array<string, mixed> $filters
     * @return LengthAwarePaginator
     */
    public function paginateForAdmin(array $filters): LengthAwarePaginator
    {
        $perPage = (int) ($filters['per_page'] ?? 10);

        return $this->adminQuery($filters)
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * @return Collection<int, array{id:int,name:string,unit:string,is_low_stock:bool}>
     */
    public function listSelectableActive(): Collection
    {
        return Ingredient::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'unit', 'current_stock', 'minimum_stock'])
            ->map(fn (Ingredient $ingredient): array => [
                'id' => $ingredient->id,
                'name' => $ingredient->name,
                'unit' => $ingredient->unit,
                'is_low_stock' => $ingredient->isLowStock(),
            ]);
    }

    /**
     * @param array<string, mixed> $data
     * @return Ingredient
     */
    public function create(array $data): Ingredient
    {
        return Ingredient::query()->create($data);
    }

    /**
     * @param array<string, mixed> $data
     * @return Ingredient
     */
    public function update(Ingredient $ingredient, array $data): Ingredient
    {
        $ingredient->update($data);

        return $ingredient->refresh();
    }

    /**
     * Summary of delete
     * @param Ingredient $ingredient
     * @return void
     */
    public function delete(Ingredient $ingredient): void
    {
        $ingredient->delete();
    }

    /**
     * Summary of slugExists
     * @param string $slug
     * @param mixed $ignoreId
     * @return bool
     */
    public function slugExists(string $slug, ?int $ignoreId = null): bool
    {
        return Ingredient::query()
            ->where('slug', $slug)
            ->when($ignoreId !== null, fn (Builder $query): Builder => $query->whereKeyNot($ignoreId))
            ->exists();
    }

    /**
     * @param array<string, mixed> $filters
     * @return Builder
     */
    private function adminQuery(array $filters): Builder
    {
        $search = trim((string) ($filters['search'] ?? ''));
        $isActive = $filters['is_active'] ?? '';
        $unit = trim((string) ($filters['unit'] ?? ''));
        $sortField = (string) ($filters['sort_field'] ?? 'name');
        $sortDirection = (string) ($filters['sort_direction'] ?? 'asc');

        $sortableFields = ['name', 'unit', 'current_stock', 'minimum_stock', 'is_active'];

        if (! \in_array($sortField, $sortableFields, true)) {
            $sortField = 'name';
        }

        if (! \in_array($sortDirection, ['asc', 'desc'], true)) {
            $sortDirection = 'asc';
        }

        $query = Ingredient::query()
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $innerQuery) use ($search): void {
                    $innerQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%");
                });
            })
            ->when($isActive !== null && $isActive !== '', function (Builder $query) use ($isActive): void {
                $query->where('is_active', (bool) $isActive);
            })
            ->when($unit !== '', function (Builder $query) use ($unit): void {
                $query->where('unit', $unit);
            });

        $query
            ->orderBy($sortField, $sortDirection)
            ->orderBy('id');

        return $query;
    }
}
