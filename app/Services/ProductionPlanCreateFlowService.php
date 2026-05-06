<?php

namespace App\Services;

use App\Models\ProductionPlan;
use Illuminate\Support\Carbon;

class ProductionPlanCreateFlowService
{
    public function __construct(private readonly ProductionPlanService $productionPlanService) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public function create(array $payload, int $userId): ProductionPlan
    {
        $payload['status'] = ProductionPlan::STATUS_CALCULATED;

        return $this->productionPlanService->create($payload, $userId);
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    public function calculateMinimumReadyAt(array $items): Carbon
    {
        return $this->productionPlanService->calculateMinimumReadyAt($items);
    }
}
