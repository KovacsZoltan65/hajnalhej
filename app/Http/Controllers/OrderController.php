<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function success(Request $request, Order $order): Response
    {
        $user = $request->user();
        $lastOrderId = (int) $request->session()->get('last_order_id', 0);

        $canView = $lastOrderId === $order->id
            || ($user !== null && ($user->isAdmin() || $order->user_id === $user->id));

        abort_unless($canView, 403);

        return Inertia::render('Orders/Success', [
            'order' => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'customer_name' => $order->customer_name,
                'customer_email' => $order->customer_email,
                'customer_phone' => $order->customer_phone,
                'pickup_date' => $order->pickup_date?->toDateString(),
                'pickup_time_slot' => $order->pickup_time_slot,
                'notes' => $order->notes,
                'total' => (float) $order->total,
                'currency' => $order->currency,
                'placed_at' => $order->placed_at?->toDateTimeString(),
            ],
        ]);
    }
}
