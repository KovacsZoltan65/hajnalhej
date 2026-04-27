<?php

namespace App\Http\Controllers;

use App\Http\Requests\Checkout\PlaceOrderRequest;
use App\Services\CartService;
use App\Services\CheckoutService;
use App\Services\ConversionTrackingService;
use App\Support\ConversionEventRegistry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use RuntimeException;
use Inertia\Inertia;
use Inertia\Response;

class CheckoutController extends Controller
{
    /**
     * @param CartService $cartService
     * @param CheckoutService $checkoutService
     * @param ConversionTrackingService $conversionTrackingService
     */
    public function __construct(
        private readonly CartService $cartService,
        private readonly CheckoutService $checkoutService,
        private readonly ConversionTrackingService $conversionTrackingService,
    ) {}

    /**
     * @param Request $request
     * @return RedirectResponse|\Inertia\Response
     */
    public function index(Request $request): Response|RedirectResponse
    {
        if ($this->cartService->isEmpty()) {
            return redirect()->route('cart.index')->with('error', __('commerce.validation.empty_cart'));
        }

        $user = $request->user();

        $this->conversionTrackingService->trackBackendEvent(
            eventKey: ConversionEventRegistry::CHECKOUT_VIEWED,
            request: $request,
            funnel: 'checkout',
            step: 'view',
            metadata: [
                'cart_total' => (float) ($this->cartService->getCartPayload()['summary']['total'] ?? 0),
            ],
        );

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

    /**
     * @param PlaceOrderRequest $request
     * @return RedirectResponse
     */
    public function store(PlaceOrderRequest $request): RedirectResponse
    {
        $payload = $request->validated();

        $this->conversionTrackingService->trackBackendEvent(
            eventKey: ConversionEventRegistry::CHECKOUT_SUBMITTED,
            request: $request,
            funnel: 'checkout',
            step: 'submit',
            metadata: [
                'has_notes' => ! empty($payload['notes']),
                'pickup_date' => $payload['pickup_date'] ?? null,
                'pickup_time_slot' => $payload['pickup_time_slot'] ?? null,
            ],
        );

        try {
            $order = $this->checkoutService->placeOrder($payload, $request->user(), $request->session()->getId());
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
