<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\IngredientController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ProductIngredientController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\WeeklyMenuController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\PublicPageController;
use Illuminate\Support\Facades\Route;

Route::controller(PublicPageController::class)->group(function (): void {
    Route::get('/', 'home')->name('home');
    Route::get('/weekly-menu', 'weeklyMenu')->name('weekly-menu');
    Route::get('/about', 'about')->name('about');
});

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
});

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::prefix('admin')->name('admin.')->group(function (): void {
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

        Route::get('/ingredients', [IngredientController::class, 'index'])->name('ingredients.index');
        Route::post('/ingredients', [IngredientController::class, 'store'])->name('ingredients.store');
        Route::put('/ingredients/{ingredient}', [IngredientController::class, 'update'])->name('ingredients.update');
        Route::delete('/ingredients/{ingredient}', [IngredientController::class, 'destroy'])->name('ingredients.destroy');

        Route::get('/weekly-menus', [WeeklyMenuController::class, 'index'])->name('weekly-menus.index');
        Route::post('/weekly-menus', [WeeklyMenuController::class, 'store'])->name('weekly-menus.store');
        Route::put('/weekly-menus/{weeklyMenu}', [WeeklyMenuController::class, 'update'])->name('weekly-menus.update');
        Route::delete('/weekly-menus/{weeklyMenu}', [WeeklyMenuController::class, 'destroy'])->name('weekly-menus.destroy');
        Route::post('/weekly-menus/{weeklyMenu}/publish', [WeeklyMenuController::class, 'publish'])->name('weekly-menus.publish');
        Route::post('/weekly-menus/{weeklyMenu}/unpublish', [WeeklyMenuController::class, 'unpublish'])->name('weekly-menus.unpublish');

        Route::post('/weekly-menus/{weeklyMenu}/items', [WeeklyMenuController::class, 'storeItem'])->name('weekly-menus.items.store');
        Route::put('/weekly-menus/{weeklyMenu}/items/{item}', [WeeklyMenuController::class, 'updateItem'])->name('weekly-menus.items.update');
        Route::delete('/weekly-menus/{weeklyMenu}/items/{item}', [WeeklyMenuController::class, 'destroyItem'])->name('weekly-menus.items.destroy');
    });
});
