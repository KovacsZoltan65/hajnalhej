<?php

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

    public function isLowStock(): bool
    {
        return (float) $this->current_stock <= (float) $this->minimum_stock;
    }
}
