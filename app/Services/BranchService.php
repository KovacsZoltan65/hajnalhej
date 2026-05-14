<?php

declare(strict_types=1);

namespace App\Services;

use App\Data\Branches\BranchIndexData;
use App\Data\Branches\BranchStoreData;
use App\Data\Branches\BranchUpdateData;
use App\Models\Branch;
use App\Repositories\BranchRepository;
use App\Services\Cache\SelectorCacheInvalidator;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class BranchService
{
    public function __construct(
        private readonly BranchRepository $repository,
        private readonly SelectorCacheInvalidator $selectorCacheInvalidator,
    ) {}

    public function paginateForAdmin(BranchIndexData $filters): LengthAwarePaginator
    {
        return $this->repository->paginateForAdmin($filters);
    }

    public function create(BranchStoreData $payload): Branch
    {
        $branch = $this->repository->create($payload->toPayload());

        $this->selectorCacheInvalidator->branches();

        return $branch;
    }

    public function update(Branch $branch, BranchUpdateData $payload): Branch
    {
        $branch = $this->repository->update($branch, $payload->toPayload());

        $this->selectorCacheInvalidator->branches();

        return $branch;
    }

    public function delete(Branch $branch): void
    {
        $this->repository->delete($branch);

        $this->selectorCacheInvalidator->branches();
    }

    /**
     * @return Collection<int, Branch>
     */
    public function activePickupOptions(): Collection
    {
        return $this->repository->activePickupOptions();
    }
}
