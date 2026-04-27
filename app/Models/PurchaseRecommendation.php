<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseRecommendation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'forecast_run_id',
        'supplier_id',
        'purchase_id',
        'recommendation_number',
        'status',
        'recommendation_date',
        'needed_by_date',
        'estimated_total',
        'cashflow_score',
        'margin_score',
        'rationale',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'recommendation_date' => 'date',
            'needed_by_date' => 'date',
            'estimated_total' => 'decimal:2',
            'cashflow_score' => 'decimal:4',
            'margin_score' => 'decimal:4',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseRecommendationItem::class);
    }

    public function forecastRun(): BelongsTo
    {
        return $this->belongsTo(ForecastRun::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
