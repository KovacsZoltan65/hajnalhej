<?php

namespace App\Services;

use App\Data\ProductionPlans\ProductionPlanStoreData;
use App\Models\ProductionPlan;
use Illuminate\Support\Carbon;

class ProductionPlanCreateFlowService
{
    public function __construct(private readonly ProductionPlanService $productionPlanService) {}

    public function create(ProductionPlanStoreData $data, int $userId): ProductionPlan
    {
        $payload = $data->toPayload();
        $payload['status'] = ProductionPlan::STATUS_CALCULATED;

        return $this->productionPlanService->create(ProductionPlanStoreData::from($payload), $userId);
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    public function calculateMinimumReadyAt(array $items): Carbon
    {
        return $this->productionPlanService->calculateMinimumReadyAt($items);
    }
}
