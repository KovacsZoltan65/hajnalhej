<?php

declare(strict_types=1);

namespace App\Data\Export;

use App\Enums\Export\ExportFormat;
use App\Enums\Export\ExportType;
use Spatie\LaravelData\Data;

class ExportRequestData extends Data
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function __construct(
        public ExportType $type,
        public ExportFormat $format,
        public array $filters = [],
    ) {}
}
