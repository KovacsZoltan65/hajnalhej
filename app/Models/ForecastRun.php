<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $run_number
 * @property string $forecast_type
 * @property string $status
 * @property \Illuminate\Support\Carbon $period_start
 * @property \Illuminate\Support\Carbon $period_end
 * @property int $horizon_days
 * @property numeric|null $confidence_percent
 * @property array<array-key, mixed>|null $parameters
 * @property \Illuminate\Support\Carbon|null $started_at
 * @property \Illuminate\Support\Carbon|null $completed_at
 * @property int|null $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $creator
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ForecastSnapshot> $snapshots
 * @property-read int|null $snapshots_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ForecastRun newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ForecastRun newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ForecastRun query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ForecastRun whereCompletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ForecastRun whereConfidencePercent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ForecastRun whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ForecastRun whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ForecastRun whereForecastType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ForecastRun whereHorizonDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ForecastRun whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ForecastRun whereParameters($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ForecastRun wherePeriodEnd($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ForecastRun wherePeriodStart($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ForecastRun whereRunNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ForecastRun whereStartedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ForecastRun whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ForecastRun whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ForecastRun extends Model
{
    use HasFactory;

    protected $fillable = [
        'run_number',
        'forecast_type',
        'status',
        'period_start',
        'period_end',
        'horizon_days',
        'confidence_percent',
        'parameters',
        'started_at',
        'completed_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'period_start' => 'date',
            'period_end' => 'date',
            'horizon_days' => 'integer',
            'confidence_percent' => 'decimal:4',
            'parameters' => 'array',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function snapshots(): HasMany
    {
        return $this->hasMany(ForecastSnapshot::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
