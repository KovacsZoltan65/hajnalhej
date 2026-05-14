<?php

declare(strict_types=1);

namespace App\Support;

use Inertia\Inertia;
use Inertia\Response;

enum InertiaPage: string
{
    case ACCOUNT_INDEX = 'Account/Index';

    case ADMIN_AUDIT_LOGS_INDEX = 'Admin/AuditLogs/Index';
    case ADMIN_AUDIT_LOGS_SHOW = 'Admin/AuditLogs/Show';
    case ADMIN_BRANCHES_INDEX = 'Admin/Branches/Index';
    case ADMIN_CATEGORIES_INDEX = 'Admin/Categories/Index';
    case ADMIN_CEO_DASHBOARD_INDEX = 'Admin/CeoDashboard/Index';
    case ADMIN_CONVERSION_ANALYTICS_INDEX = 'Admin/ConversionAnalytics/Index';
    case ADMIN_COURIERS_INDEX = 'Admin/Couriers/Index';
    case ADMIN_DASHBOARD = 'Admin/Dashboard';
    case ADMIN_EXPORTS_INDEX = 'Admin/Exports/Index';
    case ADMIN_INGREDIENTS_INDEX = 'Admin/Ingredients/Index';
    case ADMIN_INGREDIENT_SUPPLIER_TERMS_INDEX = 'Admin/IngredientSupplierTerms/Index';
    case ADMIN_INVENTORY_INDEX = 'Admin/Inventory/Index';
    case ADMIN_ORDERS_INDEX = 'Admin/Orders/Index';
    case ADMIN_ORDERS_SHOW = 'Admin/Orders/Show';
    case ADMIN_PERMISSIONS_INDEX = 'Admin/Permissions/Index';
    case ADMIN_PERMISSIONS_SHOW = 'Admin/Permissions/Show';
    case ADMIN_PROCUREMENT_INTELLIGENCE_INDEX = 'Admin/ProcurementIntelligence/Index';
    case ADMIN_PRODUCTION_PLANS_INDEX = 'Admin/ProductionPlans/Index';
    case ADMIN_PRODUCTION_PLANS_CREATE_FLOW = 'Admin/ProductionPlans/CreateFlow';
    case ADMIN_PRODUCTION_PLANS_SHOW = 'Admin/ProductionPlans/Show';
    case ADMIN_PRODUCTS_INDEX = 'Admin/Products/Index';
    case ADMIN_PRODUCTS_CREATE_FLOW = 'Admin/Products/CreateFlow';
    case ADMIN_PROFIT_DASHBOARD_INDEX = 'Admin/ProfitDashboard/Index';
    case ADMIN_PURCHASES_INDEX = 'Admin/Purchases/Index';
    case ADMIN_PURCHASES_SHOW = 'Admin/Purchases/Show';
    case ADMIN_RECIPES_INDEX = 'Admin/Recipes/Index';
    case ADMIN_ROLES_INDEX = 'Admin/Roles/Index';
    case ADMIN_ROLES_SHOW = 'Admin/Roles/Show';
    case ADMIN_SECURITY_DASHBOARD_INDEX = 'Admin/SecurityDashboard/Index';
    case ADMIN_SECURITY_DASHBOARD_EVENT = 'Admin/SecurityDashboard/Event';
    case ADMIN_STOCK_COUNTS_INDEX = 'Admin/StockCounts/Index';
    case ADMIN_STOCK_COUNTS_SHOW = 'Admin/StockCounts/Show';
    case ADMIN_SUPPLIERS_INDEX = 'Admin/Suppliers/Index';
    case ADMIN_USER_ROLES_INDEX = 'Admin/UserRoles/Index';
    case ADMIN_USERS_INDEX = 'Admin/Users/Index';
    case ADMIN_WEEKLY_MENUS_INDEX = 'Admin/WeeklyMenus/Index';

    case AUTH_LOGIN = 'Auth/Login';
    case AUTH_REGISTER = 'Auth/Register';
    case AUTH_VERIFY_EMAIL = 'Auth/VerifyEmail';

    case ABOUT = 'About';
    case CART_INDEX = 'Cart/Index';
    case CHECKOUT_INDEX = 'Checkout/Index';
    case HOME = 'Home';
    case ORDERS_SUCCESS = 'Orders/Success';
    case WEEKLY_MENU = 'WeeklyMenu';

    public function component(): string
    {
        return $this->value;
    }

    public function render(array $props = []): Response
    {
        return Inertia::render($this->value, $props);
    }
}
