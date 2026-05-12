<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\Admin\AuthorizationAuditController as AdminAuthorizationAuditController;
use App\Http\Controllers\Admin\BranchController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CeoDashboardController as AdminCeoDashboardController;
use App\Http\Controllers\Admin\ConversionAnalyticsController as AdminConversionAnalyticsController;
use App\Http\Controllers\Admin\CourierController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\IngredientController;
use App\Http\Controllers\Admin\IngredientSupplierTermController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\PermissionController as AdminPermissionController;
use App\Http\Controllers\Admin\ProcurementIntelligenceController as AdminProcurementIntelligenceController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProductIngredientController;
use App\Http\Controllers\Admin\ProductionPlanController;
use App\Http\Controllers\Admin\ProfitDashboardController as AdminProfitDashboardController;
use App\Http\Controllers\Admin\PurchaseController as AdminPurchaseController;
use App\Http\Controllers\Admin\RecipeController;
use App\Http\Controllers\Admin\RecipeStepController;
use App\Http\Controllers\Admin\RoleController as AdminRoleController;
use App\Http\Controllers\Admin\SecurityDashboardController as AdminSecurityDashboardController;
use App\Http\Controllers\Admin\StockCountController as AdminStockCountController;
use App\Http\Controllers\Admin\SupplierController as AdminSupplierController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\UserRoleController as AdminUserRoleController;
use App\Http\Controllers\Admin\WeeklyMenuController;
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

Route::prefix('cart')->name('cart.')->controller(CartController::class)->group(function (): void {
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

        Route::name('branches.')->prefix('branches')->controller(BranchController::class)->group(function (): void {
            Route::get('/', 'index')->name('index');
            Route::post('/', 'store')->name('store');
            Route::put('/{branch}', 'update')->name('update');
            Route::delete('/{branch}', 'destroy')->name('destroy');
        });

        Route::name('couriers.')->prefix('couriers')->controller(CourierController::class)->group(function (): void {
            Route::get('/', 'index')->name('index');
            Route::post('/', 'store')->name('store');
            Route::put('/{courier}', 'update')->name('update');
            Route::delete('/{courier}', 'destroy')->name('destroy');
        });

        Route::name('users.')->prefix('users')->controller(AdminUserController::class)->group(function (): void {
            Route::get('/', 'index')->name('index');
            Route::post('/', 'store')->name('store');
            Route::put('/{user}', 'update')->name('update');
            Route::delete('/{user}', 'destroy')->name('destroy');
            Route::post('/{user}/temporary-permissions', 'storeTemporaryPermission')->name('temporary-permissions.store');
            Route::delete('/{user}/temporary-permissions/{temporaryPermission}', 'revokeTemporaryPermission')->name('temporary-permissions.destroy');
            Route::post('/{user}/discounts', 'storeDiscount')->name('discounts.store');
            Route::put('/{user}/discounts/{discount}', 'updateDiscount')->name('discounts.update');
            Route::delete('/{user}/discounts/{discount}', 'destroyDiscount')->name('discounts.destroy');
        });

        Route::prefix('products')->name('products.')->group(function (): void {

            Route::get('/', [ProductController::class, 'index'])->name('index');
            Route::get('/create-flow', [ProductController::class, 'createFlow'])->name('create-flow');
            Route::post('/create-flow', [ProductController::class, 'storeFlow'])->name('create-flow.store');
            Route::post('/', [ProductController::class, 'store'])->name('store');
            Route::patch('/{product}/inline', [ProductController::class, 'updateInline'])->name('inline.update');
            Route::put('/{product}', [ProductController::class, 'update'])->name('update');
            Route::delete('/{product}', [ProductController::class, 'destroy'])->name('destroy');

            /*
            |--------------------------------------------------------------------------
            | Product Ingredients
            |--------------------------------------------------------------------------
            */
            Route::prefix('{product}/ingredients')->name('ingredients.')->group(function (): void {
                Route::post('/', [ProductIngredientController::class, 'store'])->name('store');
                Route::put('/{productIngredient}', [ProductIngredientController::class, 'update'])->name('update');
                Route::delete('/{productIngredient}', [ProductIngredientController::class, 'destroy'])->name('destroy');
            });

            /*
            |--------------------------------------------------------------------------
            | Recipe Steps
            |--------------------------------------------------------------------------
            */
            Route::prefix('{product}/recipe-steps')->name('recipe-steps.')->group(function (): void {
                Route::post('/', [RecipeStepController::class, 'store'])->name('store');
                Route::put('/{recipeStep}', [RecipeStepController::class, 'update'])->name('update');
                Route::delete('/{recipeStep}', [RecipeStepController::class, 'destroy'])->name('destroy');
            });
        });

        Route::prefix('ingredients')->name('ingredients.')->group(function (): void {
            Route::get('/', [IngredientController::class, 'index'])->name('index');
            Route::post('/', [IngredientController::class, 'store'])->name('store');
            Route::patch('/{ingredient}/inline', [IngredientController::class, 'updateInline'])->name('inline.update');
            Route::put('/{ingredient}', [IngredientController::class, 'update'])->name('update');
            Route::delete('/{ingredient}', [IngredientController::class, 'destroy'])->name('destroy');
        });

        Route::get('/recipes', [RecipeController::class, 'index'])->name('recipes.index');

        Route::name('production-plans.')->prefix('production-plans')->controller(ProductionPlanController::class)->group(function (): void {
            Route::get('/', 'index')->name('index');
            Route::get('/create-flow', 'createFlow')->name('create-flow');
            Route::post('/create-flow', 'storeFlow')->name('create-flow.store');
            Route::post('/', 'store')->name('store');
            Route::get('/{productionPlan}', 'show')->name('show');
            Route::put('/{productionPlan}', 'update')->name('update');
            Route::delete('/{productionPlan}', 'destroy')->name('destroy');
        });

        Route::name('weekly-menus.')->prefix('weekly-menus')->controller(WeeklyMenuController::class)->group(function (): void {
            Route::get('/', 'index')->name('index');
            Route::post('/', 'store')->name('store');
            Route::patch('/{weeklyMenu}/inline', 'updateInline')->name('inline.update');
            Route::put('/{weeklyMenu}', 'update')->name('update');
            Route::delete('/{weeklyMenu}', 'destroy')->name('destroy');
            Route::post('/{weeklyMenu}/publish', 'publish')->name('publish');
            Route::post('/{weeklyMenu}/unpublish', 'unpublish')->name('unpublish');

            Route::post('/{weeklyMenu}/items', 'storeItem')->name('items.store');
            Route::put('/{weeklyMenu}/items/{item}', 'updateItem')->name('items.update');
            Route::delete('/{weeklyMenu}/items/{item}', 'destroyItem')->name('items.destroy');
        });

        Route::name('orders.')->prefix('orders')->controller(AdminOrderController::class)->group(function (): void {
            Route::get('/', 'index')->name('index');
            Route::get('/{order}', 'show')->name('show');
            Route::patch('/{order}/status', 'updateStatus')->name('status.update');
            Route::post('/{order}/delivery/assign', 'assignCourier')->name('delivery.assign');
            Route::post('/{order}/delivery/start', 'startDelivery')->name('delivery.start');
            Route::post('/{order}/delivery/delivered', 'markDelivered')->name('delivery.delivered');
            Route::post('/{order}/delivery/failed', 'markFailed')->name('delivery.failed');
            Route::post('/{order}/delivery/cancel', 'cancelDelivery')->name('delivery.cancel');
        });

        Route::prefix('roles')->name('roles.')->middleware('permission:roles.view')->group(function (): void {
            Route::get('/', [AdminRoleController::class, 'index'])->name('index');
            Route::get('/{role}', [AdminRoleController::class, 'show'])->name('show');
            Route::post('/', [AdminRoleController::class, 'store'])->middleware('permission:roles.create')->name('store');
            Route::put('/{role}', [AdminRoleController::class, 'update'])->middleware('permission:roles.update')->name('update');
            Route::delete('/{role}', [AdminRoleController::class, 'destroy'])->middleware('permission:roles.delete')->name('destroy');
            Route::put('/{role}/permissions', [AdminRoleController::class, 'syncPermissions'])->middleware('permission:roles.assign-permissions')->name('permissions.sync');
        });

        Route::get('/user-roles', [AdminUserRoleController::class, 'index'])->middleware('role_or_permission:users.assign-roles|users.view-permissions')->name('user-roles.index');
        Route::put('/users/{user}/roles', [AdminUserRoleController::class, 'update'])->middleware('permission:users.assign-roles')->name('users.roles.update');

        Route::prefix('permissions')->name('permissions.')->middleware('permission:permissions.view')->group(function (): void {
            Route::get('/', [AdminPermissionController::class, 'index'])->name('index');
            Route::post('/sync', [AdminPermissionController::class, 'sync'])->middleware('permission:permissions.sync')->name('sync');
            Route::get('/{permissionName}', [AdminPermissionController::class, 'show'])
                ->where('permissionName', '[A-Za-z0-9\.\-_]+')
                ->name('show');
        });

        Route::prefix('audit-logs')->name('audit-logs.')->middleware('permission:audit-logs.view')->group(function (): void {
            Route::get('/', [AdminAuthorizationAuditController::class, 'index'])->name('index');
            Route::get('/{activity}', [AdminAuthorizationAuditController::class, 'show'])->whereNumber('activity')->name('show');
        });

        Route::prefix('security-dashboard')->name('security-dashboard.')->middleware('permission:security-dashboard.view')->group(function (): void {
            Route::get('/', [AdminSecurityDashboardController::class, 'index'])->name('index');
            Route::get('/events/{activity}', [AdminSecurityDashboardController::class, 'showEvent'])->whereNumber('activity')->name('events.show');
        });

        Route::get('/conversion-analytics', [AdminConversionAnalyticsController::class, 'index'])->middleware('permission:conversion-analytics.view')->name('conversion-analytics.index');
        Route::get('/profit-dashboard', [AdminProfitDashboardController::class, 'index'])->middleware('permission:profit-dashboard.view')->name('profit-dashboard.index');

        Route::get('/ceo-dashboard', [AdminCeoDashboardController::class, 'index'])->middleware('permission:ceo-dashboard.view')->name('ceo-dashboard.index');

        Route::name('suppliers.')->prefix('suppliers')->controller(AdminSupplierController::class)->group(function (): void {
            Route::get('/', 'index')->middleware('permission:suppliers.view')->name('index');
            Route::post('/', 'store')->middleware('permission:suppliers.manage')->name('store');
            Route::put('/{supplier}', 'update')->middleware('permission:suppliers.manage')->name('update');
            Route::delete('/{supplier}', 'destroy')->middleware('permission:suppliers.manage')->name('destroy');
        });

        Route::name('ingredient-supplier-terms.')->prefix('ingredient-supplier-terms')->controller(IngredientSupplierTermController::class)->group(function (): void {
            Route::get('/', 'index')->middleware('permission:suppliers.view')->name('index');
            Route::post('/', 'store')->middleware('permission:suppliers.manage')->name('store');
            Route::patch('/{ingredientSupplierTerm}/inline', 'updateInline')->middleware('permission:suppliers.manage')->name('inline.update');
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

        Route::prefix('procurement-intelligence')->name('procurement-intelligence.')->middleware('permission:procurement-intelligence.view')->group(function (): void {
            Route::get('/', [AdminProcurementIntelligenceController::class, 'index'])->name('index');
            Route::post('/purchase-drafts', [AdminProcurementIntelligenceController::class, 'generatePurchaseDrafts'])->middleware('permission:purchases.manage')->name('purchase-drafts.store');
        });

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
