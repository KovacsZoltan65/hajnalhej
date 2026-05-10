<?php

declare(strict_types=1);

namespace App\Data\IngredientSupplierTerms;

use Spatie\LaravelData\Data;

class IngredientSupplierTermInlineUpdateData extends Data
{
    public function __construct(
        public string $field,
        public mixed $value,
    ) {}
}
