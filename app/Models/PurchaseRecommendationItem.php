<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
