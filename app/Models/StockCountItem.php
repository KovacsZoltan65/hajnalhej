<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $stock_count_id
 * @property int $ingredient_id
 * @property numeric $expected_quantity
 * @property numeric $counted_quantity
 * @property numeric $difference
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Ingredient|null $ingredient
 * @property-read \App\Models\StockCount $stockCount
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockCountItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockCountItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockCountItem query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockCountItem whereCountedQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockCountItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockCountItem whereDifference($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockCountItem whereExpectedQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockCountItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockCountItem whereIngredientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockCountItem whereStockCountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockCountItem whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class StockCountItem extends Model
{
    use HasFactory;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'stock_count_id',
        'ingredient_id',
        'expected_quantity',
        'counted_quantity',
        'difference',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'expected_quantity' => 'decimal:3',
            'counted_quantity' => 'decimal:3',
            'difference' => 'decimal:3',
        ];
    }

    public function stockCount(): BelongsTo
    {
        return $this->belongsTo(StockCount::class);
    }

    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class);
    }
}

