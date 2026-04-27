<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class IngredientSupplierTerm extends Model
{
    use SoftDeletes;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'ingredient_id',
        'supplier_id',
        'supplier_sku',
        'lead_time_days',
        'minimum_order_quantity',
        'minimum_order_value',
        'pack_size',
        'preferred',
        'active',
        'unit_cost_override',
        'last_unit_cost',
        'average_unit_cost',
        'payment_term_days',
        'currency',
        'valid_from',
        'valid_until',
        'quality_threshold_percent',
        'meta',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'lead_time_days' => 'integer',
            'minimum_order_quantity' => 'decimal:3',
            'minimum_order_value' => 'decimal:2',
            'pack_size' => 'decimal:3',
            'preferred' => 'boolean',
            'active' => 'boolean',
            'unit_cost_override' => 'decimal:4',
            'last_unit_cost' => 'decimal:2',
            'average_unit_cost' => 'decimal:2',
            'payment_term_days' => 'integer',
            'valid_from' => 'date',
            'valid_until' => 'date',
            'quality_threshold_percent' => 'decimal:4',
            'meta' => 'array',
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
