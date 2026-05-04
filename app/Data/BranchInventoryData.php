<?php

declare(strict_types=1);

namespace App\Data;

use App\Models\BranchInventory;
use Spatie\LaravelData\Attributes\Validation\BooleanType;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Attributes\Validation\Required;

class BranchInventoryData extends Data
{
    public function __construct()
    {
        //
    }
}