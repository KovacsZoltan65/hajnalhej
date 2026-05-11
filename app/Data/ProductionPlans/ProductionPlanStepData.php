<?php

declare(strict_types=1);

namespace App\Data\ProductionPlans;

use App\Models\ProductionPlanStep;
use Spatie\LaravelData\Data;

class ProductionPlanStepData extends Data
{
    public function __construct(
        public ?int $id,
        public string $title,
        public string $step_type,
        public ?string $description,
        public ?string $work_instruction,
        public ?string $completion_criteria,
        public ?string $attention_points,
        public ?string $required_tools,
        public ?string $expected_result,
        public ?string $starts_at,
        public ?string $ends_at,
        public int $duration_minutes,
        public int $wait_minutes,
        public int $sort_order,
        public ?string $timeline_group,
        public bool $is_dependency,
        public ?string $product_name = null,
        public ?string $depends_on_product_name = null,
    ) {}

    public static function fromArray(array $payload): self
    {
        return new self(
            id: isset($payload['id']) ? (int) $payload['id'] : null,
            title: (string) $payload['title'],
            step_type: (string) $payload['step_type'],
            description: $payload['description'] ?? null,
            work_instruction: $payload['work_instruction'] ?? null,
            completion_criteria: $payload['completion_criteria'] ?? null,
            attention_points: $payload['attention_points'] ?? null,
            required_tools: $payload['required_tools'] ?? null,
            expected_result: $payload['expected_result'] ?? null,
            starts_at: $payload['starts_at'] ?? null,
            ends_at: $payload['ends_at'] ?? null,
            duration_minutes: (int) ($payload['duration_minutes'] ?? 0),
            wait_minutes: (int) ($payload['wait_minutes'] ?? 0),
            sort_order: (int) ($payload['sort_order'] ?? 0),
            timeline_group: $payload['timeline_group'] ?? null,
            is_dependency: (bool) ($payload['is_dependency'] ?? false),
            product_name: $payload['product_name'] ?? null,
            depends_on_product_name: $payload['depends_on_product_name'] ?? null,
        );
    }

    public static function fromModel(ProductionPlanStep $step): self
    {
        return new self(
            id: $step->id,
            title: $step->title,
            step_type: $step->step_type,
            description: $step->description,
            work_instruction: $step->work_instruction,
            completion_criteria: $step->completion_criteria,
            attention_points: $step->attention_points,
            required_tools: $step->required_tools,
            expected_result: $step->expected_result,
            starts_at: $step->starts_at?->toDateTimeString(),
            ends_at: $step->ends_at?->toDateTimeString(),
            duration_minutes: $step->duration_minutes,
            wait_minutes: $step->wait_minutes,
            sort_order: $step->sort_order,
            timeline_group: $step->timeline_group,
            is_dependency: $step->is_dependency,
            product_name: $step->product?->name,
            depends_on_product_name: $step->dependsOnProduct?->name,
        );
    }
}
