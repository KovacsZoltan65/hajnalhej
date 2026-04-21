<?php

namespace App\Http\Controllers;

use App\Http\Requests\Cart\StoreCartItemRequest;
use App\Http\Requests\Cart\UpdateCartItemRequest;
use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use RuntimeException;
use Inertia\Inertia;
use Inertia\Response;

class CartController extends Controller
{
    public function __construct(private readonly CartService $cartService)
    {
    }

    public function index(): Response
    {
        return Inertia::render('Cart/Index', [
            'cart' => $this->cartService->getCartPayload(),
        ]);
    }

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

        return back()->with('success', __('commerce.cart.added'));
    }

    public function update(UpdateCartItemRequest $request, int $productId): RedirectResponse
    {
        try {
            $this->cartService->updateProductQuantity($productId, (int) $request->validated('quantity'));
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', __('commerce.cart.updated'));
    }

    public function destroy(int $productId): RedirectResponse
    {
        $this->cartService->removeProduct($productId);

        return back()->with('info', __('commerce.cart.removed'));
    }

    public function clear(): RedirectResponse
    {
        $this->cartService->clear();

        return back()->with('info', __('commerce.cart.cleared'));
    }
}
