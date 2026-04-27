<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyBriefing extends Model
{
    use HasFactory;

    protected $fillable = [
        'briefing_date',
        'status',
        'cash_needed_today',
        'projected_procurement_total',
        'open_alerts_count',
        'critical_alerts_count',
        'summary',
        'recommended_actions',
        'generated_at',
        'generated_by',
        'acknowledged_at',
        'acknowledged_by',
    ];

    protected function casts(): array
    {
        return [
            'briefing_date' => 'date',
            'cash_needed_today' => 'decimal:2',
            'projected_procurement_total' => 'decimal:2',
            'open_alerts_count' => 'integer',
            'critical_alerts_count' => 'integer',
            'summary' => 'array',
            'recommended_actions' => 'array',
            'generated_at' => 'datetime',
            'acknowledged_at' => 'datetime',
        ];
    }

    public function generator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    public function acknowledger(): BelongsTo
    {
        return $this->belongsTo(User::class, 'acknowledged_by');
    }
}
