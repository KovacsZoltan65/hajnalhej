<?php

use App\Models\Branch;
use App\Models\BranchInventory;
use App\Models\Ingredient;
use App\Models\IngredientSupplierTerm;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\PurchaseReceipt;
use App\Models\PurchaseRecommendation;
use App\Models\Supplier;

it('loads supplier contacts relationship', function (): void {
    $supplier = Supplier::factory()->create();

    $contact = $supplier->contacts()->create([
        'name' => 'Procurement Lead',
        'email' => 'lead@example.test',
        'is_primary' => true,
        'active' => true,
    ]);

    expect($supplier->fresh()->contacts)->toHaveCount(1)
        ->and($supplier->fresh()->contacts->first()->is($contact))->toBeTrue();
});

it('loads ingredient supplier terms relationship', function (): void {
    $supplier = Supplier::factory()->create();
    $ingredient = Ingredient::factory()->create();

    $term = IngredientSupplierTerm::query()->create([
        'ingredient_id' => $ingredient->id,
        'supplier_id' => $supplier->id,
        'preferred' => true,
        'minimum_order_quantity' => 50,
        'pack_size' => 25,
    ]);

    expect($ingredient->fresh()->supplierTerms)->toHaveCount(1)
        ->and($ingredient->fresh()->supplierTerms->first()->is($term))->toBeTrue()
        ->and($supplier->fresh()->ingredientTerms)->toHaveCount(1);
});

it('loads purchase receipt items relationship', function (): void {
    $supplier = Supplier::factory()->create();
    $ingredient = Ingredient::factory()->create(['unit' => 'kg']);
    $purchase = Purchase::query()->create([
        'supplier_id' => $supplier->id,
        'purchase_date' => now()->toDateString(),
        'status' => 'ordered',
        'subtotal' => 1200,
        'total' => 1200,
    ]);
    $purchaseItem = PurchaseItem::query()->create([
        'purchase_id' => $purchase->id,
        'ingredient_id' => $ingredient->id,
        'quantity' => 3,
        'unit' => 'kg',
        'unit_cost' => 400,
        'line_total' => 1200,
    ]);
    $receipt = PurchaseReceipt::query()->create([
        'purchase_id' => $purchase->id,
        'receipt_number' => 'REC-TEST-001',
        'received_date' => now()->toDateString(),
        'status' => 'posted',
        'total_received_value' => 800,
    ]);

    $receiptItem = $receipt->items()->create([
        'purchase_item_id' => $purchaseItem->id,
        'ingredient_id' => $ingredient->id,
        'ordered_quantity' => 3,
        'received_quantity' => 2,
        'unit' => 'kg',
        'unit_cost' => 400,
        'line_total' => 800,
    ]);

    expect($purchase->fresh()->receipts)->toHaveCount(1)
        ->and($receipt->fresh()->items)->toHaveCount(1)
        ->and($receiptItem->fresh()->purchaseItem->is($purchaseItem))->toBeTrue();
});

it('loads purchase recommendation items relationship', function (): void {
    $supplier = Supplier::factory()->create();
    $ingredient = Ingredient::factory()->create(['unit' => 'kg']);
    $recommendation = PurchaseRecommendation::query()->create([
        'supplier_id' => $supplier->id,
        'recommendation_number' => 'REC-TEST-REL-001',
        'status' => 'draft',
        'recommendation_date' => now()->toDateString(),
        'estimated_total' => 5000,
    ]);

    $item = $recommendation->items()->create([
        'ingredient_id' => $ingredient->id,
        'supplier_id' => $supplier->id,
        'recommended_quantity' => 10,
        'unit' => 'kg',
        'estimated_unit_cost' => 500,
        'estimated_line_total' => 5000,
    ]);

    expect($recommendation->fresh()->items)->toHaveCount(1)
        ->and($item->fresh()->recommendation->is($recommendation))->toBeTrue()
        ->and($ingredient->fresh()->purchaseRecommendationItems)->toHaveCount(1);
});

it('loads branch inventory relationship', function (): void {
    $branch = Branch::query()->create([
        'name' => 'Hajnalhej Muhely',
        'code' => 'HH-MUHELY',
        'type' => 'bakery',
        'active' => true,
    ]);
    $ingredient = Ingredient::factory()->create();

    $inventory = BranchInventory::query()->create([
        'branch_id' => $branch->id,
        'ingredient_id' => $ingredient->id,
        'current_stock' => 12,
        'minimum_stock' => 3,
        'reorder_point' => 5,
    ]);

    expect($branch->fresh()->inventoryItems)->toHaveCount(1)
        ->and($ingredient->fresh()->branchInventoryItems)->toHaveCount(1)
        ->and($inventory->fresh()->branch->is($branch))->toBeTrue();
});
