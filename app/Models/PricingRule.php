<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
