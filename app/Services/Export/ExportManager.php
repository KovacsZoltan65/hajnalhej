<?php

declare(strict_types=1);

namespace App\Services\Export;

use App\Contracts\Export\Exporter;
use App\Data\Export\ExportRequestData;
use App\Enums\Export\ExportFormat;
use App\Enums\Export\ExportStatus;
use App\Enums\Export\ExportType;
use App\Exports\AuditLogsExport;
use App\Exports\IngredientsExport;
use App\Exports\InventoryMovementsExport;
use App\Exports\OrdersExport;
use App\Exports\ProductsExport;
use App\Models\ExportJob;
use App\Models\User;
use App\Repositories\ExportJobRepository;
use Throwable;

class ExportManager
{
    public function __construct(
        private readonly ExportJobRepository $repository,
        private readonly CsvExportService $csvExportService,
        private readonly XlsxExportService $xlsxExportService,
        private readonly ExportStorageService $storageService,
    ) {}

    public function createJob(ExportRequestData $data, User $user): ExportJob
    {
        $job = $this->repository->create($data, $user);
        $this->log('export.created', $job);

        return $job;
    }

    public function run(ExportJob $exportJob): ExportJob
    {
        $exportJob->update([
            'status' => ExportStatus::Running,
            'started_at' => now(),
            'error_message' => null,
        ]);
        $this->log('export.started', $exportJob);

        try {
            $exporter = $this->exporterFor($exportJob->type);
            $path = $this->storageService->relativePath(
                $exportJob->type,
                $exportJob->format,
                $exportJob->creator,
                $exporter->filename($exportJob->filters ?? []),
            );
            $absolutePath = $this->storageService->absolutePath($path);

            $rowsTotal = match ($exportJob->format) {
                ExportFormat::Csv => $this->csvExportService->write($exporter, $exportJob->filters ?? [], $absolutePath),
                ExportFormat::Xlsx => $this->xlsxExportService->write($exporter, $exportJob->filters ?? [], $absolutePath),
            };

            $exportJob->update([
                'status' => ExportStatus::Completed,
                'disk' => $this->storageService->disk(),
                'path' => $path,
                'filename' => basename($path),
                'rows_total' => $rowsTotal,
                'finished_at' => now(),
                'expires_at' => $exportJob->expires_at ?? now()->addDays(7),
            ]);
            $this->log('export.completed', $exportJob->refresh());

            return $exportJob;
        } catch (Throwable $throwable) {
            $exportJob->update([
                'status' => ExportStatus::Failed,
                'finished_at' => now(),
                'error_message' => str($throwable->getMessage())->limit(2000)->toString(),
            ]);
            $this->log('export.failed', $exportJob->refresh());

            throw $throwable;
        }
    }

    public function logDownloaded(ExportJob $exportJob): void
    {
        $this->log('export.downloaded', $exportJob);
    }

    private function exporterFor(ExportType $type): Exporter
    {
        $class = match ($type) {
            ExportType::Orders => OrdersExport::class,
            ExportType::Products => ProductsExport::class,
            ExportType::Ingredients => IngredientsExport::class,
            ExportType::InventoryMovements => InventoryMovementsExport::class,
            ExportType::AuditLogs => AuditLogsExport::class,
        };

        return app($class);
    }

    private function log(string $event, ExportJob $exportJob): void
    {
        activity('exports')
            ->event($event)
            ->performedOn($exportJob)
            ->causedBy($exportJob->creator)
            ->withProperties([
                'export_job_id' => $exportJob->id,
                'type' => $exportJob->type->value,
                'format' => $exportJob->format->value,
                'filename' => $exportJob->filename,
                'rows_total' => $exportJob->rows_total,
                'filters' => $exportJob->filters,
            ])
            ->log($event);
    }
}
