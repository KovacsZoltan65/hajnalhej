<?php

namespace App\Repositories;

use App\Models\Order;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class OrderRepository
{
    /**
     * @param array<string, mixed> $filters
     */
    public function paginateForAdmin(array $filters): LengthAwarePaginator
    {
        $perPage = (int) ($filters['per_page'] ?? 15);

        return $this->adminQuery($filters)
            ->withCount('items')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * @param array<string, mixed> $data
     */
    public function createOrder(array $data): Order
    {
        return Order::query()->create($data);
    }

    /**
     * @param array<int, array<string, mixed>> $items
     */
    public function createItems(Order $order, array $items): void
    {
        $order->items()->createMany($items);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function update(Order $order, array $data): Order
    {
        $order->update($data);

        return $order->refresh();
    }

    public function nextDailySequence(CarbonInterface $date): int
    {
        $datePrefix = $date->format('Ymd');
        $prefix = "HH-{$datePrefix}-";

        $lastOrderNumber = DB::table('orders')
            ->where('order_number', 'like', $prefix.'%')
            ->lockForUpdate()
            ->orderByDesc('id')
            ->value('order_number');

        if (! \is_string($lastOrderNumber)) {
            return 1;
        }

        $sequence = (int) substr($lastOrderNumber, -4);

        return $sequence + 1;
    }

    /**
     * @param array<string, mixed> $filters
     */
    private function adminQuery(array $filters): Builder
    {
        $search = trim((string) ($filters['search'] ?? ''));
        $status = trim((string) ($filters['status'] ?? ''));
        $sortField = (string) ($filters['sort_field'] ?? 'placed_at');
        $sortDirection = (string) ($filters['sort_direction'] ?? 'desc');

        $sortable = ['placed_at', 'total', 'status', 'customer_name', 'pickup_date'];

        if (! \in_array($sortField, $sortable, true)) {
            $sortField = 'placed_at';
        }

        if (! \in_array($sortDirection, ['asc', 'desc'], true)) {
            $sortDirection = 'desc';
        }

        return Order::query()
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $inner) use ($search): void {
                    $inner
                        ->where('order_number', 'like', "%{$search}%")
                        ->orWhere('customer_name', 'like', "%{$search}%")
                        ->orWhere('customer_email', 'like', "%{$search}%")
                        ->orWhere('customer_phone', 'like', "%{$search}%");
                });
            })
            ->when($status !== '', fn (Builder $query): Builder => $query->where('status', $status))
            ->with('user:id,name,email')
            ->orderBy($sortField, $sortDirection)
            ->orderByDesc('id');
    }
}
