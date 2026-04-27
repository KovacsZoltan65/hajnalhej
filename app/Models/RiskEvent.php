<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiskEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_id',
        'ingredient_id',
        'event_type',
        'severity',
        'status',
        'event_date',
        'estimated_impact_amount',
        'probability_percent',
        'title',
        'description',
        'mitigation_plan',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'event_date' => 'date',
            'estimated_impact_amount' => 'decimal:2',
            'probability_percent' => 'decimal:4',
            'resolved_at' => 'datetime',
        ];
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class);
    }
}
