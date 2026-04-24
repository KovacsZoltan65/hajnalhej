<?php

namespace Database\Seeders\Concerns;

use App\Models\Ingredient;
use RuntimeException;

trait UsesSeededIngredients
{
    /**
     * @param array<int, string> $slugs
     * @return array<string, Ingredient>
     */
    private function seededIngredients(array $slugs): array
    {
        $ingredients = Ingredient::query()
            ->whereIn('slug', $slugs)
            ->get()
            ->keyBy('slug');

        $missing = array_values(array_filter(
            $slugs,
            static fn (string $slug): bool => ! $ingredients->has($slug),
        ));

        if ($missing !== []) {
            throw new RuntimeException('Hiányzó IngredientSeeder alapanyag(ok): '.implode(', ', $missing));
        }

        return $ingredients->all();
    }

    private function seededIngredient(string $slug): Ingredient
    {
        return $this->seededIngredients([$slug])[$slug];
    }
}
