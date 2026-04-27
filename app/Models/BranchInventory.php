<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
