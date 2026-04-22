<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Ingredient;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductionPlan;
use App\Models\User;
use App\Models\WeeklyMenu;
use App\Policies\CategoryPolicy;
use App\Policies\IngredientPolicy;
use App\Policies\OrderPolicy;
use App\Policies\PermissionPolicy;
use App\Policies\ProductPolicy;
use App\Policies\ProductionPlanPolicy;
use App\Policies\AuthorizationAuditPolicy;
use App\Policies\RolePolicy;
use App\Policies\SecurityDashboardPolicy;
use App\Policies\UserPolicy;
use App\Policies\WeeklyMenuPolicy;
use App\Support\PermissionRegistry;
use Illuminate\Auth\Middleware\RedirectIfAuthenticated;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

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

        Gate::policy(Category::class, CategoryPolicy::class);
        Gate::policy(Ingredient::class, IngredientPolicy::class);
        Gate::policy(Order::class, OrderPolicy::class);
        Gate::policy(Permission::class, PermissionPolicy::class);
        Gate::policy(Product::class, ProductPolicy::class);
        Gate::policy(ProductionPlan::class, ProductionPlanPolicy::class);
        Gate::policy(Activity::class, AuthorizationAuditPolicy::class);
        Gate::policy(Role::class, RolePolicy::class);
        Gate::policy(Activity::class, SecurityDashboardPolicy::class);
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(WeeklyMenu::class, WeeklyMenuPolicy::class);
    }
}
