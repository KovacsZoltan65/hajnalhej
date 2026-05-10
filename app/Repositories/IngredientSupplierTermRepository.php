<?php

namespace App\Repositories;

use App\Data\IngredientSupplierTerms\IngredientSupplierTermIndexData;
use App\Models\IngredientSupplierTerm;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class IngredientSupplierTermRepository
{
    public function paginateForAdmin(IngredientSupplierTermIndexData $filters): LengthAwarePaginator
    {
        return $this->adminQuery($filters)
            ->paginate($filters->per_page)
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
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): IngredientSupplierTerm
    {
        return IngredientSupplierTerm::query()->create($data);
    }

    /**
     * @param  array<string, mixed>  $data
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

    private function adminQuery(IngredientSupplierTermIndexData $filters): Builder
    {
        $query = IngredientSupplierTerm::query()
            ->select('ingredient_supplier_terms.*')
            ->with(['ingredient:id,name,unit', 'supplier:id,name'])
            ->join('ingredients', 'ingredients.id', '=', 'ingredient_supplier_terms.ingredient_id')
            ->join('suppliers', 'suppliers.id', '=', 'ingredient_supplier_terms.supplier_id')
            ->when($filters->search !== null, static function (Builder $query) use ($filters): void {
                $query->where(static function (Builder $inner) use ($filters): void {
                    $inner
                        ->where('ingredients.name', 'like', "%{$filters->search}%")
                        ->orWhere('suppliers.name', 'like', "%{$filters->search}%")
                        ->orWhere('ingredient_supplier_terms.supplier_sku', 'like', "%{$filters->search}%");
                });
            })
            ->when($filters->active !== null, static fn (Builder $query): Builder => $query->where('ingredient_supplier_terms.active', $filters->active));

        match ($filters->sort_field) {
            'supplier' => $query->orderBy('suppliers.name', $filters->sort_direction),
            'lead_time_days', 'minimum_order_quantity', 'pack_size', 'unit_cost_override', 'active', 'preferred' => $query->orderBy("ingredient_supplier_terms.{$filters->sort_field}", $filters->sort_direction),
            default => $query->orderBy('ingredients.name', $filters->sort_direction),
        };

        return $query->orderBy('ingredient_supplier_terms.id');
    }
}
