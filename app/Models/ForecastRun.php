<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
