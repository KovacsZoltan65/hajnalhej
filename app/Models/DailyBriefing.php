<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property \Illuminate\Support\Carbon $briefing_date
 * @property string $status
 * @property numeric $cash_needed_today
 * @property numeric $projected_procurement_total
 * @property int $open_alerts_count
 * @property int $critical_alerts_count
 * @property array<array-key, mixed>|null $summary
 * @property array<array-key, mixed>|null $recommended_actions
 * @property \Illuminate\Support\Carbon|null $generated_at
 * @property int|null $generated_by
 * @property \Illuminate\Support\Carbon|null $acknowledged_at
 * @property int|null $acknowledged_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $acknowledger
 * @property-read \App\Models\User|null $generator
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailyBriefing newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailyBriefing newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailyBriefing query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailyBriefing whereAcknowledgedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailyBriefing whereAcknowledgedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailyBriefing whereBriefingDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailyBriefing whereCashNeededToday($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailyBriefing whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailyBriefing whereCriticalAlertsCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailyBriefing whereGeneratedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailyBriefing whereGeneratedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailyBriefing whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailyBriefing whereOpenAlertsCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailyBriefing whereProjectedProcurementTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailyBriefing whereRecommendedActions($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailyBriefing whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailyBriefing whereSummary($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailyBriefing whereUpdatedAt($value)
 * @mixin \Eloquent
 */
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
