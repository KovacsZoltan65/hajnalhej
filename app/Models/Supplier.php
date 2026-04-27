<?php

namespace App\Models;

use Database\Factories\SupplierFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 * @property string|null $email
 * @property string|null $phone
 * @property string|null $tax_number
 * @property int $lead_time_days
 * @property numeric|null $minimum_order_value
 * @property bool $active
 * @property string $currency
 * @property string|null $notes
 * @property array<array-key, mixed>|null $meta
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SupplierContact> $contacts
 * @property-read int|null $contacts_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\IngredientSupplierTerm> $ingredientTerms
 * @property-read int|null $ingredient_terms_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SupplierNegotiation> $negotiations
 * @property-read int|null $negotiations_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PriceAlert> $priceAlerts
 * @property-read int|null $price_alerts_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ProcurementAlert> $procurementAlerts
 * @property-read int|null $procurement_alerts_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PurchaseRecommendation> $purchaseRecommendations
 * @property-read int|null $purchase_recommendations_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Purchase> $purchases
 * @property-read int|null $purchases_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PurchaseRecommendationItem> $recommendationItems
 * @property-read int|null $recommendation_items_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\RiskEvent> $riskEvents
 * @property-read int|null $risk_events_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SupplierScore> $scores
 * @property-read int|null $scores_count
 * @method static \Database\Factories\SupplierFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier whereCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier whereLeadTimeDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier whereMeta($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier whereMinimumOrderValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier whereTaxNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier whereUpdatedAt($value)
 * @mixin \Eloquent
 */
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
