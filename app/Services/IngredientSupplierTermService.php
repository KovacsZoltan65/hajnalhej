<?php

namespace App\Services;

use App\Data\IngredientSupplierTerms\IngredientSupplierTermIndexData;
use App\Data\IngredientSupplierTerms\IngredientSupplierTermInlineUpdateData;
use App\Data\IngredientSupplierTerms\IngredientSupplierTermStoreData;
use App\Data\IngredientSupplierTerms\IngredientSupplierTermUpdateData;
use App\Models\IngredientSupplierTerm;
use App\Repositories\IngredientSupplierTermRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class IngredientSupplierTermService
{
    public function __construct(private readonly IngredientSupplierTermRepository $repository) {}

    public function paginateForAdmin(IngredientSupplierTermIndexData $filters): LengthAwarePaginator
    {
        return $this->repository->paginateForAdmin($filters);
    }

    public function create(IngredientSupplierTermStoreData $payload): IngredientSupplierTerm
    {
        return DB::transaction(function () use ($payload): IngredientSupplierTerm {
            $data = $payload->toPayload();
            $this->guardPreferredRules($data);

            if ($data['preferred']) {
                $this->repository->clearPreferredForIngredient((int) $data['ingredient_id']);
            }

            return $this->repository->create($data);
        });
    }

    public function update(IngredientSupplierTerm $term, IngredientSupplierTermUpdateData $payload): IngredientSupplierTerm
    {
        return DB::transaction(function () use ($term, $payload): IngredientSupplierTerm {
            $data = $payload->toPayload();
            $this->guardPreferredRules($data);

            if ($data['preferred']) {
                $this->repository->clearPreferredForIngredient((int) $data['ingredient_id'], $term->id);
            }

            return $this->repository->update($term, $data);
        });
    }

    public function updateInline(IngredientSupplierTerm $term, IngredientSupplierTermInlineUpdateData $payload): IngredientSupplierTerm
    {
        $data = [
            'ingredient_id' => $term->ingredient_id,
            'supplier_id' => $term->supplier_id,
            'lead_time_days' => $term->lead_time_days,
            'minimum_order_quantity' => $term->minimum_order_quantity,
            'pack_size' => $term->pack_size,
            'unit_cost_override' => $term->unit_cost_override,
            'preferred' => $term->preferred,
            'active' => $term->active,
            'meta' => $term->meta,
        ];

        $data[$payload->field] = $payload->value;

        return $this->update($term, IngredientSupplierTermUpdateData::from($data));
    }

    public function delete(IngredientSupplierTerm $term): void
    {
        $this->repository->delete($term);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function guardPreferredRules(array $data): void
    {
        if (! $data['active'] && $data['preferred']) {
            throw ValidationException::withMessages([
                'preferred' => 'Inaktív beszállítói feltétel nem lehet preferált.',
            ]);
        }
    }
}
