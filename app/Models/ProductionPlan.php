<?php

namespace App\Models;

use Database\Factories\ProductionPlanFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id Rekord azonosito
 * @property string $plan_number Tervezes egyedi azonositoja
 * @property \Illuminate\Support\Carbon $target_at Teljesites celido (mikorra legyen kesz)
 * @property string $status Allapot: draft|calculated|ready|archived
 * @property int $total_active_minutes Osszes aktiv munkaido percben
 * @property int $total_wait_minutes Osszes varakozasi ido percben
 * @property int $total_recipe_minutes Osszes receptido percben
 * @property \Illuminate\Support\Carbon|null $planned_start_at Javasolt kezdes a celidohoz visszaszamolva
 * @property bool $is_locked Lezaras jelzo: szerkesztheto-e a terv
 * @property string|null $notes Belso megjegyzes
 * @property int|null $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $creator
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ProductionPlanItem> $items
 * @property-read int|null $items_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ProductionPlanStep> $steps
 * @property-read int|null $steps_count
 * @method static \Database\Factories\ProductionPlanFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionPlan newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionPlan newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionPlan query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionPlan whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionPlan whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionPlan whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionPlan whereIsLocked($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionPlan whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionPlan wherePlanNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionPlan wherePlannedStartAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionPlan whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionPlan whereTargetAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionPlan whereTotalActiveMinutes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionPlan whereTotalRecipeMinutes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionPlan whereTotalWaitMinutes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionPlan whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ProductionPlan extends Model
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_CALCULATED = 'calculated';
    public const STATUS_READY = 'ready';
    public const STATUS_ARCHIVED = 'archived';

    /** @use HasFactory<ProductionPlanFactory> */
    use HasFactory;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'plan_number',
        'target_at',
        'status',
        'total_active_minutes',
        'total_wait_minutes',
        'total_recipe_minutes',
        'planned_start_at',
        'is_locked',
        'notes',
        'created_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'target_at' => 'datetime',
            'planned_start_at' => 'datetime',
            'total_active_minutes' => 'integer',
            'total_wait_minutes' => 'integer',
            'total_recipe_minutes' => 'integer',
            'is_locked' => 'boolean',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ProductionPlanItem::class)
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function steps(): HasMany
    {
        return $this->hasMany(ProductionPlanStep::class)
            ->orderBy('starts_at')
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    /**
     * @return array<int, string>
     */
    public static function statuses(): array
    {
        return [
            self::STATUS_DRAFT,
            self::STATUS_CALCULATED,
            self::STATUS_READY,
            self::STATUS_ARCHIVED,
        ];
    }
}
