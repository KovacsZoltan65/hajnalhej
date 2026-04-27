<?php

namespace App\Services;

use App\Models\IngredientSupplierTerm;
use App\Repositories\IngredientSupplierTermRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class IngredientSupplierTermService
{
    public function __construct(private readonly IngredientSupplierTermRepository $repository)
    {
    }

    /**
     * @param array<string, mixed> $filters
     */
    public function paginateForAdmin(array $filters): LengthAwarePaginator
    {
        return $this->repository->paginateForAdmin($filters);
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function create(array $payload): IngredientSupplierTerm
    {
        return DB::transaction(function () use ($payload): IngredientSupplierTerm {
            $data = $this->normalizePayload($payload);
            $this->guardPreferredRules($data);

            if ($data['preferred']) {
                $this->repository->clearPreferredForIngredient((int) $data['ingredient_id']);
            }

            return $this->repository->create($data);
        });
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function update(IngredientSupplierTerm $term, array $payload): IngredientSupplierTerm
    {
        return DB::transaction(function () use ($term, $payload): IngredientSupplierTerm {
            $data = $this->normalizePayload($payload);
            $this->guardPreferredRules($data);

            if ($data['preferred']) {
                $this->repository->clearPreferredForIngredient((int) $data['ingredient_id'], $term->id);
            }

            return $this->repository->update($term, $data);
        });
    }

    public function delete(IngredientSupplierTerm $term): void
    {
        $this->repository->delete($term);
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    private function normalizePayload(array $payload): array
    {
        $active = (bool) ($payload['active'] ?? true);
        $preferred = $active && (bool) ($payload['preferred'] ?? false);

        return [
            'ingredient_id' => (int) $payload['ingredient_id'],
            'supplier_id' => (int) $payload['supplier_id'],
            'lead_time_days' => $this->nullableInteger($payload['lead_time_days'] ?? null),
            'minimum_order_quantity' => $this->nullableDecimal($payload['minimum_order_quantity'] ?? null),
            'pack_size' => $this->nullableDecimal($payload['pack_size'] ?? null),
            'unit_cost_override' => $this->nullableDecimal($payload['unit_cost_override'] ?? null),
            'preferred' => $preferred,
            'active' => $active,
            'meta' => $this->normalizeMeta($payload['meta'] ?? null),
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    private function guardPreferredRules(array $data): void
    {
        if (! $data['active'] && $data['preferred']) {
            throw ValidationException::withMessages([
                'preferred' => 'Inaktív beszállítói feltétel nem lehet preferált.',
            ]);
        }
    }

    private function nullableInteger(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }

    private function nullableDecimal(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (string) $value;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function normalizeMeta(mixed $value): ?array
    {
        if ($value === null || $value === '' || $value === []) {
            return null;
        }

        if (\is_array($value)) {
            return $value;
        }

        $decoded = json_decode((string) $value, true);

        return \is_array($decoded) ? $decoded : null;
    }
}
