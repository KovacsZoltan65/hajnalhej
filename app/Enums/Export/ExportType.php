<?php

declare(strict_types=1);

namespace App\Enums\Export;

use App\Support\PermissionRegistry;

enum ExportType: string
{
    case Orders = 'orders';
    case Products = 'products';
    case Ingredients = 'ingredients';
    case InventoryMovements = 'inventory_movements';
    case AuditLogs = 'audit_logs';

    public function permission(): string
    {
        return match ($this) {
            self::Orders => PermissionRegistry::ORDERS_EXPORT,
            self::Products => PermissionRegistry::PRODUCTS_EXPORT,
            self::Ingredients => PermissionRegistry::INGREDIENTS_EXPORT,
            self::InventoryMovements => PermissionRegistry::INVENTORY_EXPORT,
            self::AuditLogs => PermissionRegistry::AUDIT_LOGS_EXPORT,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Orders => 'Orders',
            self::Products => 'Products',
            self::Ingredients => 'Ingredients',
            self::InventoryMovements => 'Inventory Movements',
            self::AuditLogs => 'Audit Logs',
        };
    }
}
