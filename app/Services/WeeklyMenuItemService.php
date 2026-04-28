<?php

namespace App\Services;

use App\Data\WeeklyMenuItem\WeeklyMenuItemStoreData;
use App\Data\WeeklyMenuItem\WeeklyMenuItemUpdateData;
use App\Models\Product;
use App\Models\WeeklyMenu;
use App\Models\WeeklyMenuItem;
use App\Repositories\WeeklyMenuRepository;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class WeeklyMenuItemService
{
    public function __construct(private readonly WeeklyMenuRepository $repository) {}

    public function addItem(WeeklyMenu $weeklyMenu, WeeklyMenuItemStoreData $data): WeeklyMenuItem
    {
        $normalized = $this->normalizeItemPayload($data->toPayload());

        if ($this->repository->existsMenuProduct($weeklyMenu, (int) $normalized['product_id'])) {
            throw new RuntimeException('A kivalasztott termek mar szerepel ebben a heti menuben.');
        }

        return $this->repository->createItem($weeklyMenu, $normalized);
    }

    public function updateItem(WeeklyMenu $weeklyMenu, WeeklyMenuItem $item, WeeklyMenuItemUpdateData $data): WeeklyMenuItem
    {
        $normalized = $this->normalizeItemPayload($data->toPayload());

        if ($this->repository->existsMenuProduct($weeklyMenu, (int) $normalized['product_id'], $item->id)) {
            throw new RuntimeException('A kivalasztott termek mar szerepel ebben a heti menuben.');
        }

        return $this->repository->updateItem($item, $normalized);
    }

    public function removeItem(WeeklyMenuItem $item): void
    {
        $this->repository->deleteItem($item);
    }

    /**
     * @param  array<int, array{id:int,sort_order:int}>  $items
     */
    public function reorderItems(WeeklyMenu $weeklyMenu, array $items): void
    {
        DB::transaction(function () use ($weeklyMenu, $items): void {
            foreach ($items as $item) {
                WeeklyMenuItem::query()
                    ->where('weekly_menu_id', $weeklyMenu->id)
                    ->whereKey((int) $item['id'])
                    ->update(['sort_order' => (int) $item['sort_order']]);
            }
        });
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function normalizeItemPayload(array $payload): array
    {
        $product = Product::query()->select('id', 'category_id', 'price')->findOrFail((int) $payload['product_id']);

        return [
            'product_id' => $product->id,
            'category_id' => $product->category_id,
            'override_name' => $this->normalizeNullableString($payload['override_name'] ?? null),
            'override_short_description' => $this->normalizeNullableString($payload['override_short_description'] ?? null),
            'override_price' => $payload['override_price'] !== null ? number_format((float) $payload['override_price'], 2, '.', '') : null,
            'sort_order' => (int) ($payload['sort_order'] ?? 0),
            'is_active' => (bool) ($payload['is_active'] ?? true),
            'badge_text' => $this->normalizeNullableString($payload['badge_text'] ?? null),
            'stock_note' => $this->normalizeNullableString($payload['stock_note'] ?? null),
        ];
    }

    private function normalizeNullableString(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }
}
