<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
