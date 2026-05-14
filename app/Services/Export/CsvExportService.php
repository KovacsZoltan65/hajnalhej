<?php

declare(strict_types=1);

namespace App\Services\Export;

use App\Contracts\Export\Exporter;
use League\Csv\Writer;
use SplFileObject;

class CsvExportService
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function write(Exporter $exporter, array $filters, string $absolutePath): int
    {
        $csv = Writer::createFromFileObject(new SplFileObject($absolutePath, 'w+'));
        $csv->setOutputBOM(Writer::BOM_UTF8);
        $csv->insertOne($exporter->headings());

        $rowsTotal = 0;
        $exporter->query($filters)->chunkById(500, function ($rows) use ($csv, $exporter, &$rowsTotal): void {
            foreach ($rows as $row) {
                $csv->insertOne($exporter->map($row));
                $rowsTotal++;
            }
        });

        return $rowsTotal;
    }
}
