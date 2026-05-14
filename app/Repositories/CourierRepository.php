<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Data\Couriers\CourierIndexData;
use App\Models\Courier;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class CourierRepository
{
    public function paginateForAdmin(CourierIndexData $filters): LengthAwarePaginator
    {
        return $this->adminQuery($filters)
            ->paginate($filters->per_page)
            ->withQueryString();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Courier
    {
        return Courier::query()->create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Courier $courier, array $data): Courier
    {
        $courier->update($data);

        return $courier->refresh();
    }

    public function delete(Courier $courier): void
    {
        $courier->delete();
    }

    /**
     * @return Collection<int, Courier>
     */
    public function activeOptions(): Collection
    {
        return Courier::query()
            ->select(['id', 'name', 'phone', 'email', 'status', 'vehicle_type', 'active'])
            ->where('status', Courier::STATUS_ACTIVE)
            ->where('active', true)
            ->orderBy('name')
            ->orderBy('id')
            ->get();
    }

    private function adminQuery(CourierIndexData $filters): Builder
    {
        $query = Courier::query()
            ->when($filters->search !== null, function (Builder $query) use ($filters): void {
                $query->where(function (Builder $innerQuery) use ($filters): void {
                    $innerQuery
                        ->where('name', 'like', "%{$filters->search}%")
                        ->orWhere('email', 'like', "%{$filters->search}%")
                        ->orWhere('phone', 'like', "%{$filters->search}%");
                });
            })
            ->when($filters->status !== null, fn (Builder $query): Builder => $query->where('status', $filters->status));

        $query
            ->orderBy($filters->sort_field, $filters->sort_direction)
            ->orderBy('id');

        return $query;
    }
}
