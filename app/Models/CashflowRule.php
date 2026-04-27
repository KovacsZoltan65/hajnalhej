<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property string $rule_type
 * @property numeric|null $threshold_amount
 * @property numeric|null $warning_percent
 * @property int $lookahead_days
 * @property string $action
 * @property bool $active
 * @property int $priority
 * @property array<array-key, mixed>|null $conditions
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CashflowRule newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CashflowRule newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CashflowRule query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CashflowRule whereAction($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CashflowRule whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CashflowRule whereConditions($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CashflowRule whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CashflowRule whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CashflowRule whereLookaheadDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CashflowRule whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CashflowRule whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CashflowRule wherePriority($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CashflowRule whereRuleType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CashflowRule whereThresholdAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CashflowRule whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CashflowRule whereWarningPercent($value)
 * @mixin \Eloquent
 */
class CashflowRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'rule_type',
        'threshold_amount',
        'warning_percent',
        'lookahead_days',
        'action',
        'active',
        'priority',
        'conditions',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'threshold_amount' => 'decimal:2',
            'warning_percent' => 'decimal:4',
            'lookahead_days' => 'integer',
            'active' => 'boolean',
            'priority' => 'integer',
            'conditions' => 'array',
        ];
    }
}
