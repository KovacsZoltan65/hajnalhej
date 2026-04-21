<?php

namespace App\Services;

use App\Models\Product;
use App\Repositories\ProductRepository;
use App\Repositories\SessionCartRepository;
use Illuminate\Support\Collection;
use RuntimeException;

class CartService
{
    public function __construct(
        private readonly SessionCartRepository $cartRepository,
        private readonly ProductRepository $productRepository,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function getCartPayload(): array
    {
        $rawItems = $this->cartRepository->all();

        if ($rawItems === []) {
            return $this->emptyPayload();
        }

        $products = $this->productRepository
            ->findOrderableByIds(array_map(static fn (array $item): int => (int) $item['product_id'], $rawItems))
            ->keyBy('id');

        $items = [];

        foreach ($rawItems as $rawItem) {
            $product = $products->get((int) $rawItem['product_id']);

            if (! $product instanceof Product) {
                continue;
            }

            $quantity = max(1, (int) $rawItem['quantity']);
            $unitPrice = (float) $product->price;

            $items[] = [
                'product_id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'short_description' => $product->short_description,
                'unit_price' => $unitPrice,
                'quantity' => $quantity,
                'line_total' => $unitPrice * $quantity,
            ];
        }

        if ($items === []) {
            $this->cartRepository->clear();

            return $this->emptyPayload();
        }

        $subtotal = array_reduce($items, static fn (float $carry, array $item): float => $carry + (float) $item['line_total'], 0.0);
        $totalQuantity = array_reduce($items, static fn (int $carry, array $item): int => $carry + (int) $item['quantity'], 0);

        return [
            'items' => $items,
            'summary' => [
                'items_count' => count($items),
                'total_quantity' => $totalQuantity,
                'subtotal' => round($subtotal, 2),
                'total' => round($subtotal, 2),
                'currency' => 'HUF',
                'is_empty' => false,
            ],
        ];
    }

    public function addProduct(int $productId, int $quantity = 1): void
    {
        $cart = $this->getCartPayload();

        $existing = collect($cart['items'])
            ->firstWhere('product_id', $productId);

        $nextQuantity = (int) ($existing['quantity'] ?? 0) + max(1, $quantity);
        $nextQuantity = min($nextQuantity, 99);

        $this->assertOrderableProductExists($productId);
        $this->cartRepository->upsert($productId, $nextQuantity);
    }

    public function updateProductQuantity(int $productId, int $quantity): void
    {
        if ($quantity <= 0) {
            $this->cartRepository->remove($productId);

            return;
        }

        $safeQuantity = min(max($quantity, 1), 99);

        $this->assertOrderableProductExists($productId);
        $this->cartRepository->upsert($productId, $safeQuantity);
    }

    public function removeProduct(int $productId): void
    {
        $this->cartRepository->remove($productId);
    }

    public function clear(): void
    {
        $this->cartRepository->clear();
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function getCheckoutLines(): Collection
    {
        return collect($this->getCartPayload()['items']);
    }

    public function isEmpty(): bool
    {
        return (bool) $this->getCartPayload()['summary']['is_empty'];
    }

    private function assertOrderableProductExists(int $productId): void
    {
        $found = $this->productRepository->findOrderableByIds([$productId]);

        if ($found->isEmpty()) {
            throw new RuntimeException(__('commerce.validation.product_not_orderable'));
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function emptyPayload(): array
    {
        return [
            'items' => [],
            'summary' => [
                'items_count' => 0,
                'total_quantity' => 0,
                'subtotal' => 0.0,
                'total' => 0.0,
                'currency' => 'HUF',
                'is_empty' => true,
            ],
        ];
    }
}
