<?php

declare(strict_types=1);

namespace App\Data\Ingredients;

use Spatie\LaravelData\Data;

class IngredientInlineUpdateData extends Data
{
    public function __construct(
        public string $field,
        public mixed $value,
    ) {}
}
