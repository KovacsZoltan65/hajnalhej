<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\IngredientController;
use App\Http\Controllers\Admin\IngredientSupplierTermController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\AuthorizationAuditController as AdminAuthorizationAuditController;
use App\Http\Controllers\Admin\RoleController as AdminRoleController;
use App\Http\Controllers\Admin\UserRoleController as AdminUserRoleController;
use App\Http\Controllers\Admin\PermissionController as AdminPermissionController;
use App\Http\Controllers\Admin\SecurityDashboardController as AdminSecurityDashboardController;
use App\Http\Controllers\Admin\ConversionAnalyticsController as AdminConversionAnalyticsController;
use App\Http\Controllers\Admin\ProfitDashboardController as AdminProfitDashboardController;
use App\Http\Controllers\Admin\ProcurementIntelligenceController as AdminProcurementIntelligenceController;
use App\Http\Controllers\Admin\CeoDashboardController as AdminCeoDashboardController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\RecipeController;
use App\Http\Controllers\Admin\RecipeStepController;
use App\Http\Controllers\Admin\ProductIngredientController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\PurchaseController as AdminPurchaseController;
use App\Http\Controllers\Admin\ProductionPlanController;
use App\Http\Controllers\Admin\StockCountController as AdminStockCountController;
use App\Http\Controllers\Admin\SupplierController as AdminSupplierController;
use App\Http\Controllers\Admin\WeeklyMenuController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ConversionTrackingController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PreferenceController;
use App\Http\Controllers\PublicPageController;
use Illuminate\Support\Facades\Route;

Route::post('/preferences/locale', [PreferenceController::class, 'setLocale'])->name('preferences.locale');

Route::controller(PublicPageController::class)->group(function (): void {
    Route::get('/', 'home')->name('home');
    Route::get('/weekly-menu', 'weeklyMenu')->name('weekly-menu');
    Route::get('/about', 'about')->name('about');
});

/*
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/items', [CartController::class, 'store'])->name('cart.items.store');
Route::patch('/cart/items/{productId}', [CartController::class, 'update'])->name('cart.items.update');
Route::delete('/cart/items/{productId}', [CartController::class, 'destroy'])->name('cart.items.destroy');
Route::delete('/cart', [CartController::class, 'clear'])->name('cart.clear');
*/

Route::prefix('cart')->name('cart.')->controller(CartController::class)->group(function() {
    Route::get('/', 'index')->name('index');
    Route::post('/items', 'store')->name('items.store');
    Route::patch('/items/{productId}', 'update')->name('items.update');
    Route::delete('/items/{productId}', 'destroy')->name('items.destroy');
    Route::delete('/', 'clear')->name('clear');
});

Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
Route::get('/orders/success/{order}', [OrderController::class, 'success'])->name('orders.success');
Route::post('/conversion-events', [ConversionTrackingController::class, 'store'])
    ->middleware('throttle:120,1')
    ->name('conversion-events.store');

Route::middleware('guest')->group(function (): void {
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store'])->name('register.store');

    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
});

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
    Route::get('/account', AccountController::class)->name('account');

    Route::get('/email/verify', EmailVerificationPromptController::class)->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');
    Route::post('/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    Route::prefix('admin')->middleware('permission:admin.panel.access')->name('admin.')->group(function (): void {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
        Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
        Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');

        Route::get('/products', [ProductController::class, 'index'])->name('products.index');
        Route::post('/products', [ProductController::class, 'store'])->name('products.store');
        Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
        Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
        Route::post('/products/{product}/ingredients', [ProductIngredientController::class, 'store'])->name('products.ingredients.store');
        Route::put('/products/{product}/ingredients/{productIngredient}', [ProductIngredientController::class, 'update'])->name('products.ingredients.update');
        Route::delete('/products/{product}/ingredients/{productIngredient}', [ProductIngredientController::class, 'destroy'])->name('products.ingredients.destroy');
        Route::post('/products/{product}/recipe-steps', [RecipeStepController::class, 'store'])->name('products.recipe-steps.store');
        Route::put('/products/{product}/recipe-steps/{recipeStep}', [RecipeStepController::class, 'update'])->name('products.recipe-steps.update');
        Route::delete('/products/{product}/recipe-steps/{recipeStep}', [RecipeStepController::class, 'destroy'])->name('products.recipe-steps.destroy');

        Route::get('/ingredients', [IngredientController::class, 'index'])->name('ingredients.index');
        Route::post('/ingredients', [IngredientController::class, 'store'])->name('ingredients.store');
        Route::put('/ingredients/{ingredient}', [IngredientController::class, 'update'])->name('ingredients.update');
        Route::delete('/ingredients/{ingredient}', [IngredientController::class, 'destroy'])->name('ingredients.destroy');

        Route::get('/recipes', [RecipeController::class, 'index'])->name('recipes.index');

        Route::name('production-plans.')->prefix('production-plans')->controller(ProductionPlanController::class)->group(function() {
            Route::get('/', 'index')->name('index');
            Route::post('/', 'store')->name('store');
            Route::put('/{productionPlan}', 'update')->name('update');
            Route::delete('/{productionPlan}', 'destroy')->name('destroy');
        });
        //Route::get('/production-plans', [ProductionPlanController::class, 'index'])->name('production-plans.index');
        //Route::post('/production-plans', [ProductionPlanController::class, 'store'])->name('production-plans.store');
        //Route::put('/production-plans/{productionPlan}', [ProductionPlanController::class, 'update'])->name('production-plans.update');
        //Route::delete('/production-plans/{productionPlan}', [ProductionPlanController::class, 'destroy'])->name('production-plans.destroy');

        Route::name('weekly-menus.')->prefix('weekly-menus')->controller(WeeklyMenuController::class)->group(function() {
            Route::get('/', 'index')->name('index');
            Route::post('/', 'store')->name('store');
            Route::put('/{weeklyMenu}', 'update')->name('update');
            Route::delete('/{weeklyMenu}', 'destroy')->name('destroy');
            Route::post('/{weeklyMenu}/publish', 'publish')->name('publish');
            Route::post('/{weeklyMenu}/unpublish', 'unpublish')->name('unpublish');

            Route::post('/{weeklyMenu}/items', 'storeItem')->name('store');
            Route::put('/{weeklyMenu}/items/{item}', 'updateItem')->name('update');
            Route::delete('/{weeklyMenu}/items/{item}', 'destroyItem')->name('destroy');
        });

        //Route::get('/weekly-menus', [WeeklyMenuController::class, 'index'])->name('weekly-menus.index');
        //Route::post('/weekly-menus', [WeeklyMenuController::class, 'store'])->name('weekly-menus.store');
        //Route::put('/weekly-menus/{weeklyMenu}', [WeeklyMenuController::class, 'update'])->name('weekly-menus.update');
        //Route::delete('/weekly-menus/{weeklyMenu}', [WeeklyMenuController::class, 'destroy'])->name('weekly-menus.destroy');
        //Route::post('/weekly-menus/{weeklyMenu}/publish', [WeeklyMenuController::class, 'publish'])->name('weekly-menus.publish');
        //Route::post('/weekly-menus/{weeklyMenu}/unpublish', [WeeklyMenuController::class, 'unpublish'])->name('weekly-menus.unpublish');
        //Route::post('/weekly-menus/{weeklyMenu}/items', [WeeklyMenuController::class, 'storeItem'])->name('weekly-menus.items.store');
        //Route::put('/weekly-menus/{weeklyMenu}/items/{item}', [WeeklyMenuController::class, 'updateItem'])->name('weekly-menus.items.update');
        //Route::delete('/weekly-menus/{weeklyMenu}/items/{item}', [WeeklyMenuController::class, 'destroyItem'])->name('weekly-menus.items.destroy');

        Route::name('orders.')->prefix('orders')->controller(AdminOrderController::class)->group(function() {
            Route::get('/', 'index')->name('index');
            Route::get('/{order}', 'show')->name('show');
            Route::patch('/{order}/status', 'updateStatus')->name('status.update');
        });

        //Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
        //Route::get('/orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
        //Route::patch('/orders/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.status.update');

        Route::get('/roles', [AdminRoleController::class, 'index'])
            ->middleware('permission:roles.view')
            ->name('roles.index');
        Route::get('/roles/{role}', [AdminRoleController::class, 'show'])
            ->middleware('permission:roles.view')
            ->name('roles.show');
        Route::post('/roles', [AdminRoleController::class, 'store'])
            ->middleware('permission:roles.create')
            ->name('roles.store');
        Route::put('/roles/{role}', [AdminRoleController::class, 'update'])
            ->middleware('permission:roles.update')
            ->name('roles.update');
        Route::delete('/roles/{role}', [AdminRoleController::class, 'destroy'])
            ->middleware('permission:roles.delete')
            ->name('roles.destroy');
        Route::put('/roles/{role}/permissions', [AdminRoleController::class, 'syncPermissions'])
            ->middleware('permission:roles.assign-permissions')
            ->name('roles.permissions.sync');

        Route::get('/user-roles', [AdminUserRoleController::class, 'index'])
            ->middleware('role_or_permission:users.assign-roles|users.view-permissions')
            ->name('user-roles.index');
        Route::put('/users/{user}/roles', [AdminUserRoleController::class, 'update'])
            ->middleware('permission:users.assign-roles')
            ->name('users.roles.update');

        Route::get('/permissions', [AdminPermissionController::class, 'index'])
            ->middleware('permission:permissions.view')
            ->name('permissions.index');
        Route::post('/permissions/sync', [AdminPermissionController::class, 'sync'])
            ->middleware('permission:permissions.sync')
            ->name('permissions.sync');
        Route::get('/permissions/{permissionName}', [AdminPermissionController::class, 'show'])
            ->middleware('permission:permissions.view')
            ->name('permissions.show');

        Route::get('/audit-logs', [AdminAuthorizationAuditController::class, 'index'])
            ->middleware('permission:audit-logs.view')
            ->name('audit-logs.index');
        Route::get('/audit-logs/{activity}', [AdminAuthorizationAuditController::class, 'show'])
            ->middleware('permission:audit-logs.view')
            ->name('audit-logs.show');

        Route::get('/security-dashboard', [AdminSecurityDashboardController::class, 'index'])
            ->middleware('permission:security-dashboard.view')
            ->name('security-dashboard.index');
        Route::get('/security-dashboard/events/{activity}', [AdminSecurityDashboardController::class, 'showEvent'])
            ->middleware('permission:security-dashboard.view')
            ->name('security-dashboard.events.show');

        Route::get('/conversion-analytics', [AdminConversionAnalyticsController::class, 'index'])
            ->middleware('permission:conversion-analytics.view')
            ->name('conversion-analytics.index');
        Route::get('/profit-dashboard', [AdminProfitDashboardController::class, 'index'])
            ->middleware('permission:profit-dashboard.view')
            ->name('profit-dashboard.index');
        Route::get('/ceo-dashboard', [AdminCeoDashboardController::class, 'index'])
            ->middleware('permission:ceo-dashboard.view')
            ->name('ceo-dashboard.index');

        Route::name('suppliers.')->prefix('suppliers')->controller(AdminSupplierController::class)->group(function (): void {
            Route::get('/', 'index')->middleware('permission:suppliers.view')->name('index');
            Route::post('/', 'store')->middleware('permission:suppliers.manage')->name('store');
            Route::put('/{supplier}', 'update')->middleware('permission:suppliers.manage')->name('update');
            Route::delete('/{supplier}', 'destroy')->middleware('permission:suppliers.manage')->name('destroy');
        });

        Route::name('ingredient-supplier-terms.')->prefix('ingredient-supplier-terms')->controller(IngredientSupplierTermController::class)->group(function (): void {
            Route::get('/', 'index')->middleware('permission:suppliers.view')->name('index');
            Route::post('/', 'store')->middleware('permission:suppliers.manage')->name('store');
            Route::put('/{ingredientSupplierTerm}', 'update')->middleware('permission:suppliers.manage')->name('update');
            Route::delete('/{ingredientSupplierTerm}', 'destroy')->middleware('permission:suppliers.manage')->name('destroy');
        });

        Route::name('purchases.')->prefix('purchases')->controller(AdminPurchaseController::class)->group(function (): void {
            Route::get('/', 'index')->middleware('permission:purchases.view')->name('index');
            Route::get('/{purchase}', 'show')->middleware('permission:purchases.view')->name('show');
            Route::post('/', 'store')->middleware('permission:purchases.manage')->name('store');
            Route::put('/{purchase}', 'update')->middleware('permission:purchases.manage')->name('update');
            Route::post('/{purchase}/post', 'post')->middleware('permission:purchases.manage')->name('post');
            Route::post('/{purchase}/cancel', 'cancel')->middleware('permission:purchases.manage')->name('cancel');
        });

        Route::get('/procurement-intelligence', [AdminProcurementIntelligenceController::class, 'index'])
            ->middleware('permission:procurement-intelligence.view')
            ->name('procurement-intelligence.index');
        Route::post('/procurement-intelligence/purchase-drafts', [AdminProcurementIntelligenceController::class, 'generatePurchaseDrafts'])
            ->middleware('permission:purchases.manage')
            ->name('procurement-intelligence.purchase-drafts.store');

        Route::name('inventory.')->prefix('inventory')->controller(InventoryController::class)->group(function (): void {
            Route::get('/', 'index')->middleware('permission:inventory-dashboard.view')->name('index');
            Route::post('/waste', 'storeWaste')->middleware('permission:waste.manage')->name('waste.store');
            Route::post('/adjustments', 'storeAdjustment')->middleware('permission:inventory.adjust')->name('adjustments.store');
        });

        Route::name('stock-counts.')->prefix('stock-counts')->controller(AdminStockCountController::class)->group(function (): void {
            Route::get('/', 'index')->middleware('permission:inventory.view')->name('index');
            Route::get('/{stockCount}', 'show')->middleware('permission:inventory.view')->name('show');
            Route::post('/', 'store')->middleware('permission:stock-counts.manage')->name('store');
            Route::put('/{stockCount}', 'update')->middleware('permission:stock-counts.manage')->name('update');
            Route::post('/{stockCount}/close', 'close')->middleware('permission:stock-counts.manage')->name('close');
        });
    });
});
