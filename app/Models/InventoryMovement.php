<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $ingredient_id
 * @property string $movement_type
 * @property string $direction
 * @property numeric $quantity
 * @property numeric|null $unit_cost
 * @property numeric|null $total_cost
 * @property \Illuminate\Support\Carbon $occurred_at
 * @property string|null $reference_type
 * @property int|null $reference_id
 * @property string|null $notes
 * @property int|null $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $creator
 * @property-read \App\Models\Ingredient|null $ingredient
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryMovement newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryMovement newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryMovement query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryMovement whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryMovement whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryMovement whereDirection($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryMovement whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryMovement whereIngredientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryMovement whereMovementType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryMovement whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryMovement whereOccurredAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryMovement whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryMovement whereReferenceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryMovement whereReferenceType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryMovement whereTotalCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryMovement whereUnitCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryMovement whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class InventoryMovement extends Model
{
    public const TYPE_PURCHASE_IN = 'purchase_in';
    public const TYPE_PRODUCTION_OUT = 'production_out';
    public const TYPE_WASTE_OUT = 'waste_out';
    public const TYPE_ADJUSTMENT_IN = 'adjustment_in';
    public const TYPE_ADJUSTMENT_OUT = 'adjustment_out';
    public const TYPE_COUNT_CORRECTION = 'count_correction';
    public const TYPE_RETURN_IN = 'return_in';
    public const TYPE_RETURN_OUT = 'return_out';

    public const DIRECTION_IN = 'in';
    public const DIRECTION_OUT = 'out';

    use HasFactory;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'ingredient_id',
        'movement_type',
        'direction',
        'quantity',
        'unit_cost',
        'total_cost',
        'occurred_at',
        'reference_type',
        'reference_id',
        'notes',
        'created_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:3',
            'unit_cost' => 'decimal:4',
            'total_cost' => 'decimal:2',
            'occurred_at' => 'datetime',
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function movementTypes(): array
    {
        return [
            self::TYPE_PURCHASE_IN,
            self::TYPE_PRODUCTION_OUT,
            self::TYPE_WASTE_OUT,
            self::TYPE_ADJUSTMENT_IN,
            self::TYPE_ADJUSTMENT_OUT,
            self::TYPE_COUNT_CORRECTION,
            self::TYPE_RETURN_IN,
            self::TYPE_RETURN_OUT,
        ];
    }

    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

