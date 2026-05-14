<?php

declare(strict_types=1);

namespace App\Contracts\Export;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;

interface Exporter
{
    /**
     * @return array<int, string>
     */
    public function headings(): array;

    /**
     * @param  array<string, mixed>  $filters
     */
    public function query(array $filters): EloquentBuilder|QueryBuilder;

    /**
     * @return array<int, mixed>
     */
    public function map(mixed $row): array;

    /**
     * @param  array<string, mixed>  $filters
     */
    public function filename(array $filters = []): string;
}
