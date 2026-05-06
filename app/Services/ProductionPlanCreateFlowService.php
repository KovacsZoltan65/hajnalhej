<?php

namespace App\Services;

use App\Models\ProductionPlan;

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
}
