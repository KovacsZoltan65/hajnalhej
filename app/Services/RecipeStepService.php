<?php

namespace App\Services;

use App\Models\Product;
use App\Models\RecipeStep;
use App\Repositories\RecipeStepRepository;

class RecipeStepService
{
    public function __construct(private readonly RecipeStepRepository $repository)
    {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function create(Product $product, array $payload): RecipeStep
    {
        return $this->repository->create($product, $this->normalizePayload($payload));
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function update(Product $product, RecipeStep $recipeStep, array $payload): RecipeStep
    {
        if ($recipeStep->product_id !== $product->id) {
            abort(404);
        }

        return $this->repository->update($recipeStep, $this->normalizePayload($payload));
    }

    public function delete(Product $product, RecipeStep $recipeStep): void
    {
        if ($recipeStep->product_id !== $product->id) {
            abort(404);
        }

        $this->repository->delete($recipeStep);
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    private function normalizePayload(array $payload): array
    {
        return [
            'title' => trim((string) $payload['title']),
            'step_type' => (string) $payload['step_type'],
            'description' => $payload['description'] ?? null,
            'work_instruction' => $payload['work_instruction'] ?? null,
            'completion_criteria' => $payload['completion_criteria'] ?? null,
            'attention_points' => $payload['attention_points'] ?? null,
            'required_tools' => $payload['required_tools'] ?? null,
            'expected_result' => $payload['expected_result'] ?? null,
            'duration_minutes' => $payload['duration_minutes'] !== null ? (int) $payload['duration_minutes'] : null,
            'wait_minutes' => $payload['wait_minutes'] !== null ? (int) $payload['wait_minutes'] : null,
            'temperature_celsius' => $payload['temperature_celsius'] !== null ? number_format((float) $payload['temperature_celsius'], 1, '.', '') : null,
            'sort_order' => (int) ($payload['sort_order'] ?? 0),
            'is_active' => (bool) ($payload['is_active'] ?? true),
        ];
    }
}
