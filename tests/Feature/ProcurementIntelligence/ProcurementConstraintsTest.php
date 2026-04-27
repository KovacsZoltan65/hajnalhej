<?php

use App\Models\Branch;
use App\Models\BranchInventory;
use App\Models\DailyBriefing;
use App\Models\Ingredient;
use App\Models\IngredientSupplierTerm;
use App\Models\Supplier;
use Illuminate\Database\QueryException;

it('enforces unique ingredient supplier terms per ingredient and supplier', function (): void {
    $supplier = Supplier::factory()->create();
    $ingredient = Ingredient::factory()->create();

    IngredientSupplierTerm::query()->create([
        'ingredient_id' => $ingredient->id,
        'supplier_id' => $supplier->id,
        'preferred' => true,
    ]);

    expect(fn () => IngredientSupplierTerm::query()->create([
        'ingredient_id' => $ingredient->id,
        'supplier_id' => $supplier->id,
        'preferred' => false,
    ]))->toThrow(QueryException::class);
});

it('enforces unique branch inventory per branch and ingredient', function (): void {
    $branch = Branch::query()->create([
        'name' => 'Belvarosi Uzlet',
        'code' => 'HH-BEL',
        'type' => 'store',
        'active' => true,
    ]);
    $ingredient = Ingredient::factory()->create();

    BranchInventory::query()->create([
        'branch_id' => $branch->id,
        'ingredient_id' => $ingredient->id,
        'current_stock' => 5,
    ]);

    expect(fn () => BranchInventory::query()->create([
        'branch_id' => $branch->id,
        'ingredient_id' => $ingredient->id,
        'current_stock' => 8,
    ]))->toThrow(QueryException::class);
});

it('enforces unique daily briefing date', function (): void {
    $date = now()->toDateString();

    DailyBriefing::query()->create([
        'briefing_date' => $date,
        'status' => 'generated',
    ]);

    expect(fn () => DailyBriefing::query()->create([
        'briefing_date' => $date,
        'status' => 'draft',
    ]))->toThrow(QueryException::class);
});
