<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $purchase_recommendation_id
 * @property int $ingredient_id
 * @property int|null $supplier_id
 * @property numeric $current_stock
 * @property numeric $forecast_demand
 * @property numeric $safety_stock
 * @property numeric $recommended_quantity
 * @property numeric|null $approved_quantity
 * @property string $unit
 * @property numeric|null $estimated_unit_cost
 * @property numeric|null $estimated_line_total
 * @property numeric|null $margin_impact
 * @property string|null $reason_code
 * @property array<array-key, mixed>|null $calculation_snapshot
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Ingredient|null $ingredient
 * @property-read \App\Models\PurchaseRecommendation|null $recommendation
 * @property-read \App\Models\Supplier|null $supplier
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseRecommendationItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseRecommendationItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseRecommendationItem query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseRecommendationItem whereApprovedQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseRecommendationItem whereCalculationSnapshot($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseRecommendationItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseRecommendationItem whereCurrentStock($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseRecommendationItem whereEstimatedLineTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseRecommendationItem whereEstimatedUnitCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseRecommendationItem whereForecastDemand($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseRecommendationItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseRecommendationItem whereIngredientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseRecommendationItem whereMarginImpact($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseRecommendationItem wherePurchaseRecommendationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseRecommendationItem whereReasonCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseRecommendationItem whereRecommendedQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseRecommendationItem whereSafetyStock($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseRecommendationItem whereSupplierId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseRecommendationItem whereUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseRecommendationItem whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class PurchaseRecommendationItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_recommendation_id',
        'ingredient_id',
        'supplier_id',
        'current_stock',
        'forecast_demand',
        'safety_stock',
        'recommended_quantity',
        'approved_quantity',
        'unit',
        'estimated_unit_cost',
        'estimated_line_total',
        'margin_impact',
        'reason_code',
        'calculation_snapshot',
    ];

    protected function casts(): array
    {
        return [
            'current_stock' => 'decimal:3',
            'forecast_demand' => 'decimal:3',
            'safety_stock' => 'decimal:3',
            'recommended_quantity' => 'decimal:3',
            'approved_quantity' => 'decimal:3',
            'estimated_unit_cost' => 'decimal:2',
            'estimated_line_total' => 'decimal:2',
            'margin_impact' => 'decimal:2',
            'calculation_snapshot' => 'array',
        ];
    }

    public function recommendation(): BelongsTo
    {
        return $this->belongsTo(PurchaseRecommendation::class, 'purchase_recommendation_id');
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
