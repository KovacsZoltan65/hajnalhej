<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Repositories\OrderRepository;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class CheckoutService
{
    public function __construct(
        private readonly CartService $cartService,
        private readonly OrderRepository $orderRepository,
        private readonly OrderService $orderService,
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function placeOrder(array $payload, ?User $user): Order
    {
        $lines = $this->cartService->getCheckoutLines();

        if ($lines->isEmpty()) {
            throw new RuntimeException(__('commerce.validation.empty_cart'));
        }

        $placedOrder = DB::transaction(function () use ($payload, $user, $lines): Order {
            $orderNumber = $this->generateOrderNumber();
            $lineSnapshots = $this->buildLineSnapshots($lines);
            $subtotal = $lineSnapshots->sum(fn (array $line): float => (float) $line['line_total']);

            $order = $this->orderRepository->createOrder([
                'order_number' => $orderNumber,
                'user_id' => $user?->id,
                'customer_name' => trim((string) $payload['customer_name']),
                'customer_email' => mb_strtolower(trim((string) $payload['customer_email'])),
                'customer_phone' => trim((string) $payload['customer_phone']),
                'status' => Order::STATUS_PENDING,
                'currency' => 'HUF',
                'subtotal' => round($subtotal, 2),
                'total' => round($subtotal, 2),
                'notes' => $payload['notes'] ?? null,
                'pickup_date' => $payload['pickup_date'] ?? null,
                'pickup_time_slot' => $payload['pickup_time_slot'] ?? null,
                'placed_at' => Carbon::now(),
                'metadata' => $this->orderService->buildFutureProductionMetadata($lineSnapshots->all()),
            ]);

            $this->orderRepository->createItems($order, $lineSnapshots->all());

            return $order->load(['items', 'user']);
        });

        $this->cartService->clear();

        return $placedOrder;
    }

    private function generateOrderNumber(): string
    {
        $today = Carbon::today();
        $sequence = $this->orderRepository->nextDailySequence($today);

        return sprintf('HH-%s-%04d', $today->format('Ymd'), $sequence);
    }

    /**
     * @param Collection<int, array<string, mixed>> $lines
     * @return Collection<int, array<string, mixed>>
     */
    private function buildLineSnapshots(Collection $lines): Collection
    {
        $products = Product::query()
            ->with(['productIngredients.ingredient:id,name,unit'])
            ->whereIn('id', $lines->pluck('product_id')->map(fn (mixed $id): int => (int) $id)->all())
            ->get()
            ->keyBy('id');

        return $lines->map(function (array $line) use ($products): array {
            $product = $products->get((int) $line['product_id']);

            if (! $product instanceof Product) {
                throw new RuntimeException(__('commerce.validation.product_not_orderable'));
            }

            $unitPrice = round((float) $line['unit_price'], 2);
            $quantity = (int) $line['quantity'];
            $lineTotal = round($unitPrice * $quantity, 2);

            return [
                'product_id' => $product->id,
                'product_name_snapshot' => $product->name,
                'unit_price' => $unitPrice,
                'quantity' => $quantity,
                'line_total' => $lineTotal,
                'recipe_snapshot' => $product->productIngredients
                    ->map(fn ($ingredient): array => [
                        'ingredient_id' => $ingredient->ingredient_id,
                        'ingredient_name' => (string) ($ingredient->ingredient?->name ?? ''),
                        'unit' => (string) ($ingredient->ingredient?->unit ?? ''),
                        'quantity' => (float) $ingredient->quantity,
                    ])
                    ->values()
                    ->all(),
                'metadata' => [
                    'stock_status_at_order' => $product->stock_status,
                ],
            ];
        });
    }
}
