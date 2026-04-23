<?php

declare(strict_types=1);

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
//use Spatie\Activitylog\LogOptions;
//use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Models\Role as SpatieRole;

/**
 * @method string getTag()
 */
class Role extends SpatieRole
{
    use HasFactory;

    /** @var array<int,string> */
    protected $fillable = ['name', 'guard_name'];

    protected $guarded = [];

    /** Rendezhető mezők (helperhez / validáláshoz) */
    /** @var array<int,string> */
    protected static array $sortable = ['name', 'guard_name'];

    /** (Opcionálisan) típus-casting – a Carbon megtartásához hasznos */
    /** @var array<string,string> */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    
    protected static string $logName = 'roles';
    /*
    protected static bool $logOnlyDirty = true;

    protected static array $recordEvents = ['created', 'updated', 'deleted'];

    public function getLogNameToUse(string $eventName = ''): string
    {
        return static::$logName ?? 'default';
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable();
    }
    */
    public static function getTag(): string
    {
        return self::$logName;
    }
    

    /** @return array<int,string> */
    public static function getSortable(): array
    {
        return self::$sortable;
    }

    /**
     * @return array<int, array{id:int, name:string}>
     */
    public static function getToSelect(): array
    {
        return static::query()
            ->select(['id', 'name'])
            ->orderBy('name', 'asc')
            ->get()
            ->map(fn ($r) => ['id' => (int) $r->id, 'name' => (string) $r->name])
            ->all();
    }

    protected function serializeDate(DateTimeInterface $date): string
    {
        return $date->format('d-m-Y H:i');
    }
}
