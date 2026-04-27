<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $ingredient_id
 * @property int $supplier_id
 * @property string|null $supplier_sku
 * @property int|null $lead_time_days
 * @property numeric|null $minimum_order_quantity
 * @property numeric|null $minimum_order_value
 * @property numeric|null $pack_size
 * @property bool $preferred
 * @property bool $active
 * @property numeric|null $unit_cost_override
 * @property numeric|null $last_unit_cost
 * @property numeric|null $average_unit_cost
 * @property int|null $payment_term_days
 * @property string $currency
 * @property \Illuminate\Support\Carbon|null $valid_from
 * @property \Illuminate\Support\Carbon|null $valid_until
 * @property numeric|null $quality_threshold_percent
 * @property array<array-key, mixed>|null $meta
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\Ingredient|null $ingredient
 * @property-read \App\Models\Supplier $supplier
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IngredientSupplierTerm newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IngredientSupplierTerm newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IngredientSupplierTerm onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IngredientSupplierTerm query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IngredientSupplierTerm whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IngredientSupplierTerm whereAverageUnitCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IngredientSupplierTerm whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IngredientSupplierTerm whereCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IngredientSupplierTerm whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IngredientSupplierTerm whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IngredientSupplierTerm whereIngredientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IngredientSupplierTerm whereLastUnitCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IngredientSupplierTerm whereLeadTimeDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IngredientSupplierTerm whereMeta($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IngredientSupplierTerm whereMinimumOrderQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IngredientSupplierTerm whereMinimumOrderValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IngredientSupplierTerm wherePackSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IngredientSupplierTerm wherePaymentTermDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IngredientSupplierTerm wherePreferred($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IngredientSupplierTerm whereQualityThresholdPercent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IngredientSupplierTerm whereSupplierId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IngredientSupplierTerm whereSupplierSku($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IngredientSupplierTerm whereUnitCostOverride($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IngredientSupplierTerm whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IngredientSupplierTerm whereValidFrom($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IngredientSupplierTerm whereValidUntil($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IngredientSupplierTerm withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IngredientSupplierTerm withoutTrashed()
 * @mixin \Eloquent
 */
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
