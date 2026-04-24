<?php

namespace App\Http\Controllers;

use App\Http\Requests\Cart\StoreCartItemRequest;
use App\Http\Requests\Cart\UpdateCartItemRequest;
use App\Services\CartService;
use App\Services\ConversionTrackingService;
use App\Support\ConversionEventRegistry;
use Illuminate\Http\RedirectResponse;
use RuntimeException;
use Inertia\Inertia;
use Inertia\Response;

class CartController extends Controller
{
    public function __construct(
        private readonly CartService $cartService,
        private readonly ConversionTrackingService $conversionTrackingService,
    ) {}

    /**
     * @return \Inertia\Response
     */
    public function index(): Response
    {
        $this->conversionTrackingService->trackBackendEvent(
            eventKey: ConversionEventRegistry::CART_VIEWED,
            request: request(),
            funnel: 'cart',
            step: 'view',
        );

        return Inertia::render('Cart/Index', [
            'cart' => $this->cartService->getCartPayload(),
        ]);
    }

    /**
     * @param StoreCartItemRequest $request
     * @return RedirectResponse
     */
    public function store(StoreCartItemRequest $request): RedirectResponse
    {
        $payload = $request->validated();

        try {
            $this->cartService->addProduct(
                (int) $payload['product_id'],
                (int) ($payload['quantity'] ?? 1),
            );
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        $this->conversionTrackingService->trackBackendEvent(
            eventKey: ConversionEventRegistry::CART_ITEM_ADDED,
            request: $request,
            funnel: 'cart',
            step: 'item_added',
            metadata: [
                'product_id' => (int) $payload['product_id'],
                'quantity' => (int) ($payload['quantity'] ?? 1),
            ],
        );

        return back()->with('success', __('commerce.cart.added'));
    }

    /**
     * @param UpdateCartItemRequest $request
     * @param int $productId
     * @return RedirectResponse
     */
    public function update(UpdateCartItemRequest $request, int $productId): RedirectResponse
    {
        try {
            $this->cartService->updateProductQuantity($productId, (int) $request->validated('quantity'));
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        $this->conversionTrackingService->trackBackendEvent(
            eventKey: ConversionEventRegistry::CART_ITEM_UPDATED,
            request: $request,
            funnel: 'cart',
            step: 'item_updated',
            metadata: [
                'product_id' => $productId,
                'quantity' => (int) $request->validated('quantity'),
            ],
        );

        return back()->with('success', __('commerce.cart.updated'));
    }

    /**
     * @param int $productId
     * @return RedirectResponse
     */
    public function destroy(int $productId): RedirectResponse
    {
        $this->cartService->removeProduct($productId);

        $this->conversionTrackingService->trackBackendEvent(
            eventKey: ConversionEventRegistry::CART_ITEM_REMOVED,
            request: request(),
            funnel: 'cart',
            step: 'item_removed',
            metadata: [
                'product_id' => $productId,
            ],
        );

        return back()->with('info', __('commerce.cart.removed'));
    }

    /**
     * @return RedirectResponse
     */
    public function clear(): RedirectResponse
    {
        $this->cartService->clear();

        $this->conversionTrackingService->trackBackendEvent(
            eventKey: ConversionEventRegistry::CART_CLEARED,
            request: request(),
            funnel: 'cart',
            step: 'clear',
        );

        return back()->with('info', __('commerce.cart.cleared'));
    }
}
