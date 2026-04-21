<?php

namespace App\Models;

use Database\Factories\ProductionPlanItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductionPlanItem extends Model
{
    /** @use HasFactory<ProductionPlanItemFactory> */
    use HasFactory;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'production_plan_id',
        'product_id',
        'product_name_snapshot',
        'product_slug_snapshot',
        'target_quantity',
        'unit_label',
        'sort_order',
        'computed_ingredient_count',
        'computed_step_count',
        'computed_active_minutes',
        'computed_wait_minutes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'target_quantity' => 'decimal:3',
            'sort_order' => 'integer',
            'computed_ingredient_count' => 'integer',
            'computed_step_count' => 'integer',
            'computed_active_minutes' => 'integer',
            'computed_wait_minutes' => 'integer',
        ];
    }

    public function productionPlan(): BelongsTo
    {
        return $this->belongsTo(ProductionPlan::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}

