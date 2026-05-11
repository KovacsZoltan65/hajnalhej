<?php

namespace App\Services;

use App\Data\Suppliers\SupplierIndexData;
use App\Data\Suppliers\SupplierStoreData;
use App\Data\Suppliers\SupplierUpdateData;
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
    ) {}

    public function paginateForAdmin(SupplierIndexData $filters): LengthAwarePaginator
    {
        return $this->repository->paginateForAdmin($filters);
    }

    public function create(SupplierStoreData $payload, ?User $actor = null): Supplier
    {
        $supplier = $this->repository->create($payload->toPayload());
        $this->auditService->logSupplierCreated($supplier, $actor);

        return $supplier;
    }

    public function update(Supplier $supplier, SupplierUpdateData $payload, ?User $actor = null): Supplier
    {
        $before = ['supplier' => $supplier->toArray()];
        $updated = $this->repository->update($supplier, $payload->toPayload());
        $this->auditService->logSupplierUpdated($updated, $actor, $before, ['supplier' => $updated->toArray()]);

        return $updated;
    }

    public function delete(Supplier $supplier, ?User $actor = null): void
    {
        $this->auditService->logSupplierDeleted($supplier, $actor);
        $this->repository->delete($supplier);
    }
}
