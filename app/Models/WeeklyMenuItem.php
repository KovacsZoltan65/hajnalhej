<?php

namespace App\Models;

use Database\Factories\WeeklyMenuItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $weekly_menu_id
 * @property int $product_id
 * @property int $category_id
 * @property string $override_name
 * @property string $override_short_description
 * @property number $override_price
 * @property int $sort_order
 * @property bool $is_active
 * @property string $badge_text
 * @property string $stock_note
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
