<?php

use App\Data\PurchaseReceipts\PurchaseReceiptItemData;
use App\Data\PurchaseReceipts\PurchaseReceiptStoreData;
use App\Data\Purchases\PurchaseIndexData;
use App\Data\Purchases\PurchaseItemData;
use App\Data\Purchases\PurchaseListItemData;
use App\Data\Purchases\PurchaseStoreData;
use App\Models\Ingredient;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\User;

it('purchase index data stabil frontend filter payloadot ad', function (): void {
    $data = PurchaseIndexData::from([
        'search' => '  PO-42 ',
        'status' => Purchase::STATUS_DRAFT,
        'supplier_id' => '7',
        'per_page' => '100',
        'sort_field' => 'invalid',
        'sort_direction' => 'sideways',
    ]);

    expect($data->search)->toBe('PO-42')
        ->and($data->supplier_id)->toBe(7)
        ->and($data->per_page)->toBe(50)
        ->and($data->sort_field)->toBe('purchase_date')
        ->and($data->sort_direction)->toBe('desc')
        ->and($data->toFrontendFilters())->toMatchArray([
            'search' => 'PO-42',
            'status' => Purchase::STATUS_DRAFT,
            'supplier_id' => 7,
        ]);
});

it('purchase store data nested item payloadban megorzi a decimal stringeket', function (): void {
    $data = PurchaseStoreData::from([
        'supplier_id' => null,
        'reference_number' => 'PO-DEC',
        'purchase_date' => '2026-05-10',
        'notes' => '',
        'items' => [
            [
                'ingredient_id' => 12,
                'quantity' => '10.125',
                'unit' => 'kg',
                'unit_cost' => '320.1234',
            ],
        ],
    ]);

    expect($data->items[0])->toBeInstanceOf(PurchaseItemData::class)
        ->and($data->toPayload()['items'][0])->toMatchArray([
            'ingredient_id' => 12,
            'quantity' => '10.125',
            'unit_cost' => '320.1234',
        ]);
});

it('purchase list item data frontend kompatibilis payloadot ad', function (): void {
    $supplier = Supplier::factory()->create(['name' => 'Teszt Beszallito']);
    $user = User::factory()->create(['name' => 'Admin User']);
    $purchase = Purchase::query()->create([
        'supplier_id' => $supplier->id,
        'reference_number' => 'PO-LIST',
        'purchase_date' => '2026-05-10',
        'status' => Purchase::STATUS_DRAFT,
        'subtotal' => '1234.50',
        'total' => '1234.50',
        'created_by' => $user->id,
    ])->load('supplier', 'creator');

    $data = PurchaseListItemData::from($purchase)->toArray();

    expect($data)->toMatchArray([
        'id' => $purchase->id,
        'supplier_name' => 'Teszt Beszallito',
        'reference_number' => 'PO-LIST',
        'purchase_date' => '2026-05-10',
        'total' => 1234.5,
        'created_by' => 'Admin User',
    ]);
});

it('purchase receipt store data nested item payloadot keszit bekotes nelkul', function (): void {
    $ingredient = Ingredient::factory()->create();

    $data = PurchaseReceiptStoreData::from([
        'purchase_id' => 42,
        'receipt_number' => 'REC-42',
        'received_date' => '2026-05-10',
        'status' => 'draft',
        'items' => [
            [
                'purchase_item_id' => 11,
                'ingredient_id' => $ingredient->id,
                'ordered_quantity' => '5.000',
                'received_quantity' => '4.500',
                'rejected_quantity' => '0.500',
                'unit' => 'kg',
                'unit_cost' => '250.00',
                'quality_status' => 'accepted',
                'notes' => 'Részleges átvétel',
            ],
        ],
    ]);

    expect($data->items[0])->toBeInstanceOf(PurchaseReceiptItemData::class)
        ->and($data->toPayload()['items'][0])->toMatchArray([
            'purchase_item_id' => 11,
            'received_quantity' => '4.500',
            'unit_cost' => '250.00',
        ]);
});
