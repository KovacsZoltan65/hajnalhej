<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Export\ExportFormat;
use App\Enums\Export\ExportStatus;
use App\Enums\Export\ExportType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property ExportType $type
 * @property ExportFormat $format
 * @property ExportStatus $status
 * @property array<string, mixed>|null $filters
 * @property string|null $disk
 * @property string|null $path
 * @property string|null $filename
 * @property int|null $rows_total
 * @property int $created_by
 * @property Carbon|null $started_at
 * @property Carbon|null $finished_at
 * @property Carbon|null $expires_at
 * @property string|null $error_message
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $creator
 */
class ExportJob extends Model
{
    use HasFactory;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'type',
        'format',
        'status',
        'filters',
        'disk',
        'path',
        'filename',
        'rows_total',
        'created_by',
        'started_at',
        'finished_at',
        'expires_at',
        'error_message',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => ExportType::class,
            'format' => ExportFormat::class,
            'status' => ExportStatus::class,
            'filters' => 'array',
            'rows_total' => 'integer',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isDownloadable(): bool
    {
        return $this->status === ExportStatus::Completed
            && $this->path !== null
            && ($this->expires_at === null || $this->expires_at->isFuture());
    }
}
