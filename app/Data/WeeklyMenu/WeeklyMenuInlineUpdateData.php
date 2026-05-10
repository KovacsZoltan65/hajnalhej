<?php

declare(strict_types=1);

namespace App\Data\WeeklyMenu;

use Spatie\LaravelData\Data;

class WeeklyMenuInlineUpdateData extends Data
{
    public function __construct(
        public string $field,
        public mixed $value,
    ) {}
}
