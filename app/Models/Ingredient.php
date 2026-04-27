<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\IngredientFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ingredient extends Model
{
    /** @use HasFactory<IngredientFactory> */
    use HasFactory, SoftDeletes;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'sku',
        'unit',
        'estimated_unit_cost',
        'average_unit_cost',
        'stock_value',
        'current_stock',
        'minimum_stock',
        'is_active',
        'notes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'estimated_unit_cost' => 'decimal:4',
            'average_unit_cost' => 'decimal:4',
            'stock_value' => 'decimal:2',
            'current_stock' => 'decimal:3',
            'minimum_stock' => 'decimal:3',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function allowedUnits(): array
    {
        return ['g', 'kg', 'ml', 'l', 'db'];
    }

    public function productIngredients(): HasMany
    {
        return $this->hasMany(ProductIngredient::class);
    }

    public function inventoryMovements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class);
    }

    public function purchaseItems(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function supplierTerms(): HasMany
    {
        return $this->hasMany(IngredientSupplierTerm::class);
    }

    public function purchaseReceiptItems(): HasMany
    {
        return $this->hasMany(PurchaseReceiptItem::class);
    }

    public function procurementAlerts(): HasMany
    {
        return $this->hasMany(ProcurementAlert::class);
    }

    public function forecastSnapshots(): HasMany
    {
        return $this->hasMany(ForecastSnapshot::class);
    }

    public function seasonalProfiles(): HasMany
    {
        return $this->hasMany(SeasonalProfile::class);
    }

    public function priceAlerts(): HasMany
    {
        return $this->hasMany(PriceAlert::class);
    }

    public function purchaseRecommendationItems(): HasMany
    {
        return $this->hasMany(PurchaseRecommendationItem::class);
    }

    public function riskEvents(): HasMany
    {
        return $this->hasMany(RiskEvent::class);
    }

    public function branchInventoryItems(): HasMany
    {
        return $this->hasMany(BranchInventory::class);
    }

    public function branchTransfers(): HasMany
    {
        return $this->hasMany(BranchTransfer::class);
    }

    public function supplierNegotiations(): HasMany
    {
        return $this->hasMany(SupplierNegotiation::class);
    }

    public function isLowStock(): bool
    {
        return (float) $this->current_stock <= (float) $this->minimum_stock;
    }
}
