<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Data\Branches\BranchIndexData;
use App\Data\Branches\BranchType;
use App\Models\Branch;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class BranchRepository
{
    public function paginateForAdmin(BranchIndexData $filters): LengthAwarePaginator
    {
        return $this->adminQuery($filters)
            ->paginate($filters->per_page)
            ->withQueryString();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Branch
    {
        return Branch::query()->create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Branch $branch, array $data): Branch
    {
        $branch->update($data);

        return $branch->refresh();
    }

    public function delete(Branch $branch): void
    {
        $branch->delete();
    }

    /**
     * @return Collection<int, Branch>
     */
    public function activePickupOptions(): Collection
    {
        return Branch::query()
            ->select(['id', 'name', 'code', 'type', 'address'])
            ->where('active', true)
            ->whereIn('type', [
                BranchType::BAKERY,
                BranchType::SHOP,
                BranchType::PICKUP_POINT,
            ])
            ->orderBy('name')
            ->orderBy('id')
            ->get();
    }

    private function adminQuery(BranchIndexData $filters): Builder
    {
        $query = Branch::query()
            ->when($filters->search !== null, function (Builder $query) use ($filters): void {
                $query->where(function (Builder $innerQuery) use ($filters): void {
                    $innerQuery
                        ->where('name', 'like', "%{$filters->search}%")
                        ->orWhere('code', 'like', "%{$filters->search}%")
                        ->orWhere('email', 'like', "%{$filters->search}%")
                        ->orWhere('phone', 'like', "%{$filters->search}%")
                        ->orWhere('address', 'like', "%{$filters->search}%");
                });
            })
            ->when($filters->type !== null, fn (Builder $query): Builder => $query->where('type', $filters->type))
            ->when($filters->active !== null, fn (Builder $query): Builder => $query->where('active', $filters->active));

        $query
            ->orderBy($filters->sort_field, $filters->sort_direction)
            ->orderBy('id');

        return $query;
    }
}
