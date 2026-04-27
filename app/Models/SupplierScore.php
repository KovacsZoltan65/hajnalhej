<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $supplier_id
 * @property \Illuminate\Support\Carbon $period_start
 * @property \Illuminate\Support\Carbon $period_end
 * @property numeric $overall_score
 * @property numeric|null $price_score
 * @property numeric|null $reliability_score
 * @property numeric|null $quality_score
 * @property numeric|null $lead_time_score
 * @property int $orders_count
 * @property int $late_deliveries_count
 * @property numeric $rejected_quantity
 * @property array<array-key, mixed>|null $score_breakdown
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Supplier $supplier
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierScore newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierScore newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierScore query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierScore whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierScore whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierScore whereLateDeliveriesCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierScore whereLeadTimeScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierScore whereOrdersCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierScore whereOverallScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierScore wherePeriodEnd($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierScore wherePeriodStart($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierScore wherePriceScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierScore whereQualityScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierScore whereRejectedQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierScore whereReliabilityScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierScore whereScoreBreakdown($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierScore whereSupplierId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierScore whereUpdatedAt($value)
 * @mixin \Eloquent
 */
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
