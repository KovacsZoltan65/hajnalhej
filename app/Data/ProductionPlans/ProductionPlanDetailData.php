<?php

declare(strict_types=1);

namespace App\Data\ProductionPlans;

use Spatie\LaravelData\Data;

class ProductionPlanDetailData extends Data
{
    /**
     * @param  array<int, ProductionPlanItemData>  $items
     * @param  array<int, ProductionPlanStepData>  $timeline_steps
     * @param  array<int, array<string, mixed>>  $ingredient_requirements
     * @param  array<string, mixed>  $summary
     */
    public function __construct(
        public int $id,
        public string $plan_number,
        public ?string $target_at,
        public ?string $target_ready_at,
        public string $status,
        public bool $is_locked,
        public ?string $notes,
        public int $total_active_minutes,
        public int $total_wait_minutes,
        public int $total_recipe_minutes,
        public ?string $planned_start_at,
        public int $items_count,
        public int $timeline_steps_count,
        public array $items,
        public array $timeline_steps,
        public array $ingredient_requirements,
        public array $summary,
    ) {}

    public static function fromArray(array $payload): self
    {
        return new self(
            id: (int) $payload['id'],
            plan_number: (string) $payload['plan_number'],
            target_at: $payload['target_at'] ?? null,
            target_ready_at: $payload['target_ready_at'] ?? null,
            status: (string) $payload['status'],
            is_locked: (bool) $payload['is_locked'],
            notes: $payload['notes'] ?? null,
            total_active_minutes: (int) $payload['total_active_minutes'],
            total_wait_minutes: (int) $payload['total_wait_minutes'],
            total_recipe_minutes: (int) $payload['total_recipe_minutes'],
            planned_start_at: $payload['planned_start_at'] ?? null,
            items_count: (int) $payload['items_count'],
            timeline_steps_count: (int) $payload['timeline_steps_count'],
            items: array_map(
                static fn (array $item): ProductionPlanItemData => ProductionPlanItemData::from($item),
                $payload['items'] ?? [],
            ),
            timeline_steps: array_map(
                static fn (array $step): ProductionPlanStepData => ProductionPlanStepData::from($step),
                $payload['timeline_steps'] ?? [],
            ),
            ingredient_requirements: $payload['ingredient_requirements'] ?? [],
            summary: $payload['summary'] ?? [],
        );
    }
}
