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
    public const SECURITY_DASHBOARD_VIEW = 'security-dashboard.view';

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
            self::PERMISSIONS_VIEW,
            self::PERMISSIONS_SYNC,
            self::SECURITY_DASHBOARD_VIEW,
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
                'label' => 'Admin panel eleres',
                'description' => 'Admin feluletre belepes engedelyezese.',
                'dangerous' => false,
                'sort' => 10,
                'system' => true,
                'audit_sensitive' => false,
            ],
            [
                'name' => self::ORDERS_VIEW,
                'module' => 'Orders',
                'label' => 'Rendelesek megtekintese',
                'description' => 'Rendeles lista es reszletek megtekintese.',
                'dangerous' => false,
                'sort' => 20,
                'system' => true,
                'audit_sensitive' => false,
            ],
            [
                'name' => self::ORDERS_UPDATE,
                'module' => 'Orders',
                'label' => 'Rendelesek szerkesztese',
                'description' => 'Rendeles statusz es belso jegyzet modositas.',
                'dangerous' => true,
                'sort' => 30,
                'system' => true,
                'audit_sensitive' => true,
            ],
            [
                'name' => self::PRODUCTS_VIEW,
                'module' => 'Products',
                'label' => 'Termekek megtekintese',
                'description' => 'Termek lista megtekintese adminban.',
                'dangerous' => false,
                'sort' => 40,
                'system' => true,
                'audit_sensitive' => false,
            ],
            [
                'name' => self::PRODUCTS_CREATE,
                'module' => 'Products',
                'label' => 'Termek letrehozas',
                'description' => 'Uj termek letrehozas.',
                'dangerous' => false,
                'sort' => 50,
                'system' => true,
                'audit_sensitive' => false,
            ],
            [
                'name' => self::PRODUCTS_UPDATE,
                'module' => 'Products',
                'label' => 'Termek szerkesztes',
                'description' => 'Meglevo termek adatok szerkesztese.',
                'dangerous' => false,
                'sort' => 60,
                'system' => true,
                'audit_sensitive' => false,
            ],
            [
                'name' => self::PRODUCTS_DELETE,
                'module' => 'Products',
                'label' => 'Termek torles',
                'description' => 'Termek archiv/torles muvelet.',
                'dangerous' => true,
                'sort' => 70,
                'system' => true,
                'audit_sensitive' => true,
            ],
            [
                'name' => self::CATEGORIES_VIEW,
                'module' => 'Categories',
                'label' => 'Kategoriak megtekintese',
                'description' => 'Kategoriak listazasa adminban.',
                'dangerous' => false,
                'sort' => 80,
                'system' => true,
                'audit_sensitive' => false,
            ],
            [
                'name' => self::CATEGORIES_CREATE,
                'module' => 'Categories',
                'label' => 'Kategoria letrehozas',
                'description' => 'Uj kategoria letrehozas.',
                'dangerous' => false,
                'sort' => 90,
                'system' => true,
                'audit_sensitive' => false,
            ],
            [
                'name' => self::CATEGORIES_UPDATE,
                'module' => 'Categories',
                'label' => 'Kategoria szerkesztes',
                'description' => 'Kategoriak szerkesztese.',
                'dangerous' => false,
                'sort' => 100,
                'system' => true,
                'audit_sensitive' => false,
            ],
            [
                'name' => self::CATEGORIES_DELETE,
                'module' => 'Categories',
                'label' => 'Kategoria torles',
                'description' => 'Kategoriak torlese.',
                'dangerous' => true,
                'sort' => 110,
                'system' => true,
                'audit_sensitive' => true,
            ],
            [
                'name' => self::INGREDIENTS_VIEW,
                'module' => 'Ingredients',
                'label' => 'Alapanyagok megtekintese',
                'description' => 'Alapanyag lista megtekintese.',
                'dangerous' => false,
                'sort' => 120,
                'system' => true,
                'audit_sensitive' => false,
            ],
            [
                'name' => self::INGREDIENTS_CREATE,
                'module' => 'Ingredients',
                'label' => 'Alapanyag letrehozas',
                'description' => 'Uj alapanyag letrehozas.',
                'dangerous' => false,
                'sort' => 130,
                'system' => true,
                'audit_sensitive' => false,
            ],
            [
                'name' => self::INGREDIENTS_UPDATE,
                'module' => 'Ingredients',
                'label' => 'Alapanyag szerkesztes',
                'description' => 'Alapanyag adatok modositas.',
                'dangerous' => false,
                'sort' => 140,
                'system' => true,
                'audit_sensitive' => false,
            ],
            [
                'name' => self::INGREDIENTS_DELETE,
                'module' => 'Ingredients',
                'label' => 'Alapanyag torles',
                'description' => 'Alapanyag inaktivalas/torles.',
                'dangerous' => true,
                'sort' => 150,
                'system' => true,
                'audit_sensitive' => true,
            ],
            [
                'name' => self::WEEKLY_MENU_VIEW,
                'module' => 'Weekly Menu',
                'label' => 'Heti menu megtekintese',
                'description' => 'Heti menu admin oldal elerese.',
                'dangerous' => false,
                'sort' => 160,
                'system' => true,
                'audit_sensitive' => false,
            ],
            [
                'name' => self::WEEKLY_MENU_CREATE,
                'module' => 'Weekly Menu',
                'label' => 'Heti menu letrehozas',
                'description' => 'Uj heti menu letrehozas.',
                'dangerous' => false,
                'sort' => 170,
                'system' => true,
                'audit_sensitive' => false,
            ],
            [
                'name' => self::WEEKLY_MENU_UPDATE,
                'module' => 'Weekly Menu',
                'label' => 'Heti menu szerkesztes',
                'description' => 'Heti menu es itemek modositas.',
                'dangerous' => false,
                'sort' => 180,
                'system' => true,
                'audit_sensitive' => false,
            ],
            [
                'name' => self::WEEKLY_MENU_DELETE,
                'module' => 'Weekly Menu',
                'label' => 'Heti menu torles',
                'description' => 'Heti menuk torlese.',
                'dangerous' => true,
                'sort' => 190,
                'system' => true,
                'audit_sensitive' => true,
            ],
            [
                'name' => self::PRODUCTION_PLANS_VIEW,
                'module' => 'Production Plans',
                'label' => 'Gyartasi tervek megtekintese',
                'description' => 'Gyartastervezo felulet megtekintese.',
                'dangerous' => false,
                'sort' => 200,
                'system' => true,
                'audit_sensitive' => false,
            ],
            [
                'name' => self::PRODUCTION_PLANS_CREATE,
                'module' => 'Production Plans',
                'label' => 'Gyartasi terv letrehozas',
                'description' => 'Uj gyartasi terv letrehozasa.',
                'dangerous' => false,
                'sort' => 210,
                'system' => true,
                'audit_sensitive' => false,
            ],
            [
                'name' => self::PRODUCTION_PLANS_UPDATE,
                'module' => 'Production Plans',
                'label' => 'Gyartasi terv szerkesztes',
                'description' => 'Gyartasi terv frissites.',
                'dangerous' => false,
                'sort' => 220,
                'system' => true,
                'audit_sensitive' => false,
            ],
            [
                'name' => self::PRODUCTION_PLANS_DELETE,
                'module' => 'Production Plans',
                'label' => 'Gyartasi terv torles',
                'description' => 'Gyartasi terv torlese.',
                'dangerous' => true,
                'sort' => 230,
                'system' => true,
                'audit_sensitive' => true,
            ],
            [
                'name' => self::ACCOUNT_VIEW,
                'module' => 'Account',
                'label' => 'Fiok megtekintese',
                'description' => 'Sajat fiok oldal elerese.',
                'dangerous' => false,
                'sort' => 240,
                'system' => true,
                'audit_sensitive' => false,
            ],
            [
                'name' => self::ROLES_VIEW,
                'module' => 'Roles & Permissions',
                'label' => 'Szerepkorok megtekintese',
                'description' => 'Szerepkor menedzsment oldalak megtekintese.',
                'dangerous' => false,
                'sort' => 250,
                'system' => true,
                'audit_sensitive' => false,
            ],
            [
                'name' => self::ROLES_CREATE,
                'module' => 'Roles & Permissions',
                'label' => 'Szerepkor letrehozas',
                'description' => 'Uj szerepkor letrehozasa.',
                'dangerous' => false,
                'sort' => 260,
                'system' => true,
                'audit_sensitive' => true,
            ],
            [
                'name' => self::ROLES_UPDATE,
                'module' => 'Roles & Permissions',
                'label' => 'Szerepkor szerkesztes',
                'description' => 'Szerepkor atnevezese es frissitese.',
                'dangerous' => false,
                'sort' => 270,
                'system' => true,
                'audit_sensitive' => true,
            ],
            [
                'name' => self::ROLES_DELETE,
                'module' => 'Roles & Permissions',
                'label' => 'Szerepkor torles',
                'description' => 'Nem rendszer szerepkor torlese.',
                'dangerous' => true,
                'sort' => 280,
                'system' => true,
                'audit_sensitive' => true,
            ],
            [
                'name' => self::ROLES_ASSIGN_PERMISSIONS,
                'module' => 'Roles & Permissions',
                'label' => 'Szerepkor jogosultsag kiosztas',
                'description' => 'Role permission matrix szerkesztese.',
                'dangerous' => true,
                'sort' => 290,
                'system' => true,
                'audit_sensitive' => true,
            ],
            [
                'name' => self::USERS_ASSIGN_ROLES,
                'module' => 'Roles & Permissions',
                'label' => 'User role kiosztas',
                'description' => 'Felhasznalo szerepkoreinek szerkesztese.',
                'dangerous' => true,
                'sort' => 300,
                'system' => true,
                'audit_sensitive' => true,
            ],
            [
                'name' => self::USERS_VIEW_PERMISSIONS,
                'module' => 'Roles & Permissions',
                'label' => 'User jogosultsag betekintes',
                'description' => 'Felhasznalo effektive jogosultsagainak megtekintese.',
                'dangerous' => false,
                'sort' => 310,
                'system' => true,
                'audit_sensitive' => false,
            ],
<<<<<<< .mine
            [
                'name' => self::AUDIT_LOGS_VIEW,
                'module' => 'Roles & Permissions',
                'label' => 'Audit naplo megtekintes',
                'description' => 'Authorization, user activity es order audit naplok megtekintese.',
                'dangerous' => false,
                'sort' => 320,
            ],
































=======
            [
                'name' => self::PERMISSIONS_VIEW,
                'module' => 'Roles & Permissions',
                'label' => 'Jogosultsagok megtekintese',
                'description' => 'Permission lista es reszletek megtekintese.',
                'dangerous' => false,
                'sort' => 320,
                'system' => true,
                'audit_sensitive' => false,
            ],
            [
                'name' => self::PERMISSIONS_SYNC,
                'module' => 'Roles & Permissions',
                'label' => 'Permission registry sync',
                'description' => 'Permission registry es adatbazis szinkronizalasa.',
                'dangerous' => true,
                'sort' => 330,
                'system' => true,
                'audit_sensitive' => true,
            ],
            [
                'name' => self::PERMISSIONS_VIEW_USAGE,
                'module' => 'Roles & Permissions',
                'label' => 'Permission usage megtekintes',
                'description' => 'Permission hasznalat role es user szinten.',
                'dangerous' => false,
                'sort' => 340,
                'system' => true,
                'audit_sensitive' => false,
            ],
            [
                'name' => self::SECURITY_DASHBOARD_VIEW,
                'module' => 'Security',
                'label' => 'Security dashboard megtekintese',
                'description' => 'Security kockazati dashboard es kritikus audit esemenyek megtekintese.',
                'dangerous' => true,
                'sort' => 350,
                'system' => true,
                'audit_sensitive' => true,
            ],
>>>>>>> .theirs
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
        $map = [];

        foreach (self::definitions() as $definition) {
            $map[$definition['name']] = $definition;
        }

        return $map;
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
