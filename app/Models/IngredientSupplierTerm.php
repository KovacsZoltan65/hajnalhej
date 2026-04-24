<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IngredientSupplierTerm extends Model
{
    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'ingredient_id',
        'supplier_id',
        'lead_time_days',
        'minimum_order_quantity',
        'pack_size',
        'preferred',
        'unit_cost_override',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'lead_time_days' => 'integer',
            'minimum_order_quantity' => 'decimal:3',
            'pack_size' => 'decimal:3',
            'preferred' => 'boolean',
            'unit_cost_override' => 'decimal:4',
        ];
    }

    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }
}
