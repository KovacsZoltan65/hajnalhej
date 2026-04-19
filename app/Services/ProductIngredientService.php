<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductIngredient;
use App\Repositories\ProductIngredientRepository;
use Illuminate\Support\Collection;
use RuntimeException;

class ProductIngredientService
{
    public function __construct(private readonly ProductIngredientRepository $repository)
    {
    }

    /**
     * @return Collection<int, array{id:int,name:string,unit:string,is_low_stock:bool}>
     */
    public function listSelectableIngredients(): Collection
    {
        return $this->repository->listSelectableIngredients();
    }

    /**
     * @return Collection<int, ProductIngredient>
     */
    public function listByProduct(Product $product): Collection
    {
        return $this->repository->listByProduct($product);
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function create(Product $product, array $payload): ProductIngredient
    {
        $normalized = $this->normalizePayload($payload);

        if ($this->repository->existsForProduct($product, (int) $normalized['ingredient_id'])) {
            throw new RuntimeException('Ez az alapanyag mar szerepel a termek receptjeben.');
        }

        return $this->repository->create($product, $normalized);
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function update(Product $product, ProductIngredient $productIngredient, array $payload): ProductIngredient
    {
        $normalized = $this->normalizePayload($payload);

        if ($this->repository->existsForProduct($product, (int) $normalized['ingredient_id'], $productIngredient->id)) {
            throw new RuntimeException('Ez az alapanyag mar szerepel a termek receptjeben.');
        }

        return $this->repository->update($productIngredient, $normalized);
    }

    public function delete(ProductIngredient $productIngredient): void
    {
        $this->repository->delete($productIngredient);
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    private function normalizePayload(array $payload): array
    {
        return [
            'ingredient_id' => (int) $payload['ingredient_id'],
            'quantity' => number_format((float) $payload['quantity'], 3, '.', ''),
            'sort_order' => (int) ($payload['sort_order'] ?? 0),
            'notes' => $payload['notes'] ?? null,
        ];
    }
}
