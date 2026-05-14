<?php

namespace App\Services;

use App\Data\Suppliers\SupplierIndexData;
use App\Data\Suppliers\SupplierStoreData;
use App\Data\Suppliers\SupplierUpdateData;
use App\Models\Supplier;
use App\Models\User;
use App\Repositories\SupplierRepository;
use App\Services\Audit\InventoryAuditService;
use App\Services\Cache\SelectorCacheInvalidator;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class SupplierService
{
    public function __construct(
        private readonly SupplierRepository $repository,
        private readonly InventoryAuditService $auditService,
        private readonly SelectorCacheInvalidator $selectorCacheInvalidator,
    ) {}

    public function paginateForAdmin(SupplierIndexData $filters): LengthAwarePaginator
    {
        return $this->repository->paginateForAdmin($filters);
    }

    /**
     * @return EloquentCollection<int, Supplier>
     */
    public function listSelectable(?bool $active = null): EloquentCollection
    {
        return $this->repository->listSelectable($active);
    }

    public function create(SupplierStoreData $payload, ?User $actor = null): Supplier
    {
        $supplier = $this->repository->create($payload->toPayload());
        $this->auditService->logSupplierCreated($supplier, $actor);
        $this->selectorCacheInvalidator->suppliers();

        return $supplier;
    }

    public function update(Supplier $supplier, SupplierUpdateData $payload, ?User $actor = null): Supplier
    {
        $before = ['supplier' => $supplier->toArray()];
        $updated = $this->repository->update($supplier, $payload->toPayload());
        $this->auditService->logSupplierUpdated($updated, $actor, $before, ['supplier' => $updated->toArray()]);
        $this->selectorCacheInvalidator->suppliers();

        return $updated;
    }

    public function delete(Supplier $supplier, ?User $actor = null): void
    {
        $this->auditService->logSupplierDeleted($supplier, $actor);
        $this->repository->delete($supplier);
        $this->selectorCacheInvalidator->suppliers();
    }
}
