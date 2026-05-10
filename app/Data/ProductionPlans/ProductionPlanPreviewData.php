<?php

declare(strict_types=1);

namespace App\Data\ProductionPlans;

use Spatie\LaravelData\Data;

class ProductionPlanPreviewData extends Data
{
    /**
     * @param  array<int, array<string, mixed>>  $ingredient_requirements
     * @param  array<int, ProductionPlanStepData>  $timeline_steps
     */
    public function __construct(
        public array $ingredient_requirements = [],
        public array $timeline_steps = [],
    ) {}
}
