<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int|null $product_id
 * @property string $name
 * @property string $rule_type
 * @property numeric|null $target_margin_percent
 * @property numeric|null $minimum_margin_percent
 * @property numeric|null $cost_change_threshold_percent
 * @property numeric|null $suggested_price
 * @property bool $active
 * @property \Illuminate\Support\Carbon|null $valid_from
 * @property \Illuminate\Support\Carbon|null $valid_until
 * @property array<array-key, mixed>|null $conditions
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Product|null $product
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PricingRule newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PricingRule newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PricingRule query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PricingRule whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PricingRule whereConditions($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PricingRule whereCostChangeThresholdPercent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PricingRule whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PricingRule whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PricingRule whereMinimumMarginPercent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PricingRule whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PricingRule whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PricingRule whereRuleType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PricingRule whereSuggestedPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PricingRule whereTargetMarginPercent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PricingRule whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PricingRule whereValidFrom($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PricingRule whereValidUntil($value)
 * @mixin \Eloquent
 */
class PricingRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'name',
        'rule_type',
        'target_margin_percent',
        'minimum_margin_percent',
        'cost_change_threshold_percent',
        'suggested_price',
        'active',
        'valid_from',
        'valid_until',
        'conditions',
    ];

    protected function casts(): array
    {
        return [
            'target_margin_percent' => 'decimal:4',
            'minimum_margin_percent' => 'decimal:4',
            'cost_change_threshold_percent' => 'decimal:4',
            'suggested_price' => 'decimal:2',
            'active' => 'boolean',
            'valid_from' => 'date',
            'valid_until' => 'date',
            'conditions' => 'array',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
