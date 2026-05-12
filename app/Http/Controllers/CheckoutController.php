<?php

namespace App\Http\Controllers;

use App\Data\Orders\OrderCheckoutData;
use App\Data\Orders\PickupBranchOptionData;
use App\Enums\Orders\FulfillmentMethod;
use App\Http\Requests\Checkout\PlaceOrderRequest;
use App\Services\BranchService;
use App\Services\CartService;
use App\Services\CheckoutService;
use App\Services\ConversionTrackingService;
use App\Support\ConversionEventRegistry;
use App\Support\InertiaPage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;
use RuntimeException;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly BranchService $branchService,
        private readonly CartService $cartService,
        private readonly CheckoutService $checkoutService,
        private readonly ConversionTrackingService $conversionTrackingService,
    ) {}

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

        return InertiaPage::CHECKOUT_INDEX->render([
            'cart' => $this->cartService->getCartPayload(),
            'fulfillmentOptions' => FulfillmentMethod::options(),
            'pickupBranches' => $this->branchService
                ->activePickupOptions()
                ->map(fn ($branch): PickupBranchOptionData => PickupBranchOptionData::fromModel($branch))
                ->values()
                ->all(),
            'prefill' => [
                'customer_name' => (string) ($user?->name ?? ''),
                'customer_email' => (string) ($user?->email ?? ''),
                'customer_phone' => '',
                'notes' => '',
                'pickup_date' => null,
                'pickup_time_slot' => null,
                'fulfillment_method' => FulfillmentMethod::PICKUP->value,
                'pickup_branch_id' => null,
                'billing_address' => $this->emptyAddressPrefill((string) ($user?->name ?? '')),
                'shipping_address' => $this->emptyAddressPrefill(),
                'same_as_billing' => true,
                'delivery_notes' => '',
            ],
        ]);
    }

    public function store(PlaceOrderRequest $request): RedirectResponse
    {
        $payload = $request->validated();
        $checkoutData = OrderCheckoutData::from($payload);

        $this->conversionTrackingService->trackBackendEvent(
            eventKey: ConversionEventRegistry::CHECKOUT_SUBMITTED,
            request: $request,
            funnel: 'checkout',
            step: 'submit',
            metadata: [
                'has_notes' => ! empty($payload['notes']),
                'fulfillment_method' => $checkoutData->fulfillment_method,
                'pickup_date' => $payload['pickup_date'] ?? null,
                'pickup_time_slot' => $payload['pickup_time_slot'] ?? null,
            ],
        );

        try {
            $order = $this->checkoutService->placeOrder($checkoutData, $request->user(), $request->session()->getId());
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

    /**
     * @return array<string, string|null>
     */
    private function emptyAddressPrefill(string $name = ''): array
    {
        return [
            'name' => $name,
            'country' => 'Magyarország',
            'postal_code' => '',
            'city' => '',
            'street' => '',
            'house_number' => '',
            'floor' => '',
            'door' => '',
            'company_name' => '',
            'tax_number' => '',
            'phone' => '',
            'notes' => '',
        ];
    }
}
