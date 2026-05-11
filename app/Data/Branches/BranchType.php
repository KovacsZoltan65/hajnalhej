<?php

declare(strict_types=1);

namespace App\Data\Branches;

final class BranchType
{
    public const BAKERY = 'bakery';

    public const SHOP = 'shop';

    public const PICKUP_POINT = 'pickup_point';

    public const WAREHOUSE = 'warehouse';

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return [
            self::BAKERY,
            self::SHOP,
            self::PICKUP_POINT,
            self::WAREHOUSE,
        ];
    }
}
