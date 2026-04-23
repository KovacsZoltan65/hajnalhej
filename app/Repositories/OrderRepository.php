<?php

namespace App\Repositories;

use App\Models\Order;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
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
     * @return array{revenue_total:float,orders_count:int,unique_customers:int,average_cart_value:float,repeat_customer_rate:float,ltv:float,repeat_customers:int}
     */
    public function commerceOverview(int $days): array
    {
        $orders = $this->analyticsBaseQuery($days)
            ->select(['id', 'user_id', 'customer_email', 'total'])
            ->get();

        $revenueTotal = round((float) $orders->sum(static fn (Order $order): float => (float) $order->total), 2);
        $ordersCount = $orders->count();
        $groups = $this->groupOrdersByCustomer($orders);
        $uniqueCustomers = $groups->count();
        $repeatCustomers = $groups->filter(static fn (Collection $items): bool => $items->count() >= 2)->count();

        return [
            'revenue_total' => $revenueTotal,
            'orders_count' => $ordersCount,
            'unique_customers' => $uniqueCustomers,
            'average_cart_value' => $ordersCount > 0 ? round($revenueTotal / $ordersCount, 2) : 0.0,
            'repeat_customers' => $repeatCustomers,
            'repeat_customer_rate' => $uniqueCustomers > 0 ? round(($repeatCustomers / $uniqueCustomers) * 100, 2) : 0.0,
            'ltv' => $uniqueCustomers > 0 ? round($revenueTotal / $uniqueCustomers, 2) : 0.0,
        ];
    }

    /**
     * @return array<int, array{date:string,revenue:float,orders_count:int,average_cart_value:float}>
     */
    public function dailyCommerceMetrics(int $days): array
    {
        $rows = $this->analyticsBaseQuery($days)
            ->selectRaw('DATE(placed_at) as metric_date, COUNT(*) as orders_count, SUM(total) as revenue_total')
            ->groupByRaw('DATE(placed_at)')
            ->orderByRaw('DATE(placed_at)')
            ->get();

        return $rows->map(static function (object $row): array {
            $ordersCount = (int) $row->orders_count;
            $revenue = round((float) $row->revenue_total, 2);

            return [
                'date' => (string) $row->metric_date,
                'revenue' => $revenue,
                'orders_count' => $ordersCount,
                'average_cart_value' => $ordersCount > 0 ? round($revenue / $ordersCount, 2) : 0.0,
            ];
        })->all();
    }

    /**
     * @return array<int, array{product_name:string,revenue:float,quantity:int,orders:int}>
     */
    public function topProductRevenue(int $days, int $limit = 10): array
    {
        $rows = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->selectRaw('order_items.product_name_snapshot as product_name, SUM(order_items.line_total) as revenue_total, SUM(order_items.quantity) as quantity_total, COUNT(DISTINCT orders.id) as orders_count')
            ->whereNotNull('orders.placed_at')
            ->where('orders.placed_at', '>=', now()->subDays($days))
            ->where('orders.status', '!=', Order::STATUS_CANCELLED)
            ->groupBy('order_items.product_name_snapshot')
            ->orderByDesc('revenue_total')
            ->limit($limit)
            ->get();

        return collect($rows)->map(static fn (object $row): array => [
            'product_name' => (string) $row->product_name,
            'revenue' => round((float) $row->revenue_total, 2),
            'quantity' => (int) $row->quantity_total,
            'orders' => (int) $row->orders_count,
        ])->all();
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

    private function analyticsBaseQuery(int $days): Builder
    {
        return Order::query()
            ->whereNotNull('placed_at')
            ->where('placed_at', '>=', now()->subDays($days))
            ->where('status', '!=', Order::STATUS_CANCELLED);
    }

    /**
     * @param Collection<int, Order> $orders
     * @return Collection<string, Collection<int, Order>>
     */
    private function groupOrdersByCustomer(Collection $orders): Collection
    {
        return $orders->groupBy(static function (Order $order): string {
            if ($order->user_id !== null) {
                return 'u:'.$order->user_id;
            }

            return 'e:'.mb_strtolower(trim((string) $order->customer_email));
        });
    }
}
