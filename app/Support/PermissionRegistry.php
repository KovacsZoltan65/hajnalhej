<?php

namespace App\Support;

class PermissionRegistry
{
    /**
     * @var array<string, array{name:string,module:string,label:string,description:string,dangerous:bool,sort:int,system:bool,audit_sensitive:bool}>|null
     */
    private static ?array $definitionsByNameCache = null;

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

    public const ADMIN_USERS_VIEW = 'admin.users.view';
    public const ADMIN_USERS_CREATE = 'admin.users.create';
    public const ADMIN_USERS_UPDATE = 'admin.users.update';
    public const ADMIN_USERS_DELETE = 'admin.users.delete';
    public const ADMIN_USERS_MANAGE_ROLES = 'admin.users.manage_roles';

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
    public const ROLES_VIEW = 'roles.view';
    public const ROLES_CREATE = 'roles.create';
    public const ROLES_UPDATE = 'roles.update';
    public const ROLES_DELETE = 'roles.delete';
    public const ROLES_ASSIGN_PERMISSIONS = 'roles.assign-permissions';
    public const USERS_ASSIGN_ROLES = 'users.assign-roles';
    public const USERS_VIEW_PERMISSIONS = 'users.view-permissions';
    public const PERMISSIONS_VIEW = 'permissions.view';
    public const PERMISSIONS_SYNC = 'permissions.sync';
    public const PERMISSIONS_VIEW_USAGE = 'permissions.view-usage';
    public const AUDIT_LOGS_VIEW = 'audit-logs.view';
    public const SECURITY_DASHBOARD_VIEW = 'security-dashboard.view';
    public const CONVERSION_ANALYTICS_VIEW = 'conversion-analytics.view';
    public const PROFIT_DASHBOARD_VIEW = 'profit-dashboard.view';
    public const CEO_DASHBOARD_VIEW = 'ceo-dashboard.view';
    public const SUPPLIERS_VIEW = 'suppliers.view';
    public const SUPPLIERS_MANAGE = 'suppliers.manage';
    public const PURCHASES_VIEW = 'purchases.view';
    public const PURCHASES_MANAGE = 'purchases.manage';
    public const PROCUREMENT_INTELLIGENCE_VIEW = 'procurement-intelligence.view';
    public const INVENTORY_VIEW = 'inventory.view';
    public const INVENTORY_ADJUST = 'inventory.adjust';
    public const WASTE_MANAGE = 'waste.manage';
    public const STOCK_COUNTS_MANAGE = 'stock-counts.manage';
    public const INVENTORY_DASHBOARD_VIEW = 'inventory-dashboard.view';

    /**
     * @return array<int, string>
     */
    public static function permissions(): array
    {
        return array_values(array_map(
            static fn (array $definition): string => $definition['name'],
            self::definitions(),
        ));
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

    /**
     * @return array<int, string>
     */
    public static function systemRoles(): array
    {
        return [
            self::ROLE_ADMIN,
            self::ROLE_CUSTOMER,
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function criticalAdminPermissions(): array
    {
        return [
            self::ADMIN_PANEL_ACCESS,
            self::ROLES_VIEW,
            self::ROLES_ASSIGN_PERMISSIONS,
            self::USERS_ASSIGN_ROLES,
            self::ADMIN_USERS_VIEW,
            self::ADMIN_USERS_MANAGE_ROLES,
            self::PERMISSIONS_VIEW,
            self::PERMISSIONS_SYNC,
            self::AUDIT_LOGS_VIEW,
            self::SECURITY_DASHBOARD_VIEW,
            self::CONVERSION_ANALYTICS_VIEW,
            self::PROFIT_DASHBOARD_VIEW,
            self::CEO_DASHBOARD_VIEW,
            self::INVENTORY_DASHBOARD_VIEW,
        ];
    }

    /**
     * @return array<int, array{name:string,module:string,label:string,description:string,dangerous:bool,sort:int,system:bool,audit_sensitive:bool}>
     */
    public static function definitions(): array
    {
        return [
            [
                'name' => self::ADMIN_PANEL_ACCESS,
                'module' => 'Admin',
                'label' => 'Admin panel elérés',
                'description' => 'Admin felületre belépés engedélyezése.',
                'dangerous' => false,
                'sort' => 10,
                'system' => true,
                'audit_sensitive' => false,
            ],
            [
                'name' => self::ORDERS_VIEW,
                'module' => 'Rendelések',
                'label' => 'Rendelések megtekintése',
                'description' => 'Rendelés lista és részletek megtekintése.',
                'dangerous' => false,
                'sort' => 20,
                'system' => true,
                'audit_sensitive' => false,
            ],
            [
                'name' => self::ORDERS_UPDATE,
                'module' => 'Rendelések',
                'label' => 'Rendelések szerkesztése',
                'description' => 'Rendelés státusz és belső jegyzet módosítás.',
                'dangerous' => true,
                'sort' => 30,
                'system' => true,
                'audit_sensitive' => true,
            ],
            [
                'name' => self::PRODUCTS_VIEW,
                'module' => 'Products',
                'label' => 'Termékek megtekintése',
                'description' => 'Termék lista megtekintése adminban.',
                'dangerous' => false,
                'sort' => 40,
                'system' => true,
                'audit_sensitive' => false,
            ],
            [
                'name' => self::PRODUCTS_CREATE,
                'module' => 'Products',
                'label' => 'Termék létrehozás',
                'description' => 'Új termék létrehozás.',
                'dangerous' => false,
                'sort' => 50,
                'system' => true,
                'audit_sensitive' => false,
            ],
            [
                'name' => self::PRODUCTS_UPDATE,
                'module' => 'Products',
                'label' => 'Termék szerkesztés',
                'description' => 'Meglévő termék adatok szerkesztése.',
                'dangerous' => false,
                'sort' => 60,
                'system' => true,
                'audit_sensitive' => false,
            ],
            [
                'name' => self::PRODUCTS_DELETE,
                'module' => 'Products',
                'label' => 'Termék törlés',
                'description' => 'Termék archiv/törlés muvelet.',
                'dangerous' => true,
                'sort' => 70,
                'system' => true,
                'audit_sensitive' => true,
            ],
            [
                'name' => self::CATEGORIES_VIEW,
                'module' => 'Categories',
                'label' => 'Kategóriák megtekintése',
                'description' => 'Kategóriák listázása adminban.',
                'dangerous' => false,
                'sort' => 80,
                'system' => true,
                'audit_sensitive' => false,
            ],
            [
                'name' => self::CATEGORIES_CREATE,
                'module' => 'Categories',
                'label' => 'Kategória létrehozás',
                'description' => 'Új kategoria létrehozás.',
                'dangerous' => false,
                'sort' => 90,
                'system' => true,
                'audit_sensitive' => false,
            ],
            [
                'name' => self::CATEGORIES_UPDATE,
                'module' => 'Categories',
                'label' => 'Kategória szerkesztés',
                'description' => 'Kategóriák szerkesztése.',
                'dangerous' => false,
                'sort' => 100,
                'system' => true,
                'audit_sensitive' => false,
            ],
            [
                'name' => self::CATEGORIES_DELETE,
                'module' => 'Categories',
                'label' => 'Kategória törlés',
                'description' => 'Kategóriák törlése.',
                'dangerous' => true,
                'sort' => 110,
                'system' => true,
                'audit_sensitive' => true,
            ],
            [
                'name' => self::ADMIN_USERS_VIEW,
                'module' => 'Felhasználók',
                'label' => 'Felhasználók megtekintése',
                'description' => 'Admin felhasználólista és részletek megtekintése.',
                'dangerous' => false,
                'sort' => 112,
                'system' => true,
                'audit_sensitive' => false,
            ],
            [
                'name' => self::ADMIN_USERS_CREATE,
                'module' => 'Felhasználók',
                'label' => 'Felhasználó létrehozás',
                'description' => 'Új felhasználó létrehozása adminból.',
                'dangerous' => true,
                'sort' => 114,
                'system' => true,
                'audit_sensitive' => true,
            ],
            [
                'name' => self::ADMIN_USERS_UPDATE,
                'module' => 'Felhasználók',
                'label' => 'Felhasználó szerkesztés',
                'description' => 'Felhasználói adatok és státusz módosítása.',
                'dangerous' => true,
                'sort' => 116,
                'system' => true,
                'audit_sensitive' => true,
            ],
            [
                'name' => self::ADMIN_USERS_DELETE,
                'module' => 'Felhasználók',
                'label' => 'Felhasználó inaktiválás',
                'description' => 'Felhasználó inaktiválása adminból.',
                'dangerous' => true,
                'sort' => 118,
                'system' => true,
                'audit_sensitive' => true,
            ],
            [
                'name' => self::ADMIN_USERS_MANAGE_ROLES,
                'module' => 'Felhasználók',
                'label' => 'Felhasználói szerepkörök kezelése',
                'description' => 'Szerepkörök hozzárendelése a user szerkesztőben.',
                'dangerous' => true,
                'sort' => 119,
                'system' => true,
                'audit_sensitive' => true,
            ],
            [
                'name' => self::INGREDIENTS_VIEW,
                'module' => 'Ingredients',
                'label' => 'Alapanyagok megtekintése',
                'description' => 'Alapanyag lista megtekintése.',
                'dangerous' => false,
                'sort' => 120,
                'system' => true,
                'audit_sensitive' => false,
            ],
            [
                'name' => self::INGREDIENTS_CREATE,
                'module' => 'Ingredients',
                'label' => 'Alapanyag létrehozás',
                'description' => 'Új alapanyag létrehozás.',
                'dangerous' => false,
                'sort' => 130,
                'system' => true,
                'audit_sensitive' => false,
            ],
            [
                'name' => self::INGREDIENTS_UPDATE,
                'module' => 'Ingredients',
                'label' => 'Alapanyag szerkesztés',
                'description' => 'Alapanyag adatok módosítás.',
                'dangerous' => false,
                'sort' => 140,
                'system' => true,
                'audit_sensitive' => false,
            ],
            [
                'name' => self::INGREDIENTS_DELETE,
                'module' => 'Ingredients',
                'label' => 'Alapanyag törlés',
                'description' => 'Alapanyag inaktiválás/törlés.',
                'dangerous' => true,
                'sort' => 150,
                'system' => true,
                'audit_sensitive' => true,
            ],
            [
                'name' => self::WEEKLY_MENU_VIEW,
                'module' => 'Weekly Menu',
                'label' => 'Heti menü megtekintése',
                'description' => 'Heti menü admin oldal elérése.',
                'dangerous' => false,
                'sort' => 160,
                'system' => true,
                'audit_sensitive' => false,
            ],
            [
                'name' => self::WEEKLY_MENU_CREATE,
                'module' => 'Weekly Menu',
                'label' => 'Heti menü létrehozás',
                'description' => 'Új heti menü létrehozás.',
                'dangerous' => false,
                'sort' => 170,
                'system' => true,
                'audit_sensitive' => false,
            ],
            [
                'name' => self::WEEKLY_MENU_UPDATE,
                'module' => 'Weekly Menu',
                'label' => 'Heti menü szerkesztés',
                'description' => 'Heti menü és tételek módosítás.',
                'dangerous' => false,
                'sort' => 180,
                'system' => true,
                'audit_sensitive' => false,
            ],
            [
                'name' => self::WEEKLY_MENU_DELETE,
                'module' => 'Weekly Menu',
                'label' => 'Heti menü törlés',
                'description' => 'Heti menük törlése.',
                'dangerous' => true,
                'sort' => 190,
                'system' => true,
                'audit_sensitive' => true,
            ],
            [
                'name' => self::PRODUCTION_PLANS_VIEW,
                'module' => 'Production Plans',
                'label' => 'Gyártási tervek megtekintése',
                'description' => 'Gyartastervezo felulet megtekintése.',
                'dangerous' => false,
                'sort' => 200,
                'system' => true,
                'audit_sensitive' => false,
            ],
            [
                'name' => self::PRODUCTION_PLANS_CREATE,
                'module' => 'Production Plans',
                'label' => 'Gyártási terv létrehozás',
                'description' => 'Új gyartasi terv létrehozása.',
                'dangerous' => false,
                'sort' => 210,
                'system' => true,
                'audit_sensitive' => false,
            ],
            [
                'name' => self::PRODUCTION_PLANS_UPDATE,
                'module' => 'Production Plans',
                'label' => 'Gyártási terv szerkesztés',
                'description' => 'Gyártási terv frissítés.',
                'dangerous' => false,
                'sort' => 220,
                'system' => true,
                'audit_sensitive' => false,
            ],
            [
                'name' => self::PRODUCTION_PLANS_DELETE,
                'module' => 'Production Plans',
                'label' => 'Gyártási terv törlés',
                'description' => 'Gyártási terv törlése.',
                'dangerous' => true,
                'sort' => 230,
                'system' => true,
                'audit_sensitive' => true,
            ],
            [
                'name' => self::ACCOUNT_VIEW,
                'module' => 'Account',
                'label' => 'Fiók megtekintése',
                'description' => 'Saját fiók oldal elérése.',
                'dangerous' => false,
                'sort' => 240,
                'system' => true,
                'audit_sensitive' => false,
            ],
            [
                'name' => self::ROLES_VIEW,
                'module' => 'Roles & Permissions',
                'label' => 'Szerepkörök megtekintése',
                'description' => 'Szerepkör menedzsment oldalak megtekintése.',
                'dangerous' => false,
                'sort' => 250,
                'system' => true,
                'audit_sensitive' => false,
            ],
            [
                'name' => self::ROLES_CREATE,
                'module' => 'Roles & Permissions',
                'label' => 'Szerepkör létrehozás',
                'description' => 'Új szerepkör létrehozása.',
                'dangerous' => false,
                'sort' => 260,
                'system' => true,
                'audit_sensitive' => true,
            ],
            [
                'name' => self::ROLES_UPDATE,
                'module' => 'Roles & Permissions',
                'label' => 'Szerepkör szerkesztés',
                'description' => 'Szerepkör átnevezése és frissítése.',
                'dangerous' => false,
                'sort' => 270,
                'system' => true,
                'audit_sensitive' => true,
            ],
            [
                'name' => self::ROLES_DELETE,
                'module' => 'Roles & Permissions',
                'label' => 'Szerepkör törlés',
                'description' => 'Nem rendszer szerepkör törlése.',
                'dangerous' => true,
                'sort' => 280,
                'system' => true,
                'audit_sensitive' => true,
            ],
            [
                'name' => self::ROLES_ASSIGN_PERMISSIONS,
                'module' => 'Roles & Permissions',
                'label' => 'Szerepkör jogosultság kiosztás',
                'description' => 'Role permission matrix szerkesztése.',
                'dangerous' => true,
                'sort' => 290,
                'system' => true,
                'audit_sensitive' => true,
            ],
            [
                'name' => self::USERS_ASSIGN_ROLES,
                'module' => 'Roles & Permissions',
                'label' => 'User role kiosztas',
                'description' => 'Felhasználó szerepköreinek szerkesztése.',
                'dangerous' => true,
                'sort' => 300,
                'system' => true,
                'audit_sensitive' => true,
            ],
            [
                'name' => self::USERS_VIEW_PERMISSIONS,
                'module' => 'Roles & Permissions',
                'label' => 'Felhasználó jogosultság betekintés',
                'description' => 'Felhasználó effektív jogosultságainak megtekintése.',
                'dangerous' => false,
                'sort' => 310,
                'system' => true,
                'audit_sensitive' => false,
            ],

            [
                'name' => self::AUDIT_LOGS_VIEW,
                'module' => 'Roles & Permissions',
                'label' => 'Audit napló megtekintés',
                'description' => 'Jogosultsági, felhasználói és rendelés audit naplók megtekintése.',
                'dangerous' => false,
                'sort' => 315,
                'system' => true,
                'audit_sensitive' => false,
            ],
            [
                'name' => self::PERMISSIONS_VIEW,
                'module' => 'Roles & Permissions',
                'label' => 'Jogosultságok megtekintése',
                'description' => 'Jogosultság lista és részletek megtekintése.',
                'dangerous' => false,
                'sort' => 320,
                'system' => true,
                'audit_sensitive' => false,
            ],
            [
                'name' => self::PERMISSIONS_SYNC,
                'module' => 'Roles & Permissions',
                'label' => 'Jogosultság-registry szinkron',
                'description' => 'Jogosultság-registry és adatbázis szinkronizálása.',
                'dangerous' => true,
                'sort' => 330,
                'system' => true,
                'audit_sensitive' => true,
            ],
            [
                'name' => self::PERMISSIONS_VIEW_USAGE,
                'module' => 'Roles & Permissions',
                'label' => 'Jogosultság-használat megtekintés',
                'description' => 'Jogosultság használat szerepkör és felhasználó szinten.',
                'dangerous' => false,
                'sort' => 340,
                'system' => true,
                'audit_sensitive' => false,
            ],
            [
                'name' => self::SECURITY_DASHBOARD_VIEW,
                'module' => 'Security',
                'label' => 'Biztonsági irányítópult megtekintése',
                'description' => 'Biztonsági kockázati irányítópult és kritikus audit események megtekintése.',
                'dangerous' => true,
                'sort' => 350,
                'system' => true,
                'audit_sensitive' => true,
            ],
            [
                'name' => self::CONVERSION_ANALYTICS_VIEW,
                'module' => 'Analytics',
                'label' => 'Konverziós analitika megtekintése',
                'description' => 'CTA, kosár, checkout és regisztráció funnel statisztikák megtekintése.',
                'dangerous' => false,
                'sort' => 360,
                'system' => true,
                'audit_sensitive' => false,
            ],
            [
                'name' => self::PROFIT_DASHBOARD_VIEW,
                'module' => 'Analytics',
                'label' => 'Profit irányítópult megtekintése',
                'description' => 'Becsült önköltség, margin és profit trend elemzések megtekintése.',
                'dangerous' => false,
                'sort' => 370,
                'system' => true,
                'audit_sensitive' => false,
            ],
            [
                'name' => self::CEO_DASHBOARD_VIEW,
                'module' => 'Analytics',
                'label' => 'CEO irányítópult megtekintése',
                'description' => 'Revenue, profit, konverzió, repeat customer és audit összkép megtekintése.',
                'dangerous' => true,
                'sort' => 380,
                'system' => true,
                'audit_sensitive' => true,
            ],
            [
                'name' => self::SUPPLIERS_VIEW,
                'module' => 'Beszerzés',
                'label' => 'Beszállítók megtekintése',
                'description' => 'Beszállító lista és adatok megtekintése.',
                'dangerous' => false,
                'sort' => 390,
                'system' => true,
                'audit_sensitive' => false,
            ],
            [
                'name' => self::SUPPLIERS_MANAGE,
                'module' => 'Beszerzés',
                'label' => 'Beszállítók kezelése',
                'description' => 'Beszállítók létrehozása, szerkesztése, törlése.',
                'dangerous' => true,
                'sort' => 400,
                'system' => true,
                'audit_sensitive' => true,
            ],
            [
                'name' => self::PURCHASES_VIEW,
                'module' => 'Beszerzés',
                'label' => 'Beszerzések megtekintése',
                'description' => 'Beszerzési listák és részletek megtekintése.',
                'dangerous' => false,
                'sort' => 410,
                'system' => true,
                'audit_sensitive' => false,
            ],
            [
                'name' => self::PURCHASES_MANAGE,
                'module' => 'Beszerzés',
                'label' => 'Beszerzések kezelése',
                'description' => 'Beszerzések létrehozása, könyvelése és stornózása.',
                'dangerous' => true,
                'sort' => 420,
                'system' => true,
                'audit_sensitive' => true,
            ],
            [
                'name' => self::PROCUREMENT_INTELLIGENCE_VIEW,
                'module' => 'Beszerzés',
                'label' => 'Beszerzési intelligencia megtekintése',
                'description' => 'Beszállítói ártrendek, minimum készlet alapú utánrendelési javaslatok és beszerzési figyelmeztetések megtekintése.',
                'dangerous' => false,
                'sort' => 425,
                'system' => true,
                'audit_sensitive' => false,
            ],
            [
                'name' => self::INVENTORY_VIEW,
                'module' => 'Készlet',
                'label' => 'Készletmozgások megtekintése',
                'description' => 'Készlet főkönyv és részletes mozgásnapló megtekintése.',
                'dangerous' => false,
                'sort' => 430,
                'system' => true,
                'audit_sensitive' => false,
            ],
            [
                'name' => self::INVENTORY_ADJUST,
                'module' => 'Készlet',
                'label' => 'Készletkorrekció kezelése',
                'description' => 'Készlet korrekciós mozgások rögzítése.',
                'dangerous' => true,
                'sort' => 440,
                'system' => true,
                'audit_sensitive' => true,
            ],
            [
                'name' => self::WASTE_MANAGE,
                'module' => 'Készlet',
                'label' => 'Selejt kezelése',
                'description' => 'Selejt és veszteség mozgások rögzítése.',
                'dangerous' => true,
                'sort' => 450,
                'system' => true,
                'audit_sensitive' => true,
            ],
            [
                'name' => self::STOCK_COUNTS_MANAGE,
                'module' => 'Készlet',
                'label' => 'Leltár kezelése',
                'description' => 'Leltár indítása, módosítása és lezárása.',
                'dangerous' => true,
                'sort' => 460,
                'system' => true,
                'audit_sensitive' => true,
            ],
            [
                'name' => self::INVENTORY_DASHBOARD_VIEW,
                'module' => 'Készlet',
                'label' => 'Készlet dashboard megtekintése',
                'description' => 'Készletérték, alacsony készlet és selejt metrikák megtekintése.',
                'dangerous' => false,
                'sort' => 470,
                'system' => true,
                'audit_sensitive' => false,
            ],

        ];
    }

    /**
     * @return array<string, array<int, array{name:string,label:string,description:string,dangerous:bool,sort:int,system:bool,audit_sensitive:bool}>>
     */
    public static function groupedDefinitions(): array
    {
        $groups = [];

        foreach (self::definitions() as $definition) {
            $groups[$definition['module']][] = [
                'name' => $definition['name'],
                'label' => $definition['label'],
                'description' => $definition['description'],
                'dangerous' => $definition['dangerous'],
                'sort' => $definition['sort'],
                'system' => $definition['system'],
                'audit_sensitive' => $definition['audit_sensitive'],
            ];
        }

        foreach ($groups as $module => $items) {
            usort($items, static fn (array $a, array $b): int => $a['sort'] <=> $b['sort']);
            $groups[$module] = $items;
        }

        return $groups;
    }

    /**
     * @return array<string, array{name:string,module:string,label:string,description:string,dangerous:bool,sort:int,system:bool,audit_sensitive:bool}>
     */
    public static function definitionsByName(): array
    {
        if (self::$definitionsByNameCache !== null) {
            return self::$definitionsByNameCache;
        }

        $map = [];
        foreach (self::definitions() as $definition) {
            $map[$definition['name']] = $definition;
        }

        self::$definitionsByNameCache = $map;

        return self::$definitionsByNameCache;
    }

    /**
     * @return array{name:string,module:string,label:string,description:string,dangerous:bool,sort:int,system:bool,audit_sensitive:bool}|null
     */
    public static function definition(string $permissionName): ?array
    {
        return self::definitionsByName()[$permissionName] ?? null;
    }

    public static function isSystemRole(string $roleName): bool
    {
        return \in_array($roleName, self::systemRoles(), true);
    }
}
