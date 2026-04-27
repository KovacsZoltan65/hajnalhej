<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SupplierNegotiation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'supplier_id',
        'ingredient_id',
        'status',
        'planned_on',
        'completed_on',
        'current_unit_cost',
        'target_unit_cost',
        'expected_savings',
        'achieved_savings',
        'talking_points',
        'outcome_notes',
        'evidence_snapshot',
        'owner_id',
    ];

    protected function casts(): array
    {
        return [
            'planned_on' => 'date',
            'completed_on' => 'date',
            'current_unit_cost' => 'decimal:2',
            'target_unit_cost' => 'decimal:2',
            'expected_savings' => 'decimal:2',
            'achieved_savings' => 'decimal:2',
            'evidence_snapshot' => 'array',
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

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }
}
