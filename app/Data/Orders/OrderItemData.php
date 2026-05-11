<?php

declare(strict_types=1);

namespace App\Data\Orders;

use App\Models\OrderItem;
use Spatie\LaravelData\Data;

class OrderItemData extends Data
{
    public function __construct(
        public int $id,
        public ?int $product_id,
        public string $product_name_snapshot,
        public float $unit_price,
        public int $quantity,
        public float $line_total,
    ) {}

    public static function fromModel(OrderItem $item): self
    {
        return new self(
            id: $item->id,
            product_id: $item->product_id,
            product_name_snapshot: $item->product_name_snapshot,
            unit_price: (float) $item->unit_price,
            quantity: (int) $item->quantity,
            line_total: (float) $item->line_total,
        );
    }
}
