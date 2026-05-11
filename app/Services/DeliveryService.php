<?php

declare(strict_types=1);

namespace App\Services;

use App\Data\Orders\DeliveryAssignData;
use App\Data\Orders\DeliveryFailedData;
use App\Enums\Delivery\DeliveryStatus;
use App\Enums\Orders\FulfillmentMethod;
use App\Models\Courier;
use App\Models\Order;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class DeliveryService
{
    public function assignCourier(Order $order, DeliveryAssignData $payload): Order
    {
        return DB::transaction(function () use ($order, $payload): Order {
            $order->refresh();
            $this->ensureDeliveryOrder($order);

            if (! $this->status($order)->canAssignCourier()) {
                throw new RuntimeException(__('delivery.errors.cannot_assign'));
            }

            $courier = Courier::query()->findOrFail($payload->courier_id);

            if (! $courier->active) {
                throw new RuntimeException(__('delivery.errors.inactive_courier'));
            }

            $order->update([
                'courier_id' => $courier->id,
                'delivery_status' => DeliveryStatus::ASSIGNED->value,
                'delivery_scheduled_at' => $payload->delivery_scheduled_at,
                'failed_delivery_reason' => null,
            ]);

            return $order->refresh()->load('courier');
        });
    }

    public function startDelivery(Order $order): Order
    {
        return DB::transaction(function () use ($order): Order {
            $order->refresh();
            $this->ensureDeliveryOrder($order);

            if (! $this->status($order)->canStartDelivery()) {
                throw new RuntimeException(__('delivery.errors.cannot_start'));
            }

            $order->update([
                'delivery_status' => DeliveryStatus::OUT_FOR_DELIVERY->value,
                'out_for_delivery_at' => Carbon::now(),
            ]);

            return $order->refresh()->load('courier');
        });
    }

    public function markDelivered(Order $order): Order
    {
        return DB::transaction(function () use ($order): Order {
            $order->refresh();
            $this->ensureDeliveryOrder($order);

            if (! $this->status($order)->canMarkDelivered()) {
                throw new RuntimeException(__('delivery.errors.cannot_mark_delivered'));
            }

            $order->update([
                'delivery_status' => DeliveryStatus::DELIVERED->value,
                'delivered_at' => Carbon::now(),
            ]);

            return $order->refresh()->load('courier');
        });
    }

    public function markFailed(Order $order, DeliveryFailedData $payload): Order
    {
        return DB::transaction(function () use ($order, $payload): Order {
            $order->refresh();
            $this->ensureDeliveryOrder($order);

            if (! $this->status($order)->canMarkFailed()) {
                throw new RuntimeException(__('delivery.errors.cannot_mark_failed'));
            }

            $reason = $payload->reason();

            if ($reason === '') {
                throw new RuntimeException(__('delivery.errors.failed_reason_required'));
            }

            $order->update([
                'delivery_status' => DeliveryStatus::FAILED->value,
                'failed_delivery_reason' => $reason,
                'delivered_at' => null,
            ]);

            return $order->refresh()->load('courier');
        });
    }

    public function cancelDelivery(Order $order): Order
    {
        return DB::transaction(function () use ($order): Order {
            $order->refresh();
            $this->ensureDeliveryOrder($order);

            if ($this->status($order)->isFinal()) {
                throw new RuntimeException(__('delivery.errors.final_status'));
            }

            $order->update([
                'delivery_status' => DeliveryStatus::CANCELLED->value,
            ]);

            return $order->refresh()->load('courier');
        });
    }

    private function ensureDeliveryOrder(Order $order): void
    {
        if ($order->fulfillment_method !== FulfillmentMethod::DELIVERY->value) {
            throw new RuntimeException(__('delivery.errors.only_delivery_orders'));
        }
    }

    private function status(Order $order): DeliveryStatus
    {
        if ($order->delivery_status === null) {
            return DeliveryStatus::PENDING;
        }

        return DeliveryStatus::from($order->delivery_status);
    }
}
