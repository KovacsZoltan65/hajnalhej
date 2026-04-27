<?php

namespace App\Models;

use Database\Factories\SupplierFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    /** @use HasFactory<SupplierFactory> */
    use HasFactory;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'tax_number',
        'lead_time_days',
        'minimum_order_value',
        'active',
        'currency',
        'notes',
        'meta',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'lead_time_days' => 'integer',
            'minimum_order_value' => 'decimal:2',
            'active' => 'boolean',
            'meta' => 'array',
        ];
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(SupplierContact::class);
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }

    public function ingredientTerms(): HasMany
    {
        return $this->hasMany(IngredientSupplierTerm::class);
    }

    public function procurementAlerts(): HasMany
    {
        return $this->hasMany(ProcurementAlert::class);
    }

    public function priceAlerts(): HasMany
    {
        return $this->hasMany(PriceAlert::class);
    }

    public function scores(): HasMany
    {
        return $this->hasMany(SupplierScore::class);
    }

    public function purchaseRecommendations(): HasMany
    {
        return $this->hasMany(PurchaseRecommendation::class);
    }

    public function recommendationItems(): HasMany
    {
        return $this->hasMany(PurchaseRecommendationItem::class);
    }

    public function riskEvents(): HasMany
    {
        return $this->hasMany(RiskEvent::class);
    }

    public function negotiations(): HasMany
    {
        return $this->hasMany(SupplierNegotiation::class);
    }
}
