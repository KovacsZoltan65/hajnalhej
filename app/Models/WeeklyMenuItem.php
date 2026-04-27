<?php

namespace App\Models;

use Database\Factories\WeeklyMenuItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id Rekord azonosító
 * @property int $weekly_menu_id
 * @property int $product_id
 * @property int|null $category_id
 * @property string|null $override_name Heti menüben felülírt terméknév
 * @property string|null $override_short_description Heti menüben felülírt rövid leírás
 * @property numeric|null $override_price Heti menüben felülírt ár
 * @property int $sort_order Heti menün belüli sorrend
 * @property bool $is_active Aktív megjelenés a heti menüben
 * @property string|null $badge_text Termékkártya badge felirat
 * @property string|null $stock_note Készlettel kapcsolatos rövid megjegyzés
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Category|null $category
 * @property-read \App\Models\Product|null $product
 * @property-read \App\Models\WeeklyMenu|null $weeklyMenu
 * @method static \Database\Factories\WeeklyMenuItemFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WeeklyMenuItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WeeklyMenuItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WeeklyMenuItem query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WeeklyMenuItem whereBadgeText($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WeeklyMenuItem whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WeeklyMenuItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WeeklyMenuItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WeeklyMenuItem whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WeeklyMenuItem whereOverrideName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WeeklyMenuItem whereOverridePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WeeklyMenuItem whereOverrideShortDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WeeklyMenuItem whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WeeklyMenuItem whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WeeklyMenuItem whereStockNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WeeklyMenuItem whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WeeklyMenuItem whereWeeklyMenuId($value)
 * @mixin \Eloquent
 */
class WeeklyMenuItem extends Model
{
    /** @use HasFactory<WeeklyMenuItemFactory> */
    use HasFactory;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'weekly_menu_id',
        'product_id',
        'category_id',
        'override_name',
        'override_short_description',
        'override_price',
        'sort_order',
        'is_active',
        'badge_text',
        'stock_note',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'override_price' => 'decimal:2',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function weeklyMenu(): BelongsTo
    {
        return $this->belongsTo(WeeklyMenu::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
