<?php

namespace App\Models;

use Database\Factories\WeeklyMenuFactory;
use DateTime;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id Rekord azonosító
 * @property string $title Heti menü admin címe
 * @property string $slug Egyedi URL azonosító, SEO célra
 * @property \Illuminate\Support\Carbon $week_start A heti menü kezdődátuma
 * @property \Illuminate\Support\Carbon $week_end A heti menü záródátuma
 * @property string $status Állapot: draft|published|archived
 * @property string|null $public_note Vásárlóknak megjelenő megjegyzés
 * @property string|null $internal_note Belső üzemeltetési megjegyzés
 * @property bool $is_featured Kiemelt heti menü jelző
 * @property \Illuminate\Support\Carbon|null $published_at Publikálás időpontja
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at Soft delete időpontja
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\WeeklyMenuItem> $items
 * @property-read int|null $items_count
 * @method static \Database\Factories\WeeklyMenuFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WeeklyMenu newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WeeklyMenu newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WeeklyMenu onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WeeklyMenu query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WeeklyMenu whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WeeklyMenu whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WeeklyMenu whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WeeklyMenu whereInternalNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WeeklyMenu whereIsFeatured($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WeeklyMenu wherePublicNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WeeklyMenu wherePublishedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WeeklyMenu whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WeeklyMenu whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WeeklyMenu whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WeeklyMenu whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WeeklyMenu whereWeekEnd($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WeeklyMenu whereWeekStart($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WeeklyMenu withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WeeklyMenu withoutTrashed()
 * @mixin \Eloquent
 */
class WeeklyMenu extends Model
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_ARCHIVED = 'archived';

    /** @use HasFactory<WeeklyMenuFactory> */
    use HasFactory, SoftDeletes;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'slug',
        'week_start',
        'week_end',
        'status',
        'public_note',
        'internal_note',
        'is_featured',
        'published_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'week_start' => 'date',
            'week_end' => 'date',
            'is_featured' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(WeeklyMenuItem::class)->orderBy('sort_order')->orderBy('id');
    }

    /**
     * @return array<int, string>
     */
    public static function statuses(): array
    {
        return [
            self::STATUS_DRAFT,
            self::STATUS_PUBLISHED,
            self::STATUS_ARCHIVED,
        ];
    }
}
