<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int|null $ingredient_id
 * @property int|null $supplier_id
 * @property string $alert_type
 * @property string $severity
 * @property string $status
 * @property \Illuminate\Support\Carbon $alert_date
 * @property numeric|null $quantity_gap
 * @property numeric|null $estimated_cash_impact
 * @property string $title
 * @property string|null $message
 * @property array<array-key, mixed>|null $context
 * @property \Illuminate\Support\Carbon|null $resolved_at
 * @property int|null $resolved_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Ingredient|null $ingredient
 * @property-read \App\Models\User|null $resolver
 * @property-read \App\Models\Supplier|null $supplier
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProcurementAlert newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProcurementAlert newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProcurementAlert query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProcurementAlert whereAlertDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProcurementAlert whereAlertType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProcurementAlert whereContext($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProcurementAlert whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProcurementAlert whereEstimatedCashImpact($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProcurementAlert whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProcurementAlert whereIngredientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProcurementAlert whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProcurementAlert whereQuantityGap($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProcurementAlert whereResolvedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProcurementAlert whereResolvedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProcurementAlert whereSeverity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProcurementAlert whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProcurementAlert whereSupplierId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProcurementAlert whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProcurementAlert whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ProcurementAlert extends Model
{
    use HasFactory;

    protected $fillable = [
        'ingredient_id',
        'supplier_id',
        'alert_type',
        'severity',
        'status',
        'alert_date',
        'quantity_gap',
        'estimated_cash_impact',
        'title',
        'message',
        'context',
        'resolved_at',
        'resolved_by',
    ];

    protected function casts(): array
    {
        return [
            'alert_date' => 'date',
            'quantity_gap' => 'decimal:3',
            'estimated_cash_impact' => 'decimal:2',
            'context' => 'array',
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

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }
}
