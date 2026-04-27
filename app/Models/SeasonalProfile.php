<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SeasonalProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'ingredient_id',
        'product_id',
        'name',
        'profile_type',
        'starts_on',
        'ends_on',
        'demand_multiplier',
        'confidence_percent',
        'active',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'starts_on' => 'date',
            'ends_on' => 'date',
            'demand_multiplier' => 'decimal:4',
            'confidence_percent' => 'decimal:4',
            'active' => 'boolean',
        ];
    }

    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
