<?php

declare(strict_types=1);

namespace App\Data\ProductionPlans;

use App\Models\ProductionPlan;
use Spatie\LaravelData\Data;

class ProductionPlanListItemData extends Data
{
    /**
     * @param  array<int, ProductionPlanItemData>  $items
     * @param  array<string, mixed>  $details
     */
    public function __construct(
        public int $id,
        public string $plan_number,
        public ?string $target_at,
        public ?string $target_ready_at,
        public string $status,
        public bool $is_locked,
        public int $total_active_minutes,
        public int $total_wait_minutes,
        public int $total_recipe_minutes,
        public ?string $planned_start_at,
        public int $items_count,
        public array $items,
        public array $details,
    ) {}

    public static function fromModel(ProductionPlan $plan, array $details): self
    {
        return new self(
            id: $plan->id,
            plan_number: $plan->plan_number,
            target_at: $plan->target_at?->toDateTimeString(),
            target_ready_at: $plan->target_at?->toDateTimeString(),
            status: $plan->status,
            is_locked: $plan->is_locked,
            total_active_minutes: $plan->total_active_minutes,
            total_wait_minutes: $plan->total_wait_minutes,
            total_recipe_minutes: $plan->total_recipe_minutes,
            planned_start_at: $plan->planned_start_at?->toDateTimeString(),
            items_count: (int) ($plan->items_count ?? $plan->items->count()),
            items: $plan->items
                ->map(fn ($item): ProductionPlanItemData => ProductionPlanItemData::from($item))
                ->values()
                ->all(),
            details: $details,
        );
    }
}
