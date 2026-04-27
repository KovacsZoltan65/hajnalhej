<?php

namespace App\Models;

use Database\Factories\RecipeStepFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id Rekord azonosító
 * @property int $product_id
 * @property string $title Lépés megnevezése
 * @property string $step_type Lépés típusa: preparation|mixing|resting|proofing|baking|cooling|finishing
 * @property string|null $description Lépés részletes leírása
 * @property string|null $work_instruction Mit kell vegrehajtani pontosan
 * @property string|null $completion_criteria Mibol latszik, hogy a lepes kesz
 * @property string|null $attention_points Kritikus figyelmeztetesek, hibalehetosegek
 * @property string|null $required_tools Szukseges eszkozok, gepek, segedanyagok
 * @property string|null $expected_result Elvart allapot vagy kimenet a lepes vegen
 * @property int|null $duration_minutes Aktív munkaidő percben
 * @property int|null $wait_minutes Várakozási idő percben
 * @property numeric|null $temperature_celsius Hőmérséklet Celsius fokban
 * @property int $sort_order Lépés sorrendje a recepten belül
 * @property bool $is_active Aktív lépés jelző
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Product|null $product
 * @method static \Database\Factories\RecipeStepFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RecipeStep newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RecipeStep newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RecipeStep query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RecipeStep whereAttentionPoints($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RecipeStep whereCompletionCriteria($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RecipeStep whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RecipeStep whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RecipeStep whereDurationMinutes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RecipeStep whereExpectedResult($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RecipeStep whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RecipeStep whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RecipeStep whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RecipeStep whereRequiredTools($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RecipeStep whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RecipeStep whereStepType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RecipeStep whereTemperatureCelsius($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RecipeStep whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RecipeStep whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RecipeStep whereWaitMinutes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RecipeStep whereWorkInstruction($value)
 * @mixin \Eloquent
 */
class RecipeStep extends Model
{
    public const TYPE_PREPARATION = 'preparation';
    public const TYPE_MIXING = 'mixing';
    public const TYPE_RESTING = 'resting';
    public const TYPE_PROOFING = 'proofing';
    public const TYPE_BAKING = 'baking';
    public const TYPE_COOLING = 'cooling';
    public const TYPE_FINISHING = 'finishing';

    /** @use HasFactory<RecipeStepFactory> */
    use HasFactory;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'product_id',
        'title',
        'step_type',
        'description',
        'work_instruction',
        'completion_criteria',
        'attention_points',
        'required_tools',
        'expected_result',
        'duration_minutes',
        'wait_minutes',
        'temperature_celsius',
        'sort_order',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'duration_minutes' => 'integer',
            'wait_minutes' => 'integer',
            'temperature_celsius' => 'decimal:1',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * @return array<int, string>
     */
    public static function stepTypes(): array
    {
        return [
            self::TYPE_PREPARATION,
            self::TYPE_MIXING,
            self::TYPE_RESTING,
            self::TYPE_PROOFING,
            self::TYPE_BAKING,
            self::TYPE_COOLING,
            self::TYPE_FINISHING,
        ];
    }
}
