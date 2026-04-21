<?php

namespace App\Repositories;

use Illuminate\Session\Store;

class SessionCartRepository
{
    private const SESSION_KEY = 'cart.items';

    public function __construct(private readonly Store $session)
    {
    }

    /**
     * @return array<int, array{product_id:int,quantity:int}>
     */
    public function all(): array
    {
        $items = $this->session->get(self::SESSION_KEY, []);

        if (! \is_array($items)) {
            return [];
        }

        return array_values(array_filter(array_map(function (mixed $item): ?array {
            if (! \is_array($item)) {
                return null;
            }

            $productId = (int) ($item['product_id'] ?? 0);
            $quantity = (int) ($item['quantity'] ?? 0);

            if ($productId <= 0 || $quantity <= 0) {
                return null;
            }

            return [
                'product_id' => $productId,
                'quantity' => $quantity,
            ];
        }, $items)));
    }

    public function upsert(int $productId, int $quantity): void
    {
        $items = $this->all();
        $updated = false;

        foreach ($items as &$item) {
            if ((int) $item['product_id'] === $productId) {
                $item['quantity'] = $quantity;
                $updated = true;
                break;
            }
        }

        if (! $updated) {
            $items[] = [
                'product_id' => $productId,
                'quantity' => $quantity,
            ];
        }

        $this->session->put(self::SESSION_KEY, $items);
    }

    public function remove(int $productId): void
    {
        $items = array_values(array_filter(
            $this->all(),
            static fn (array $item): bool => (int) $item['product_id'] !== $productId,
        ));

        $this->session->put(self::SESSION_KEY, $items);
    }

    public function clear(): void
    {
        $this->session->forget(self::SESSION_KEY);
    }
}
