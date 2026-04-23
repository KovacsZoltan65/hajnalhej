<?php

namespace App\Services\Audit;

use App\Models\InventoryMovement;
use App\Models\Purchase;
use App\Models\StockCount;
use App\Models\Supplier;
use App\Models\User;

class InventoryAuditService extends BaseAuditService
{
    public const LOG_NAME = 'inventory';

    public const SUPPLIER_CREATED = 'supplier.created';
    public const SUPPLIER_UPDATED = 'supplier.updated';
    public const SUPPLIER_DELETED = 'supplier.deleted';
    public const PURCHASE_CREATED = 'purchase.created';
    public const PURCHASE_POSTED = 'purchase.posted';
    public const PURCHASE_CANCELLED = 'purchase.cancelled';
    public const INVENTORY_ADJUSTED = 'inventory.adjusted';
    public const WASTE_RECORDED = 'waste.recorded';
    public const STOCK_COUNT_CLOSED = 'stock_count.closed';
    public const INVENTORY_SHORTAGE_DETECTED = 'inventory.shortage_detected';

    /**
     * @return array<int, string>
     */
    public static function eventKeys(): array
    {
        return [
            self::SUPPLIER_CREATED,
            self::SUPPLIER_UPDATED,
            self::SUPPLIER_DELETED,
            self::PURCHASE_CREATED,
            self::PURCHASE_POSTED,
            self::PURCHASE_CANCELLED,
            self::INVENTORY_ADJUSTED,
            self::WASTE_RECORDED,
            self::STOCK_COUNT_CLOSED,
            self::INVENTORY_SHORTAGE_DETECTED,
        ];
    }

    public function logSupplierCreated(Supplier $supplier, ?User $actor): void
    {
        $this->log(self::LOG_NAME, self::SUPPLIER_CREATED, 'Supplier created', $actor, $supplier, ['supplier' => null], ['supplier' => $supplier->toArray()]);
    }

    public function logSupplierUpdated(Supplier $supplier, ?User $actor, array $before, array $after): void
    {
        $this->log(self::LOG_NAME, self::SUPPLIER_UPDATED, 'Supplier updated', $actor, $supplier, $before, $after);
    }

    public function logSupplierDeleted(Supplier $supplier, ?User $actor): void
    {
        $this->log(self::LOG_NAME, self::SUPPLIER_DELETED, 'Supplier deleted', $actor, $supplier, ['supplier' => $supplier->toArray()], ['supplier' => null]);
    }

    public function logPurchaseCreated(Purchase $purchase, ?User $actor): void
    {
        $this->log(self::LOG_NAME, self::PURCHASE_CREATED, 'Purchase created', $actor, $purchase, ['purchase' => null], ['purchase' => $this->purchaseSnapshot($purchase)]);
    }

    public function logPurchasePosted(Purchase $purchase, ?User $actor): void
    {
        $this->log(self::LOG_NAME, self::PURCHASE_POSTED, 'Purchase posted', $actor, $purchase, ['status' => Purchase::STATUS_DRAFT], ['status' => Purchase::STATUS_POSTED], [], ['purchase' => $this->purchaseSnapshot($purchase)]);
    }

    public function logPurchaseCancelled(Purchase $purchase, ?User $actor): void
    {
        $this->log(self::LOG_NAME, self::PURCHASE_CANCELLED, 'Purchase cancelled', $actor, $purchase, ['status' => Purchase::STATUS_DRAFT], ['status' => Purchase::STATUS_CANCELLED], [], ['purchase' => $this->purchaseSnapshot($purchase)]);
    }

    public function logInventoryAdjusted(InventoryMovement $movement, ?User $actor): void
    {
        $this->log(self::LOG_NAME, self::INVENTORY_ADJUSTED, 'Inventory adjusted', $actor, $movement, ['movement' => null], ['movement' => $this->movementSnapshot($movement)]);
    }

    public function logWasteRecorded(InventoryMovement $movement, ?User $actor): void
    {
        $this->log(self::LOG_NAME, self::WASTE_RECORDED, 'Waste recorded', $actor, $movement, ['movement' => null], ['movement' => $this->movementSnapshot($movement)]);
    }

    public function logStockCountClosed(StockCount $stockCount, ?User $actor): void
    {
        $this->log(self::LOG_NAME, self::STOCK_COUNT_CLOSED, 'Stock count closed', $actor, $stockCount, ['status' => StockCount::STATUS_DRAFT], ['status' => StockCount::STATUS_CLOSED]);
    }

    public function logInventoryShortageDetected(InventoryMovement $movement, ?User $actor, float $available): void
    {
        $this->log(
            self::LOG_NAME,
            self::INVENTORY_SHORTAGE_DETECTED,
            'Inventory shortage detected',
            $actor,
            $movement,
            ['available_quantity' => $available],
            ['required_quantity' => (float) $movement->quantity],
            [],
            ['movement' => $this->movementSnapshot($movement)],
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function purchaseSnapshot(Purchase $purchase): array
    {
        return [
            'id' => $purchase->id,
            'status' => $purchase->status,
            'purchase_date' => $purchase->purchase_date?->toDateString(),
            'total' => (float) $purchase->total,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function movementSnapshot(InventoryMovement $movement): array
    {
        return [
            'id' => $movement->id,
            'ingredient_id' => $movement->ingredient_id,
            'movement_type' => $movement->movement_type,
            'direction' => $movement->direction,
            'quantity' => (float) $movement->quantity,
            'unit_cost' => $movement->unit_cost !== null ? (float) $movement->unit_cost : null,
            'total_cost' => $movement->total_cost !== null ? (float) $movement->total_cost : null,
            'reference_type' => $movement->reference_type,
            'reference_id' => $movement->reference_id,
        ];
    }
}

