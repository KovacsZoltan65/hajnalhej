<?php

namespace App\Services;

use App\Models\InventoryMovement;
use App\Repositories\IngredientRepository;
use App\Repositories\InventoryMovementRepository;

class InventoryDashboardService
{
    public function __construct(
        private readonly InventoryMovementRepository $movementRepository,
        private readonly IngredientRepository $ingredientRepository,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function dashboard(int $days): array
    {
        return [
            'days' => $days,
            'summary' => $this->movementRepository->dashboardSummary($days),
            'low_stock' => $this->movementRepository->lowStockIngredients(),
            'movement_types' => InventoryMovement::movementTypes(),
            'ingredient_options' => $this->ingredientRepository->listSelectableActive()->values()->all(),
        ];
    }
}

