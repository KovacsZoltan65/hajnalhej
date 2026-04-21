<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Ingredient;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductionPlan;
use App\Models\WeeklyMenu;
use App\Policies\CategoryPolicy;
use App\Policies\IngredientPolicy;
use App\Policies\OrderPolicy;
use App\Policies\ProductPolicy;
use App\Policies\ProductionPlanPolicy;
use App\Policies\WeeklyMenuPolicy;
use Illuminate\Auth\Middleware\RedirectIfAuthenticated;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

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

            if ($user?->isAdmin()) {
                return route('admin.dashboard');
            }

            return route('account');
        });

        Gate::policy(Category::class, CategoryPolicy::class);
        Gate::policy(Ingredient::class, IngredientPolicy::class);
        Gate::policy(Order::class, OrderPolicy::class);
        Gate::policy(Product::class, ProductPolicy::class);
        Gate::policy(ProductionPlan::class, ProductionPlanPolicy::class);
        Gate::policy(WeeklyMenu::class, WeeklyMenuPolicy::class);
    }
}
