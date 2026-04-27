<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $forecast_run_id
 * @property int $ingredient_id
 * @property int|null $product_id
 * @property \Illuminate\Support\Carbon $forecast_date
 * @property numeric $forecast_quantity
 * @property numeric|null $actual_quantity
 * @property numeric|null $variance_quantity
 * @property numeric|null $confidence_percent
 * @property numeric|null $estimated_cost
 * @property array<array-key, mixed>|null $drivers
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\ForecastRun $forecastRun
 * @property-read \App\Models\Ingredient|null $ingredient
 * @property-read \App\Models\Product|null $product
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ForecastSnapshot newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ForecastSnapshot newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ForecastSnapshot query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ForecastSnapshot whereActualQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ForecastSnapshot whereConfidencePercent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ForecastSnapshot whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ForecastSnapshot whereDrivers($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ForecastSnapshot whereEstimatedCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ForecastSnapshot whereForecastDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ForecastSnapshot whereForecastQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ForecastSnapshot whereForecastRunId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ForecastSnapshot whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ForecastSnapshot whereIngredientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ForecastSnapshot whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ForecastSnapshot whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ForecastSnapshot whereVarianceQuantity($value)
 * @mixin \Eloquent
 */
class ForecastSnapshot extends Model
{
    use HasFactory;

    protected $fillable = [
        'forecast_run_id',
        'ingredient_id',
        'product_id',
        'forecast_date',
        'forecast_quantity',
        'actual_quantity',
        'variance_quantity',
        'confidence_percent',
        'estimated_cost',
        'drivers',
    ];

    protected function casts(): array
    {
        return [
            'forecast_date' => 'date',
            'forecast_quantity' => 'decimal:3',
            'actual_quantity' => 'decimal:3',
            'variance_quantity' => 'decimal:3',
            'confidence_percent' => 'decimal:4',
            'estimated_cost' => 'decimal:2',
            'drivers' => 'array',
        ];
    }

    public function forecastRun(): BelongsTo
    {
        return $this->belongsTo(ForecastRun::class);
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
