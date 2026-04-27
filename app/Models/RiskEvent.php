<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int|null $supplier_id
 * @property int|null $ingredient_id
 * @property string $event_type
 * @property string $severity
 * @property string $status
 * @property \Illuminate\Support\Carbon $event_date
 * @property numeric|null $estimated_impact_amount
 * @property numeric|null $probability_percent
 * @property string $title
 * @property string|null $description
 * @property string|null $mitigation_plan
 * @property \Illuminate\Support\Carbon|null $resolved_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Ingredient|null $ingredient
 * @property-read \App\Models\Supplier|null $supplier
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RiskEvent newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RiskEvent newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RiskEvent query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RiskEvent whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RiskEvent whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RiskEvent whereEstimatedImpactAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RiskEvent whereEventDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RiskEvent whereEventType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RiskEvent whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RiskEvent whereIngredientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RiskEvent whereMitigationPlan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RiskEvent whereProbabilityPercent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RiskEvent whereResolvedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RiskEvent whereSeverity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RiskEvent whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RiskEvent whereSupplierId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RiskEvent whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RiskEvent whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class RiskEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_id',
        'ingredient_id',
        'event_type',
        'severity',
        'status',
        'event_date',
        'estimated_impact_amount',
        'probability_percent',
        'title',
        'description',
        'mitigation_plan',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'event_date' => 'date',
            'estimated_impact_amount' => 'decimal:2',
            'probability_percent' => 'decimal:4',
            'resolved_at' => 'datetime',
        ];
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class);
    }
}
