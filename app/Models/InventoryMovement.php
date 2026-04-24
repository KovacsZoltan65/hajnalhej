<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

