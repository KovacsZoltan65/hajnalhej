<?php

namespace App\Http\Controllers;

use App\Http\Requests\Checkout\PlaceOrderRequest;
use App\Services\CartService;
use App\Services\CheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use RuntimeException;
use Inertia\Inertia;
use Inertia\Response;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly CartService $cartService,
        private readonly CheckoutService $checkoutService,
    ) {
    }

    public function index(Request $request): Response|RedirectResponse
    {
        if ($this->cartService->isEmpty()) {
            return redirect()->route('cart.index')->with('error', __('commerce.validation.empty_cart'));
        }

        $user = $request->user();

        return Inertia::render('Checkout/Index', [
            'cart' => $this->cartService->getCartPayload(),
            'prefill' => [
                'customer_name' => (string) ($user?->name ?? ''),
                'customer_email' => (string) ($user?->email ?? ''),
                'customer_phone' => '',
                'notes' => '',
                'pickup_date' => null,
                'pickup_time_slot' => null,
            ],
        ]);
    }

    public function store(PlaceOrderRequest $request): RedirectResponse
    {
        try {
            $order = $this->checkoutService->placeOrder($request->validated(), $request->user());
        } catch (RuntimeException $exception) {
            return redirect()
                ->route('cart.index')
                ->with('error', $exception->getMessage());
        }

        $request->session()->put('last_order_id', $order->id);

        return redirect()
            ->route('orders.success', $order)
            ->with('success', __('commerce.orders.placed_success'));
    }
}
