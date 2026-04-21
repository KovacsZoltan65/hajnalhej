<?php

namespace App\Support;

class PermissionRegistry
{
    public const ROLE_ADMIN = 'admin';
    public const ROLE_CUSTOMER = 'customer';

    public const ADMIN_PANEL_ACCESS = 'admin.panel.access';

    public const ORDERS_VIEW = 'orders.view';
    public const ORDERS_UPDATE = 'orders.update';

    public const PRODUCTS_VIEW = 'products.view';
    public const PRODUCTS_CREATE = 'products.create';
    public const PRODUCTS_UPDATE = 'products.update';
    public const PRODUCTS_DELETE = 'products.delete';

    public const CATEGORIES_VIEW = 'categories.view';
    public const CATEGORIES_CREATE = 'categories.create';
    public const CATEGORIES_UPDATE = 'categories.update';
    public const CATEGORIES_DELETE = 'categories.delete';

    public const INGREDIENTS_VIEW = 'ingredients.view';
    public const INGREDIENTS_CREATE = 'ingredients.create';
    public const INGREDIENTS_UPDATE = 'ingredients.update';
    public const INGREDIENTS_DELETE = 'ingredients.delete';

    public const WEEKLY_MENU_VIEW = 'weekly-menu.view';
    public const WEEKLY_MENU_CREATE = 'weekly-menu.create';
    public const WEEKLY_MENU_UPDATE = 'weekly-menu.update';
    public const WEEKLY_MENU_DELETE = 'weekly-menu.delete';

    public const PRODUCTION_PLANS_VIEW = 'production-plans.view';
    public const PRODUCTION_PLANS_CREATE = 'production-plans.create';
    public const PRODUCTION_PLANS_UPDATE = 'production-plans.update';
    public const PRODUCTION_PLANS_DELETE = 'production-plans.delete';

    public const ACCOUNT_VIEW = 'account.view';

    /**
     * @return array<int, string>
     */
    public static function permissions(): array
    {
        return [
            self::ADMIN_PANEL_ACCESS,
            self::ORDERS_VIEW,
            self::ORDERS_UPDATE,
            self::PRODUCTS_VIEW,
            self::PRODUCTS_CREATE,
            self::PRODUCTS_UPDATE,
            self::PRODUCTS_DELETE,
            self::CATEGORIES_VIEW,
            self::CATEGORIES_CREATE,
            self::CATEGORIES_UPDATE,
            self::CATEGORIES_DELETE,
            self::INGREDIENTS_VIEW,
            self::INGREDIENTS_CREATE,
            self::INGREDIENTS_UPDATE,
            self::INGREDIENTS_DELETE,
            self::WEEKLY_MENU_VIEW,
            self::WEEKLY_MENU_CREATE,
            self::WEEKLY_MENU_UPDATE,
            self::WEEKLY_MENU_DELETE,
            self::PRODUCTION_PLANS_VIEW,
            self::PRODUCTION_PLANS_CREATE,
            self::PRODUCTION_PLANS_UPDATE,
            self::PRODUCTION_PLANS_DELETE,
            self::ACCOUNT_VIEW,
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function adminPermissions(): array
    {
        return self::permissions();
    }

    /**
     * @return array<int, string>
     */
    public static function customerPermissions(): array
    {
        return [
            self::ACCOUNT_VIEW,
        ];
    }
}
