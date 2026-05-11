<?php

declare(strict_types=1);

namespace App\Services;

use App\Data\Couriers\CourierIndexData;
use App\Data\Couriers\CourierStoreData;
use App\Data\Couriers\CourierUpdateData;
use App\Models\Courier;
use App\Repositories\CourierRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class CourierService
{
    public function __construct(private readonly CourierRepository $repository) {}

    public function paginateForAdmin(CourierIndexData $filters): LengthAwarePaginator
    {
        return $this->repository->paginateForAdmin($filters);
    }

    public function create(CourierStoreData $payload): Courier
    {
        return $this->repository->create($payload->toPayload());
    }

    public function update(Courier $courier, CourierUpdateData $payload): Courier
    {
        return $this->repository->update($courier, $payload->toPayload());
    }

    public function delete(Courier $courier): void
    {
        $this->repository->delete($courier);
    }

    /**
     * @return Collection<int, Courier>
     */
    public function activeOptions(): Collection
    {
        return $this->repository->activeOptions();
    }
}
