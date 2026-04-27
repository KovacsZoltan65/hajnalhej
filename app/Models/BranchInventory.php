<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Branch level ingredient stock.
 *
 * @property int $id
 * @property int $branch_id
 * @property int $ingredient_id
 * @property numeric $current_stock
 * @property numeric $reserved_stock
 * @property numeric $minimum_stock
 * @property numeric $reorder_point
 * @property numeric|null $target_stock
 * @property Carbon|null $last_counted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read \App\Models\Branch|null $branch
 * @property-read \App\Models\Ingredient|null $ingredient
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BranchInventory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BranchInventory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BranchInventory query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BranchInventory whereBranchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BranchInventory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BranchInventory whereCurrentStock($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BranchInventory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BranchInventory whereIngredientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BranchInventory whereLastCountedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BranchInventory whereMinimumStock($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BranchInventory whereReorderPoint($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BranchInventory whereReservedStock($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BranchInventory whereTargetStock($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BranchInventory whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class BranchInventory extends Model
{
    use HasFactory;

    protected $table = 'branch_inventory';

    protected $fillable = [
        'branch_id',
        'ingredient_id',
        'current_stock',
        'reserved_stock',
        'minimum_stock',
        'reorder_point',
        'target_stock',
        'last_counted_at',
    ];

    protected function casts(): array
    {
        return [
            'current_stock' => 'decimal:3',
            'reserved_stock' => 'decimal:3',
            'minimum_stock' => 'decimal:3',
            'reorder_point' => 'decimal:3',
            'target_stock' => 'decimal:3',
            'last_counted_at' => 'datetime',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class);
    }
}
