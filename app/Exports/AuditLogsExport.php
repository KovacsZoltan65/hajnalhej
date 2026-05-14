<?php

declare(strict_types=1);

namespace App\Exports;

use App\Contracts\Export\Exporter;
use App\Exports\Concerns\FormatsExportValues;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Activitylog\Models\Activity;

class AuditLogsExport implements Exporter
{
    use FormatsExportValues;

    public function headings(): array
    {
        return ['ID', 'Log Name', 'Event', 'Causer', 'Subject Type', 'Subject ID', 'Created At', 'Properties JSON'];
    }

    public function query(array $filters): Builder
    {
        return Activity::query()
            ->select(['id', 'log_name', 'event', 'causer_type', 'causer_id', 'subject_type', 'subject_id', 'properties', 'created_at'])
            ->with('causer:id,name,email')
            ->when($filters['log_name'] ?? null, fn (Builder $query, string $logName): Builder => $query->where('log_name', $logName))
            ->when($filters['event'] ?? null, fn (Builder $query, string $event): Builder => $query->where('event', $event))
            ->when($filters['causer_id'] ?? null, fn (Builder $query, mixed $causerId): Builder => $query->where('causer_id', (int) $causerId))
            ->when($filters['subject_type'] ?? null, fn (Builder $query, string $subjectType): Builder => $query->where('subject_type', $subjectType))
            ->when($filters['date_from'] ?? null, fn (Builder $query, string $date): Builder => $query->whereDate('created_at', '>=', $date))
            ->when($filters['date_to'] ?? null, fn (Builder $query, string $date): Builder => $query->whereDate('created_at', '<=', $date))
            ->orderByDesc('id');
    }

    public function map(mixed $row): array
    {
        return [
            $row->id,
            $row->log_name,
            $row->event ?? '',
            $row->causer?->name ?? ($row->causer_id ? "{$row->causer_type} #{$row->causer_id}" : ''),
            $row->subject_type ?? '',
            $row->subject_id ?? '',
            $this->dateTime($row->created_at),
            $this->sanitizedJson($row->properties),
        ];
    }

    public function filename(array $filters = []): string
    {
        return 'audit_logs';
    }
}
