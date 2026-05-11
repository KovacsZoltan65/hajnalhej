<?php

namespace App\Providers;

use App\Models\Branch;
use App\Models\Category;
use App\Models\ConversionEvent;
use App\Models\Courier;
use App\Models\Ingredient;
use App\Models\IngredientSupplierTerm;
use App\Models\InventoryMovement;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductionPlan;
use App\Models\Purchase;
use App\Models\StockCount;
use App\Models\Supplier;
use App\Models\User;
use App\Models\WeeklyMenu;
use App\Policies\AuthorizationAuditPolicy;
use App\Policies\BranchPolicy;
use App\Policies\CategoryPolicy;
use App\Policies\ConversionAnalyticsPolicy;
use App\Policies\CourierPolicy;
use App\Policies\IngredientPolicy;
use App\Policies\IngredientSupplierTermPolicy;
use App\Policies\InventoryMovementPolicy;
use App\Policies\OrderPolicy;
use App\Policies\PermissionPolicy;
use App\Policies\ProcurementIntelligencePolicy;
use App\Policies\ProductionPlanPolicy;
use App\Policies\ProductPolicy;
use App\Policies\PurchasePolicy;
use App\Policies\RolePolicy;
use App\Policies\SecurityDashboardPolicy;
use App\Policies\StockCountPolicy;
use App\Policies\SupplierPolicy;
use App\Policies\UserPolicy;
use App\Policies\WeeklyMenuPolicy;
use App\Support\PermissionRegistry;
use Illuminate\Auth\Middleware\RedirectIfAuthenticated;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;
use Session;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RedirectIfAuthenticated::redirectUsing(function () {
            $user = auth()->user();

            if ($user?->can(PermissionRegistry::ADMIN_PANEL_ACCESS)) {
                return route('admin.dashboard');
            }

            return route('account');
        });

        Gate::before(function ($user, string $ability): ?bool {
            if ($user->hasRole(PermissionRegistry::ROLE_ADMIN)) {
                return true;
            }

            return null;
        });

        Gate::define(
            'viewProcurementIntelligence',
            fn (User $user): bool => app(ProcurementIntelligencePolicy::class)->viewAny($user),
        );

        Gate::policy(Branch::class, BranchPolicy::class);
        Gate::policy(Category::class, CategoryPolicy::class);
        Gate::policy(ConversionEvent::class, ConversionAnalyticsPolicy::class);
        Gate::policy(Courier::class, CourierPolicy::class);
        Gate::policy(Ingredient::class, IngredientPolicy::class);
        Gate::policy(IngredientSupplierTerm::class, IngredientSupplierTermPolicy::class);
        Gate::policy(InventoryMovement::class, InventoryMovementPolicy::class);
        Gate::policy(Order::class, OrderPolicy::class);
        Gate::policy(Permission::class, PermissionPolicy::class);
        Gate::policy(Purchase::class, PurchasePolicy::class);
        Gate::policy(Product::class, ProductPolicy::class);
        Gate::policy(ProductionPlan::class, ProductionPlanPolicy::class);
        Gate::policy(Activity::class, AuthorizationAuditPolicy::class);
        Gate::policy(Role::class, RolePolicy::class);
        Gate::policy(Activity::class, SecurityDashboardPolicy::class);
        Gate::policy(StockCount::class, StockCountPolicy::class);
        Gate::policy(Supplier::class, SupplierPolicy::class);
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(WeeklyMenu::class, WeeklyMenuPolicy::class);

        $app_locale = app()->getLocale();
        $csrf_token = csrf_token();

        Inertia::share([
            'available_locales' => fn (): array => config('app.available_locales'),
            'locale' => fn (): string => $app_locale,
            'preferences' => fn (): array => [
                'locale' => $app_locale,
                'currency' => config('app.currency', 'HUF'),
                'timezone' => session('timezone', config('app.timezone', 'UTC')),
                'theme' => session('theme', 'system'),
            ],
        ]);

        Inertia::share('flash', fn () => ['message' => Session::get('message')]);

        Inertia::share('csrf_token', fn () => $csrf_token);
    }
}
