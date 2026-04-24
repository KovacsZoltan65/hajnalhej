<?php

namespace App\Services;

use App\Models\InventoryMovement;
use App\Models\Purchase;
use App\Models\User;
use App\Repositories\InventoryMovementRepository;
use App\Repositories\PurchaseRepository;
use App\Services\Audit\InventoryAuditService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PurchaseService
{
    public function __construct(
        private readonly PurchaseRepository $repository,
        private readonly InventoryMovementRepository $movementRepository,
        private readonly InventoryService $inventoryService,
        private readonly InventoryAuditService $auditService,
    ) {
    }

    /**
     * @param array<string, mixed> $filters
     */
    public function paginateForAdmin(array $filters): LengthAwarePaginator
    {
        return $this->repository->paginateForAdmin($filters);
    }

    public function findWithItems(int $purchaseId): ?Purchase
    {
        return $this->repository->findWithItems($purchaseId);
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function create(array $payload, ?User $actor = null): Purchase
    {
        return DB::transaction(function () use ($payload, $actor): Purchase {
            $items = $this->normalizeItems($payload['items'] ?? []);
            if ($items === []) {
                throw new RuntimeException('Legalább egy beszerzési tétel szükséges.');
            }

            [$subtotal, $total] = $this->totals($items);

            $purchase = $this->repository->create([
                'supplier_id' => $payload['supplier_id'] ?? null,
                'reference_number' => $this->emptyToNull($payload['reference_number'] ?? null),
                'purchase_date' => $payload['purchase_date'] ?? Carbon::today()->toDateString(),
                'status' => Purchase::STATUS_DRAFT,
                'subtotal' => $subtotal,
                'total' => $total,
                'notes' => $this->emptyToNull($payload['notes'] ?? null),
                'created_by' => $actor?->id,
            ]);

            $this->repository->syncItems($purchase, $items);
            $purchase = $this->repository->findWithItems($purchase->id) ?? $purchase;

            $this->auditService->logPurchaseCreated($purchase, $actor);

            return $purchase;
        });
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function update(Purchase $purchase, array $payload, ?User $actor = null): Purchase
    {
        if ($purchase->status !== Purchase::STATUS_DRAFT) {
            throw new RuntimeException('Csak draft beszerzés módosítható.');
        }

        return DB::transaction(function () use ($purchase, $payload, $actor): Purchase {
            $items = $this->normalizeItems($payload['items'] ?? []);
            if ($items === []) {
                throw new RuntimeException('Legalább egy beszerzési tétel szükséges.');
            }

            [$subtotal, $total] = $this->totals($items);

            $updated = $this->repository->update($purchase, [
                'supplier_id' => $payload['supplier_id'] ?? null,
                'reference_number' => $this->emptyToNull($payload['reference_number'] ?? null),
                'purchase_date' => $payload['purchase_date'] ?? $purchase->purchase_date?->toDateString(),
                'subtotal' => $subtotal,
                'total' => $total,
                'notes' => $this->emptyToNull($payload['notes'] ?? null),
            ]);

            $this->repository->syncItems($updated, $items);

            return $this->repository->findWithItems($updated->id) ?? $updated;
        });
    }

    public function post(Purchase $purchase, ?User $actor = null): Purchase
    {
        if ($purchase->status !== Purchase::STATUS_DRAFT) {
            throw new RuntimeException('Csak draft beszerzés könyvelhető.');
        }

        return DB::transaction(function () use ($purchase, $actor): Purchase {
            $purchase->loadMissing('items.ingredient');

            if ($this->movementRepository->existsForReference('purchase', $purchase->id, InventoryMovement::TYPE_PURCHASE_IN)) {
                throw new RuntimeException('A beszerzés már könyvelve lett.');
            }

            foreach ($purchase->items as $item) {
                $this->inventoryService->createMovement([
                    'ingredient_id' => $item->ingredient_id,
                    'movement_type' => InventoryMovement::TYPE_PURCHASE_IN,
                    'direction' => InventoryMovement::DIRECTION_IN,
                    'quantity' => $item->quantity,
                    'unit_cost' => $item->unit_cost,
                    'total_cost' => $item->line_total,
                    'occurred_at' => Carbon::now(),
                    'reference_type' => 'purchase',
                    'reference_id' => $purchase->id,
                    'notes' => 'Bevételezés beszerzésből',
                ], $actor);
            }

            $updated = $this->repository->update($purchase, [
                'status' => Purchase::STATUS_POSTED,
                'posted_at' => Carbon::now(),
            ]);

            $this->auditService->logPurchasePosted($updated, $actor);

            return $this->repository->findWithItems($updated->id) ?? $updated;
        });
    }

    public function cancel(Purchase $purchase, ?User $actor = null): Purchase
    {
        if ($purchase->status !== Purchase::STATUS_DRAFT) {
            throw new RuntimeException('Csak draft beszerzés stornózható.');
        }

        $updated = $this->repository->update($purchase, [
            'status' => Purchase::STATUS_CANCELLED,
        ]);
        $this->auditService->logPurchaseCancelled($updated, $actor);

        return $updated;
    }

    /**
     * @param array<int, array<string, mixed>> $items
     * @return array<int, array<string, mixed>>
     */
    private function normalizeItems(array $items): array
    {
        $normalized = [];

        foreach ($items as $item) {
            $quantity = round(max((float) ($item['quantity'] ?? 0), 0), 3);
            $unitCost = round(max((float) ($item['unit_cost'] ?? 0), 0), 4);
            if ($quantity <= 0) {
                continue;
            }

            $normalized[] = [
                'ingredient_id' => (int) $item['ingredient_id'],
                'quantity' => number_format($quantity, 3, '.', ''),
                'unit' => (string) ($item['unit'] ?? 'db'),
                'unit_cost' => number_format($unitCost, 4, '.', ''),
                'line_total' => number_format($quantity * $unitCost, 2, '.', ''),
            ];
        }

        return $normalized;
    }

    /**
     * @param array<int, array<string, mixed>> $items
     * @return array{0:float,1:float}
     */
    private function totals(array $items): array
    {
        $subtotal = round((float) collect($items)->sum(static fn (array $item): float => (float) $item['line_total']), 2);

        return [$subtotal, $subtotal];
    }

    private function emptyToNull(mixed $value): ?string
    {
        $normalized = trim((string) $value);

        return $normalized === '' ? null : $normalized;
    }
}
