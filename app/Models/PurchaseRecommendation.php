<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int|null $forecast_run_id
 * @property int|null $supplier_id
 * @property int|null $purchase_id
 * @property string $recommendation_number
 * @property string $status
 * @property \Illuminate\Support\Carbon $recommendation_date
 * @property \Illuminate\Support\Carbon|null $needed_by_date
 * @property numeric $estimated_total
 * @property numeric|null $cashflow_score
 * @property numeric|null $margin_score
 * @property string|null $rationale
 * @property int|null $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\User|null $creator
 * @property-read \App\Models\ForecastRun|null $forecastRun
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PurchaseRecommendationItem> $items
 * @property-read int|null $items_count
 * @property-read \App\Models\Purchase|null $purchase
 * @property-read \App\Models\Supplier|null $supplier
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseRecommendation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseRecommendation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseRecommendation onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseRecommendation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseRecommendation whereCashflowScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseRecommendation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseRecommendation whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseRecommendation whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseRecommendation whereEstimatedTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseRecommendation whereForecastRunId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseRecommendation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseRecommendation whereMarginScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseRecommendation whereNeededByDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseRecommendation wherePurchaseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseRecommendation whereRationale($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseRecommendation whereRecommendationDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseRecommendation whereRecommendationNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseRecommendation whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseRecommendation whereSupplierId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseRecommendation whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseRecommendation withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseRecommendation withoutTrashed()
 * @mixin \Eloquent
 */
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
