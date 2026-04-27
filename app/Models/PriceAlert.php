<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $ingredient_id
 * @property int|null $supplier_id
 * @property string $alert_type
 * @property string $status
 * @property numeric|null $previous_unit_cost
 * @property numeric $current_unit_cost
 * @property numeric|null $change_percent
 * @property numeric|null $margin_impact
 * @property \Illuminate\Support\Carbon $detected_on
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $resolved_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Ingredient|null $ingredient
 * @property-read \App\Models\Supplier|null $supplier
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceAlert newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceAlert newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceAlert query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceAlert whereAlertType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceAlert whereChangePercent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceAlert whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceAlert whereCurrentUnitCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceAlert whereDetectedOn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceAlert whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceAlert whereIngredientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceAlert whereMarginImpact($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceAlert whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceAlert wherePreviousUnitCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceAlert whereResolvedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceAlert whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceAlert whereSupplierId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceAlert whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class PriceAlert extends Model
{
    use HasFactory;

    protected $fillable = [
        'ingredient_id',
        'supplier_id',
        'alert_type',
        'status',
        'previous_unit_cost',
        'current_unit_cost',
        'change_percent',
        'margin_impact',
        'detected_on',
        'notes',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'previous_unit_cost' => 'decimal:2',
            'current_unit_cost' => 'decimal:2',
            'change_percent' => 'decimal:4',
            'margin_impact' => 'decimal:2',
            'detected_on' => 'date',
            'resolved_at' => 'datetime',
        ];
    }

    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }
}
