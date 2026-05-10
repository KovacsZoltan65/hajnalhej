<?php

namespace App\Data\Products;

use Spatie\LaravelData\Data;

class ProductInlineUpdateData extends Data
{
    public function __construct(
        public string $field,
        public mixed $value,
    ) {}
}
