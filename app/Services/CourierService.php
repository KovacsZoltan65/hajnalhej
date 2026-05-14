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
        $courier = $this->repository->create($payload->toPayload());

        $this->log('courier.created', $courier);

        return $courier;
    }

    public function update(Courier $courier, CourierUpdateData $payload): Courier
    {
        $updated = $this->repository->update($courier, $payload->toPayload());

        $this->log('courier.updated', $updated);

        return $updated;
    }

    public function delete(Courier $courier): void
    {
        $this->repository->delete($courier);
        $this->log('courier.deleted', $courier);
    }

    /**
     * @return Collection<int, Courier>
     */
    public function activeOptions(): Collection
    {
        return $this->repository->activeOptions();
    }

    private function log(string $event, Courier $courier): void
    {
        activity('couriers')
            ->event($event)
            ->performedOn($courier)
            ->withProperties([
                'courier_id' => $courier->id,
                'status' => $courier->status,
            ])
            ->log($event);
    }
}
