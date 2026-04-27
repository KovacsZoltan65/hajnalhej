<?php

declare(strict_types=1);

namespace App\Services;

use App\Data\Settings\SettingGroupData;
use App\Data\Settings\SettingItemData;
use App\Data\Settings\SettingSaveValueData;
use App\Repositories\SettingsRepository;
use App\Services\Cache\CacheVersionService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class SettingsService
{
    public function save(int $actorUserId, array $context, array $values): array
    {
        return [];
    }
}