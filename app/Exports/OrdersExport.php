<?php

declare(strict_types=1);

namespace App\Exports;

use App\Contracts\Export\Exporter;
use App\Exports\Concerns\FormatsExportValues;
use App\Models\Order;
use Illuminate\Database\Eloquent\Builder;

class OrdersExport implements Exporter
{
    use FormatsExportValues;

    public function headings(): array
    {
        return [
            'Order Number',
            'Customer Name',
            'Customer Email',
            'Customer Phone',
            'Status',
            'Fulfillment Method',
            'Subtotal',
            'Delivery Fee',
            'Total',
            'Created At',
            'Completed At',
        ];
    }

    public function query(array $filters): Builder
    {
        return Order::query()
            ->select([
                'id',
                'order_number',
                'customer_name',
                'customer_email',
                'customer_phone',
                'status',
                'fulfillment_method',
                'subtotal',
                'delivery_fee',
                'total',
                'created_at',
                'completed_at',
            ])
            ->when($filters['status'] ?? null, fn (Builder $query, string $status): Builder => $query->where('status', $status))
            ->when($filters['fulfillment_method'] ?? null, fn (Builder $query, string $method): Builder => $query->where('fulfillment_method', $method))
            ->when($filters['date_from'] ?? null, fn (Builder $query, string $date): Builder => $query->whereDate('created_at', '>=', $date))
            ->when($filters['date_to'] ?? null, fn (Builder $query, string $date): Builder => $query->whereDate('created_at', '<=', $date))
            ->when($filters['search'] ?? null, function (Builder $query, string $search): void {
                $query->where(function (Builder $inner) use ($search): void {
                    $inner->where('order_number', 'like', "%{$search}%")
                        ->orWhere('customer_name', 'like', "%{$search}%")
                        ->orWhere('customer_email', 'like', "%{$search}%")
                        ->orWhere('customer_phone', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('id');
    }

    public function map(mixed $row): array
    {
        return [
            $row->order_number,
            $row->customer_name,
            $row->customer_email,
            $row->customer_phone,
            $this->humanStatus($row->status),
            $this->humanStatus($row->fulfillment_method),
            $this->money($row->subtotal),
            $this->money($row->delivery_fee),
            $this->money($row->total),
            $this->dateTime($row->created_at),
            $this->dateTime($row->completed_at),
        ];
    }

    public function filename(array $filters = []): string
    {
        return 'orders';
    }
}
