<?php

declare(strict_types=1);

namespace App\Data\Export;

use Spatie\LaravelData\Data;

class ExportResultData extends Data
{
    public function __construct(
        public string $disk,
        public string $path,
        public string $filename,
        public int $rowsTotal,
    ) {}
}
