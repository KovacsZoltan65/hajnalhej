<?php

declare(strict_types=1);

namespace App\Data\ProductionPlans;

use App\Models\ProductionPlanItem;
use Spatie\LaravelData\Data;

class ProductionPlanItemData extends Data
{
    public function __construct(
        public int $product_id,
        public int|float|string $target_quantity,
        public ?string $unit_label = null,
        public int $sort_order = 0,
        public ?string $product_name = null,
        public ?string $product_slug = null,
        public ?int $ingredient_count = null,
        public ?int $step_count = null,
        public ?int $total_active_minutes = null,
        public ?int $total_wait_minutes = null,
        public ?int $total_recipe_minutes = null,
        public ?string $suggested_start_at = null,
    ) {}

    public static function fromArray(array $payload): self
    {
        return new self(
            product_id: (int) $payload['product_id'],
            target_quantity: $payload['target_quantity'],
            unit_label: isset($payload['unit_label']) ? (string) $payload['unit_label'] : null,
            sort_order: (int) ($payload['sort_order'] ?? 0),
            product_name: isset($payload['product_name']) ? (string) $payload['product_name'] : null,
            product_slug: isset($payload['product_slug']) ? (string) $payload['product_slug'] : null,
            ingredient_count: isset($payload['ingredient_count']) ? (int) $payload['ingredient_count'] : null,
            step_count: isset($payload['step_count']) ? (int) $payload['step_count'] : null,
            total_active_minutes: isset($payload['total_active_minutes']) ? (int) $payload['total_active_minutes'] : null,
            total_wait_minutes: isset($payload['total_wait_minutes']) ? (int) $payload['total_wait_minutes'] : null,
            total_recipe_minutes: isset($payload['total_recipe_minutes']) ? (int) $payload['total_recipe_minutes'] : null,
            suggested_start_at: isset($payload['suggested_start_at']) ? (string) $payload['suggested_start_at'] : null,
        );
    }

    public static function fromModel(ProductionPlanItem $item): self
    {
        return new self(
            product_id: $item->product_id,
            target_quantity: (float) $item->target_quantity,
            unit_label: $item->unit_label,
            sort_order: $item->sort_order,
            product_name: $item->product_name_snapshot,
            product_slug: $item->product_slug_snapshot,
            ingredient_count: $item->computed_ingredient_count,
            step_count: $item->computed_step_count,
            total_active_minutes: $item->computed_active_minutes,
            total_wait_minutes: $item->computed_wait_minutes,
            total_recipe_minutes: $item->computed_active_minutes + $item->computed_wait_minutes,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toPayload(): array
    {
        return [
            'product_id' => $this->product_id,
            'target_quantity' => $this->target_quantity,
            'unit_label' => $this->unit_label,
            'sort_order' => $this->sort_order,
        ];
    }
}
