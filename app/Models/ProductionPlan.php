<?php

namespace App\Models;

use Database\Factories\ProductionPlanFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $plan_number
 * @property \Illuminate\Support\Carbon|null $target_at
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $target_ready_at
 * @property int $total_active_minutes
 * @property int $total_wait_minutes
 * @property int $total_recipe_minutes
 * @property \Illuminate\Support\Carbon|null $planned_start_at
 * @property bool $is_locked
 * @property string|null $notes
 * @property int|null $created_by
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
