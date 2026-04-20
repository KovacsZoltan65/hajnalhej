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
