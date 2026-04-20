<?php

namespace App\Models;

use Database\Factories\RecipeStepFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

