<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int|null $ingredient_id
 * @property int|null $product_id
 * @property string $name
 * @property string $profile_type
 * @property \Illuminate\Support\Carbon $starts_on
 * @property \Illuminate\Support\Carbon $ends_on
 * @property numeric $demand_multiplier
 * @property numeric|null $confidence_percent
 * @property bool $active
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Ingredient|null $ingredient
 * @property-read \App\Models\Product|null $product
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeasonalProfile newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeasonalProfile newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeasonalProfile query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeasonalProfile whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeasonalProfile whereConfidencePercent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeasonalProfile whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeasonalProfile whereDemandMultiplier($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeasonalProfile whereEndsOn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeasonalProfile whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeasonalProfile whereIngredientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeasonalProfile whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeasonalProfile whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeasonalProfile whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeasonalProfile whereProfileType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeasonalProfile whereStartsOn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeasonalProfile whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class SeasonalProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'ingredient_id',
        'product_id',
        'name',
        'profile_type',
        'starts_on',
        'ends_on',
        'demand_multiplier',
        'confidence_percent',
        'active',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'starts_on' => 'date',
            'ends_on' => 'date',
            'demand_multiplier' => 'decimal:4',
            'confidence_percent' => 'decimal:4',
            'active' => 'boolean',
        ];
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
