<?php

namespace App\Models;

use Database\Factories\ProductIngredientFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id Rekord azonosító
 * @property int $product_id
 * @property int $ingredient_id
 * @property numeric $quantity Felhasznált mennyiség
 * @property int $sort_order Recepten belüli sorrend
 * @property string|null $notes Recept tétel megjegyzése
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Ingredient|null $ingredient
 * @property-read \App\Models\Product|null $product
 * @method static \Database\Factories\ProductIngredientFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductIngredient newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductIngredient newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductIngredient query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductIngredient whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductIngredient whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductIngredient whereIngredientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductIngredient whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductIngredient whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductIngredient whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductIngredient whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductIngredient whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ProductIngredient extends Model
{
    /** @use HasFactory<ProductIngredientFactory> */
    use HasFactory;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'product_id',
        'ingredient_id',
        'quantity',
        'sort_order',
        'notes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:3',
            'sort_order' => 'integer',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class)->withTrashed();
    }
}
