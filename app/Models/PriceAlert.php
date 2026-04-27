<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PriceAlert extends Model
{
    use HasFactory;

    protected $fillable = [
        'ingredient_id',
        'supplier_id',
        'alert_type',
        'status',
        'previous_unit_cost',
        'current_unit_cost',
        'change_percent',
        'margin_impact',
        'detected_on',
        'notes',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'previous_unit_cost' => 'decimal:2',
            'current_unit_cost' => 'decimal:2',
            'change_percent' => 'decimal:4',
            'margin_impact' => 'decimal:2',
            'detected_on' => 'date',
            'resolved_at' => 'datetime',
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
