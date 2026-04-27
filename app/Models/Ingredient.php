<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\IngredientFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id Rekord azonosító
 * @property string $name Megnevezés
 * @property string $slug Egyedi URL azonosító, SEO célra
 * @property string|null $sku Belső cikkszám
 * @property string $unit Mértékegység (pl. kg, g, l, db)
 * @property numeric $estimated_unit_cost Becsült egységköltség a mértékegységre vetítve
 * @property numeric|null $average_unit_cost
 * @property numeric|null $stock_value
 * @property numeric $current_stock Aktuális készlet
 * @property numeric $minimum_stock Minimum készletszint
 * @property bool $is_active Felhasználhatóság státusza
 * @property string|null $notes Belső megjegyzés
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at Soft delete időpontja
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\BranchInventory> $branchInventoryItems
 * @property-read int|null $branch_inventory_items_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\BranchTransfer> $branchTransfers
 * @property-read int|null $branch_transfers_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ForecastSnapshot> $forecastSnapshots
 * @property-read int|null $forecast_snapshots_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\InventoryMovement> $inventoryMovements
 * @property-read int|null $inventory_movements_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PriceAlert> $priceAlerts
 * @property-read int|null $price_alerts_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ProcurementAlert> $procurementAlerts
 * @property-read int|null $procurement_alerts_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ProductIngredient> $productIngredients
 * @property-read int|null $product_ingredients_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PurchaseItem> $purchaseItems
 * @property-read int|null $purchase_items_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PurchaseReceiptItem> $purchaseReceiptItems
 * @property-read int|null $purchase_receipt_items_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PurchaseRecommendationItem> $purchaseRecommendationItems
 * @property-read int|null $purchase_recommendation_items_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\RiskEvent> $riskEvents
 * @property-read int|null $risk_events_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SeasonalProfile> $seasonalProfiles
 * @property-read int|null $seasonal_profiles_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SupplierNegotiation> $supplierNegotiations
 * @property-read int|null $supplier_negotiations_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\IngredientSupplierTerm> $supplierTerms
 * @property-read int|null $supplier_terms_count
 * @method static \Database\Factories\IngredientFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ingredient newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ingredient newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ingredient onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ingredient query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ingredient whereAverageUnitCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ingredient whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ingredient whereCurrentStock($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ingredient whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ingredient whereEstimatedUnitCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ingredient whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ingredient whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ingredient whereMinimumStock($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ingredient whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ingredient whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ingredient whereSku($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ingredient whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ingredient whereStockValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ingredient whereUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ingredient whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ingredient withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ingredient withoutTrashed()
 * @mixin \Eloquent
 */
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
