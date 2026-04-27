<?php

namespace App\Models;

use Database\Factories\ProductionPlanStepFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id Rekord azonosito
 * @property int $production_plan_id
 * @property int|null $production_plan_item_id
 * @property int|null $product_id
 * @property int|null $depends_on_product_id
 * @property string $title Timeline lepes megnevezese
 * @property string $step_type Lepes tipusa
 * @property string|null $description Lepes reszletes leirasa
 * @property string|null $work_instruction A vegrehajtando muvelet utasitasa (snapshot)
 * @property string|null $completion_criteria Lepes kesz allapotanak kriteriuma (snapshot)
 * @property string|null $attention_points Kritikus figyelmeztetesek (snapshot)
 * @property string|null $required_tools Szukseges eszkozok (snapshot)
 * @property string|null $expected_result Elvart eredmeny (snapshot)
 * @property \Illuminate\Support\Carbon $starts_at Lepes kezdete
 * @property \Illuminate\Support\Carbon $ends_at Lepes vege
 * @property int $duration_minutes Aktiv ido percben
 * @property int $wait_minutes Varakozasi ido percben
 * @property int $sort_order Globalis timeline sorrend
 * @property string|null $timeline_group Csoport azonosito (pl. termek vagy starter)
 * @property bool $is_dependency Dependency lepes-e (starter/kovasz)
 * @property array<array-key, mixed>|null $meta Kiegeszito technikai adatok
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Product|null $dependsOnProduct
 * @property-read \App\Models\Product|null $product
 * @property-read \App\Models\ProductionPlan $productionPlan
 * @property-read \App\Models\ProductionPlanItem|null $productionPlanItem
 * @method static \Database\Factories\ProductionPlanStepFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionPlanStep newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionPlanStep newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionPlanStep query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionPlanStep whereAttentionPoints($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionPlanStep whereCompletionCriteria($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionPlanStep whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionPlanStep whereDependsOnProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionPlanStep whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionPlanStep whereDurationMinutes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionPlanStep whereEndsAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionPlanStep whereExpectedResult($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionPlanStep whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionPlanStep whereIsDependency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionPlanStep whereMeta($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionPlanStep whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionPlanStep whereProductionPlanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionPlanStep whereProductionPlanItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionPlanStep whereRequiredTools($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionPlanStep whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionPlanStep whereStartsAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionPlanStep whereStepType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionPlanStep whereTimelineGroup($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionPlanStep whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionPlanStep whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionPlanStep whereWaitMinutes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionPlanStep whereWorkInstruction($value)
 * @mixin \Eloquent
 */
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
