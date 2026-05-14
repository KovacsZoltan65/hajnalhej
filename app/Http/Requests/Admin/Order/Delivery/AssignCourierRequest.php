<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Order\Delivery;

use App\Enums\Delivery\DeliveryStatus;
use App\Enums\Orders\FulfillmentMethod;
use App\Models\Courier;
use App\Models\Order;
use App\Support\PermissionRegistry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class AssignCourierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(PermissionRegistry::ORDERS_ASSIGN_COURIER) ?? false;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'courier_id' => [
                'required',
                'integer',
                Rule::exists('couriers', 'id')
                    ->where('status', Courier::STATUS_ACTIVE)
                    ->where('active', true)
                    ->whereNull('deleted_at'),
            ],
            'delivery_scheduled_at' => ['nullable', 'date'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $order = $this->route('order');

                if ($order === null) {
                    return;
                }

                if ($order->fulfillment_method !== FulfillmentMethod::DELIVERY->value) {
                    $validator->errors()->add('courier_id', __('delivery.errors.only_delivery_orders'));

                    return;
                }

                if (in_array($order->status, [Order::STATUS_COMPLETED, Order::STATUS_CANCELLED], true)) {
                    $validator->errors()->add('courier_id', __('delivery.errors.cannot_assign'));

                    return;
                }

                $deliveryStatus = $order->delivery_status === null
                    ? DeliveryStatus::PENDING
                    : DeliveryStatus::tryFrom($order->delivery_status);

                if ($deliveryStatus === null || ! $deliveryStatus->canAssignCourier()) {
                    $validator->errors()->add('courier_id', __('delivery.errors.cannot_assign'));
                }
            },
        ];
    }
}
