<?php

declare(strict_types=1);

namespace App\Services\Export;

use App\Contracts\Export\Exporter;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer;

class XlsxExportService
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function write(Exporter $exporter, array $filters, string $absolutePath): int
    {
        $writer = new Writer;
        $writer->openToFile($absolutePath);
        $writer->addRow(Row::fromValues($exporter->headings()));

        $rowsTotal = 0;
        $exporter->query($filters)->chunkById(500, function ($rows) use ($writer, $exporter, &$rowsTotal): void {
            foreach ($rows as $row) {
                $writer->addRow(Row::fromValues($exporter->map($row)));
                $rowsTotal++;
            }
        });

        $writer->close();

        return $rowsTotal;
    }
}
