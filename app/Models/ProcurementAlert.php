<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProcurementAlert extends Model
{
    use HasFactory;

    protected $fillable = [
        'ingredient_id',
        'supplier_id',
        'alert_type',
        'severity',
        'status',
        'alert_date',
        'quantity_gap',
        'estimated_cash_impact',
        'title',
        'message',
        'context',
        'resolved_at',
        'resolved_by',
    ];

    protected function casts(): array
    {
        return [
            'alert_date' => 'date',
            'quantity_gap' => 'decimal:3',
            'estimated_cash_impact' => 'decimal:2',
            'context' => 'array',
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

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }
}
