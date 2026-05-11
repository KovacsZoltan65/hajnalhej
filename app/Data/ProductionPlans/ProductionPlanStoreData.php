<?php

declare(strict_types=1);

namespace App\Data\ProductionPlans;

use App\Models\ProductionPlan;
use Spatie\LaravelData\Data;

class ProductionPlanStoreData extends Data
{
    /**
     * @param  array<int, ProductionPlanItemData>  $items
     */
    public function __construct(
        public string $target_ready_at,
        public array $items,
        public ?string $target_at = null,
        public string $status = ProductionPlan::STATUS_DRAFT,
        public ?string $notes = null,
    ) {}

    public static function fromArray(array $payload): self
    {
        $targetReadyAt = (string) ($payload['target_ready_at'] ?? $payload['target_at']);

        return new self(
            target_ready_at: $targetReadyAt,
            items: array_map(
                static fn (array $item): ProductionPlanItemData => ProductionPlanItemData::from($item),
                $payload['items'] ?? [],
            ),
            target_at: isset($payload['target_at']) ? (string) $payload['target_at'] : $targetReadyAt,
            status: (string) ($payload['status'] ?? ProductionPlan::STATUS_DRAFT),
            notes: isset($payload['notes']) ? (string) $payload['notes'] : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toPayload(): array
    {
        return [
            'target_ready_at' => $this->target_ready_at,
            'target_at' => $this->target_at ?? $this->target_ready_at,
            'status' => $this->status,
            'notes' => $this->notes,
            'items' => array_map(
                static fn (ProductionPlanItemData $item): array => $item->toPayload(),
                $this->items,
            ),
        ];
    }
}
