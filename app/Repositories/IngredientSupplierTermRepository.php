<?php

namespace App\Repositories;

use App\Models\IngredientSupplierTerm;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class IngredientSupplierTermRepository
{
    /**
     * @param array<string, mixed> $filters
     */
    public function paginateForAdmin(array $filters): LengthAwarePaginator
    {
        $perPage = (int) ($filters['per_page'] ?? 10);

        return $this->adminQuery($filters)
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * @return Collection<int, IngredientSupplierTerm>
     */
    public function activePreferredTermsForIngredient(int $ingredientId, ?int $exceptId = null): Collection
    {
        return IngredientSupplierTerm::query()
            ->where('ingredient_id', $ingredientId)
            ->where('active', true)
            ->where('preferred', true)
            ->when($exceptId !== null, static fn (Builder $query): Builder => $query->whereKeyNot($exceptId))
            ->get();
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): IngredientSupplierTerm
    {
        return IngredientSupplierTerm::query()->create($data);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function update(IngredientSupplierTerm $term, array $data): IngredientSupplierTerm
    {
        $term->update($data);

        return $term->refresh();
    }

    public function clearPreferredForIngredient(int $ingredientId, ?int $exceptId = null): void
    {
        IngredientSupplierTerm::query()
            ->where('ingredient_id', $ingredientId)
            ->where('active', true)
            ->where('preferred', true)
            ->when($exceptId !== null, static fn (Builder $query): Builder => $query->whereKeyNot($exceptId))
            ->update(['preferred' => false]);
    }

    public function delete(IngredientSupplierTerm $term): void
    {
        $term->delete();
    }

    /**
     * @param array<string, mixed> $filters
     */
    private function adminQuery(array $filters): Builder
    {
        $search = trim((string) ($filters['search'] ?? ''));
        $active = (string) ($filters['active'] ?? '');
        $sortField = (string) ($filters['sort_field'] ?? 'ingredient');
        $sortDirection = (string) ($filters['sort_direction'] ?? 'asc');

        if (! \in_array($sortDirection, ['asc', 'desc'], true)) {
            $sortDirection = 'asc';
        }

        $query = IngredientSupplierTerm::query()
            ->select('ingredient_supplier_terms.*')
            ->with(['ingredient:id,name,unit', 'supplier:id,name'])
            ->join('ingredients', 'ingredients.id', '=', 'ingredient_supplier_terms.ingredient_id')
            ->join('suppliers', 'suppliers.id', '=', 'ingredient_supplier_terms.supplier_id')
            ->when($search !== '', static function (Builder $query) use ($search): void {
                $query->where(static function (Builder $inner) use ($search): void {
                    $inner
                        ->where('ingredients.name', 'like', "%{$search}%")
                        ->orWhere('suppliers.name', 'like', "%{$search}%")
                        ->orWhere('ingredient_supplier_terms.supplier_sku', 'like', "%{$search}%");
                });
            })
            ->when($active !== '', static fn (Builder $query): Builder => $query->where('ingredient_supplier_terms.active', $active === '1'));

        match ($sortField) {
            'supplier' => $query->orderBy('suppliers.name', $sortDirection),
            'lead_time_days', 'minimum_order_quantity', 'pack_size', 'unit_cost_override', 'active', 'preferred' => $query->orderBy("ingredient_supplier_terms.{$sortField}", $sortDirection),
            default => $query->orderBy('ingredients.name', $sortDirection),
        };

        return $query->orderBy('ingredient_supplier_terms.id');
    }
}
