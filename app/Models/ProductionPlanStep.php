<?php

namespace App\Models;

use Database\Factories\ProductionPlanStepFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductionPlanStep extends Model
{
    /** @use HasFactory<ProductionPlanStepFactory> */
    use HasFactory;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'production_plan_id',
        'production_plan_item_id',
        'product_id',
        'depends_on_product_id',
        'title',
        'step_type',
        'description',
        'work_instruction',
        'completion_criteria',
        'attention_points',
        'required_tools',
        'expected_result',
        'starts_at',
        'ends_at',
        'duration_minutes',
        'wait_minutes',
        'sort_order',
        'timeline_group',
        'is_dependency',
        'meta',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'duration_minutes' => 'integer',
            'wait_minutes' => 'integer',
            'sort_order' => 'integer',
            'is_dependency' => 'boolean',
            'meta' => 'array',
        ];
    }

    public function productionPlan(): BelongsTo
    {
        return $this->belongsTo(ProductionPlan::class);
    }

    public function productionPlanItem(): BelongsTo
    {
        return $this->belongsTo(ProductionPlanItem::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function dependsOnProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'depends_on_product_id');
    }
}
