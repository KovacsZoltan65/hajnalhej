<?php

namespace App\Models;

use Database\Factories\ProductionPlanItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id Rekord azonosito
 * @property int $production_plan_id
 * @property int $product_id
 * @property string $product_name_snapshot Termek nev snapshot a terv idejen
 * @property string $product_slug_snapshot Termek slug snapshot a terv idejen
 * @property numeric $target_quantity Gyartando mennyiseg
 * @property string $unit_label Mennyiségi egyseg jeloles (pl. db)
 * @property int $sort_order Tetel sorrend a terven belul
 * @property int $computed_ingredient_count Szamitott hozzavalo sorok szama
 * @property int $computed_step_count Szamitott receptlépések szama
 * @property int $computed_active_minutes Szamitott aktiv ido percben
 * @property int $computed_wait_minutes Szamitott varakozasi ido percben
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Product|null $product
 * @property-read \App\Models\ProductionPlan $productionPlan
 * @method static \Database\Factories\ProductionPlanItemFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionPlanItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionPlanItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionPlanItem query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionPlanItem whereComputedActiveMinutes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionPlanItem whereComputedIngredientCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionPlanItem whereComputedStepCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionPlanItem whereComputedWaitMinutes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionPlanItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionPlanItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionPlanItem whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionPlanItem whereProductNameSnapshot($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionPlanItem whereProductSlugSnapshot($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionPlanItem whereProductionPlanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionPlanItem whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionPlanItem whereTargetQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionPlanItem whereUnitLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionPlanItem whereUpdatedAt($value)
 * @mixin \Eloquent
 */
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

