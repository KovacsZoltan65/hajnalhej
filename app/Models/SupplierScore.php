<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplierScore extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_id',
        'period_start',
        'period_end',
        'overall_score',
        'price_score',
        'reliability_score',
        'quality_score',
        'lead_time_score',
        'orders_count',
        'late_deliveries_count',
        'rejected_quantity',
        'score_breakdown',
    ];

    protected function casts(): array
    {
        return [
            'period_start' => 'date',
            'period_end' => 'date',
            'overall_score' => 'decimal:4',
            'price_score' => 'decimal:4',
            'reliability_score' => 'decimal:4',
            'quality_score' => 'decimal:4',
            'lead_time_score' => 'decimal:4',
            'orders_count' => 'integer',
            'late_deliveries_count' => 'integer',
            'rejected_quantity' => 'decimal:3',
            'score_breakdown' => 'array',
        ];
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }
}
