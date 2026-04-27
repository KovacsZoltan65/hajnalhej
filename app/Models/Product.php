<?php

namespace App\Models;

use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * @property int $id Rekord azonosító
 * @property int $category_id
 * @property string $name Megnevezés
 * @property string|null $slug Egyedi URL azonosító, SEO célra
 * @property string|null $short_description Rövid leírás
 * @property string|null $description Részletes termékleírás
 * @property numeric $price Ár
 * @property bool $is_active Publikus láthatóság státusza
 * @property bool $is_featured Kiemelt termék jelző
 * @property string $stock_status Készletállapot: in_stock|preorder|out_of_stock
 * @property string|null $image_path Kép útvonala
 * @property int $sort_order Admin listázási sorrend
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at Soft delete időpontja
 * @property-read \App\Models\Category|null $category
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ForecastSnapshot> $forecastSnapshots
 * @property-read int|null $forecast_snapshots_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\OrderItem> $orderItems
 * @property-read int|null $order_items_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PricingRule> $pricingRules
 * @property-read int|null $pricing_rules_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ProductIngredient> $productIngredients
 * @property-read int|null $product_ingredients_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\RecipeStep> $recipeSteps
 * @property-read int|null $recipe_steps_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SeasonalProfile> $seasonalProfiles
 * @property-read int|null $seasonal_profiles_count
 * @method static \Database\Factories\ProductFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereImagePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereIsFeatured($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereShortDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereStockStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product withoutTrashed()
 * @mixin \Eloquent
 */
class Product extends Model
{
    public const STOCK_IN_STOCK = 'in_stock';
    public const STOCK_PREORDER = 'preorder';
    public const STOCK_OUT_OF_STOCK = 'out_of_stock';

    /** @use HasFactory<ProductFactory> */
    use HasFactory, SoftDeletes;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'short_description',
        'description',
        'price',
        'is_active',
        'is_featured',
        'stock_status',
        'image_path',
        'sort_order',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    protected function slug(): Attribute
    {
        return Attribute::make(
            set: static fn (?string $value): string => Str::slug((string) $value),
        );
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function productIngredients(): HasMany
    {
        return $this->hasMany(ProductIngredient::class)
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function recipeSteps(): HasMany
    {
        return $this->hasMany(RecipeStep::class)
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function forecastSnapshots(): HasMany
    {
        return $this->hasMany(ForecastSnapshot::class);
    }

    public function seasonalProfiles(): HasMany
    {
        return $this->hasMany(SeasonalProfile::class);
    }

    public function pricingRules(): HasMany
    {
        return $this->hasMany(PricingRule::class);
    }

    /**
     * @return array<int, string>
     */
    public static function stockStatuses(): array
    {
        return [
            self::STOCK_IN_STOCK,
            self::STOCK_PREORDER,
            self::STOCK_OUT_OF_STOCK,
        ];
    }
}
