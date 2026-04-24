<?php

namespace App\Services;

use App\Models\Supplier;
use App\Models\User;
use App\Repositories\SupplierRepository;
use App\Services\Audit\InventoryAuditService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class SupplierService
{
    public function __construct(
        private readonly SupplierRepository $repository,
        private readonly InventoryAuditService $auditService,
    ) {
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
    public function create(array $payload, ?User $actor = null): Supplier
    {
        $supplier = $this->repository->create($this->normalizePayload($payload));
        $this->auditService->logSupplierCreated($supplier, $actor);

        return $supplier;
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function update(Supplier $supplier, array $payload, ?User $actor = null): Supplier
    {
        $before = ['supplier' => $supplier->toArray()];
        $updated = $this->repository->update($supplier, $this->normalizePayload($payload));
        $this->auditService->logSupplierUpdated($updated, $actor, $before, ['supplier' => $updated->toArray()]);

        return $updated;
    }

    public function delete(Supplier $supplier, ?User $actor = null): void
    {
        $this->auditService->logSupplierDeleted($supplier, $actor);
        $this->repository->delete($supplier);
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    private function normalizePayload(array $payload): array
    {
        return [
            'name' => trim((string) ($payload['name'] ?? '')),
            'email' => $this->emptyToNull($payload['email'] ?? null),
            'phone' => $this->emptyToNull($payload['phone'] ?? null),
            'tax_number' => $this->emptyToNull($payload['tax_number'] ?? null),
            'notes' => $this->emptyToNull($payload['notes'] ?? null),
        ];
    }

    private function emptyToNull(mixed $value): ?string
    {
        $normalized = trim((string) $value);

        return $normalized === '' ? null : $normalized;
    }
}

