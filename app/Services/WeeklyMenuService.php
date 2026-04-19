<?php

namespace App\Services;

use App\Models\Product;
use App\Models\WeeklyMenu;
use App\Models\WeeklyMenuItem;
use App\Repositories\WeeklyMenuRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class WeeklyMenuService
{
    public function __construct(private readonly WeeklyMenuRepository $repository)
    {
    }

    /**
     * @param array<string, mixed> $filters
     */
    public function paginateForAdmin(array $filters): LengthAwarePaginator
    {
        return $this->repository->paginateForAdmin($filters);
    }

    /**
     * @return Collection<int, array{id:int,name:string,category_name:string|null,price:float}>
     */
    public function listSelectableProducts(): Collection
    {
        return $this->repository->listSelectableProducts();
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function create(array $payload): WeeklyMenu
    {
        $normalized = $this->normalizeMenuPayload($payload);
        $normalized['slug'] = $this->resolveUniqueSlug((string) $normalized['slug']);

        return $this->repository->create($normalized);
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function update(WeeklyMenu $weeklyMenu, array $payload): WeeklyMenu
    {
        $normalized = $this->normalizeMenuPayload($payload, $weeklyMenu);
        $normalized['slug'] = $this->resolveUniqueSlug((string) $normalized['slug'], $weeklyMenu->id);

        return $this->repository->update($weeklyMenu, $normalized);
    }

    public function delete(WeeklyMenu $weeklyMenu): void
    {
        $this->repository->delete($weeklyMenu);
    }

    public function publish(WeeklyMenu $weeklyMenu): WeeklyMenu
    {
        if (! $this->repository->hasAnyItems($weeklyMenu)) {
            throw new RuntimeException('A menu csak akkor publikalhato, ha legalabb egy aktiv tetel tartozik hozza.');
        }

        return DB::transaction(function () use ($weeklyMenu): WeeklyMenu {
            $this->repository->archiveOtherMenusForWeek($weeklyMenu);

            return $this->repository->publish($weeklyMenu, Carbon::now());
        });
    }

    public function unpublish(WeeklyMenu $weeklyMenu): WeeklyMenu
    {
        return $this->repository->unpublish($weeklyMenu);
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function createItem(WeeklyMenu $weeklyMenu, array $payload): WeeklyMenuItem
    {
        $normalized = $this->normalizeItemPayload($payload);

        if ($this->repository->existsMenuProduct($weeklyMenu, (int) $normalized['product_id'])) {
            throw new RuntimeException('A kivalasztott termek mar szerepel ebben a heti menuben.');
        }

        return $this->repository->createItem($weeklyMenu, $normalized);
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function updateItem(WeeklyMenu $weeklyMenu, WeeklyMenuItem $item, array $payload): WeeklyMenuItem
    {
        $normalized = $this->normalizeItemPayload($payload);

        if ($this->repository->existsMenuProduct($weeklyMenu, (int) $normalized['product_id'], $item->id)) {
            throw new RuntimeException('A kivalasztott termek mar szerepel ebben a heti menuben.');
        }

        return $this->repository->updateItem($item, $normalized);
    }

    public function deleteItem(WeeklyMenuItem $item): void
    {
        $this->repository->deleteItem($item);
    }

    /**
     * @return array<string, mixed>
     */
    public function getPublicWeeklyMenuPayload(): array
    {
        $menu = $this->repository->findPublishedForDateOrLatest(Carbon::today());

        if (! $menu) {
            return [
                'menu' => null,
                'fallback_used' => false,
                'groups' => [],
            ];
        }

        $isCurrent = Carbon::today()->betweenIncluded($menu->week_start, $menu->week_end);

        $groups = $menu->items
            ->filter(fn (WeeklyMenuItem $item): bool => $item->is_active && $item->product !== null)
            ->groupBy(fn (WeeklyMenuItem $item): string => (string) ($item->product?->category?->name ?? 'Egyeb'))
            ->map(function (Collection $items, string $groupName): array {
                return [
                    'category_name' => $groupName,
                    'items' => $items
                        ->sortBy(['sort_order', 'id'])
                        ->values()
                        ->map(fn (WeeklyMenuItem $item): array => [
                            'id' => $item->id,
                            'product_id' => $item->product_id,
                            'name' => $item->override_name ?: $item->product?->name,
                            'short_description' => $item->override_short_description ?: $item->product?->short_description,
                            'price' => $item->override_price !== null ? (float) $item->override_price : (float) ($item->product?->price ?? 0),
                            'badge_text' => $item->badge_text,
                            'stock_note' => $item->stock_note,
                        ])
                        ->all(),
                ];
            })
            ->values()
            ->all();

        return [
            'menu' => [
                'id' => $menu->id,
                'title' => $menu->title,
                'week_start' => $menu->week_start?->toDateString(),
                'week_end' => $menu->week_end?->toDateString(),
                'public_note' => $menu->public_note,
                'status' => $menu->status,
            ],
            'fallback_used' => ! $isCurrent,
            'groups' => $groups,
        ];
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    private function normalizeMenuPayload(array $payload, ?WeeklyMenu $weeklyMenu = null): array
    {
        $title = trim((string) ($payload['title'] ?? ''));
        $slugInput = trim((string) ($payload['slug'] ?? ''));

        if ($slugInput === '') {
            $slugInput = Str::slug($title);
        }

        if ($slugInput === '') {
            $slugInput = $weeklyMenu?->slug ?? 'weekly-menu';
        }

        $status = (string) ($payload['status'] ?? WeeklyMenu::STATUS_DRAFT);

        if (! in_array($status, WeeklyMenu::statuses(), true)) {
            $status = WeeklyMenu::STATUS_DRAFT;
        }

        return [
            'title' => $title,
            'slug' => $slugInput,
            'week_start' => (string) $payload['week_start'],
            'week_end' => (string) $payload['week_end'],
            'status' => $status,
            'public_note' => $payload['public_note'] ?? null,
            'internal_note' => $payload['internal_note'] ?? null,
            'is_featured' => (bool) ($payload['is_featured'] ?? false),
            'published_at' => $status === WeeklyMenu::STATUS_PUBLISHED ? ($weeklyMenu?->published_at ?? Carbon::now()) : null,
        ];
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    private function normalizeItemPayload(array $payload): array
    {
        $product = Product::query()->select('id', 'category_id')->findOrFail((int) $payload['product_id']);

        return [
            'product_id' => $product->id,
            'category_id' => $product->category_id,
            'override_name' => $payload['override_name'] ?? null,
            'override_short_description' => $payload['override_short_description'] ?? null,
            'override_price' => $payload['override_price'] !== null ? number_format((float) $payload['override_price'], 2, '.', '') : null,
            'sort_order' => (int) ($payload['sort_order'] ?? 0),
            'is_active' => (bool) ($payload['is_active'] ?? true),
            'badge_text' => $payload['badge_text'] ?? null,
            'stock_note' => $payload['stock_note'] ?? null,
        ];
    }

    private function resolveUniqueSlug(string $baseSlug, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($baseSlug);
        $baseSlug = $baseSlug !== '' ? $baseSlug : 'weekly-menu';

        $slug = $baseSlug;
        $counter = 2;

        while ($this->repository->slugExists($slug, $ignoreId)) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }

        return $slug;
    }
}
