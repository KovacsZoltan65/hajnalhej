<?php

namespace App\Services;

use App\Data\WeeklyMenu\WeeklyMenuIndexData;
use App\Data\WeeklyMenu\WeeklyMenuStoreData;
use App\Data\WeeklyMenu\WeeklyMenuUpdateData;
use App\Data\WeeklyMenuItem\WeeklyMenuItemStoreData;
use App\Data\WeeklyMenuItem\WeeklyMenuItemUpdateData;
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
    public function __construct(
        private readonly WeeklyMenuRepository $repository,
        private readonly WeeklyMenuItemService $itemService,
    ) {}

    public function paginate(WeeklyMenuIndexData $filters): LengthAwarePaginator
    {
        return $this->repository->paginate($filters);
    }

    public function paginateForAdmin(WeeklyMenuIndexData $filters): LengthAwarePaginator
    {
        return $this->paginate($filters);
    }

    /**
     * @return Collection<int, array{id:int,name:string,category_name:string|null,price:float}>
     */
    public function listSelectableProducts(): Collection
    {
        return $this->repository->listSelectableProducts();
    }

    public function store(WeeklyMenuStoreData $data): WeeklyMenu
    {
        $normalized = $this->normalizeMenuPayload($data->toPayload());
        $normalized['slug'] = $this->resolveUniqueSlug((string) $normalized['slug']);

        return DB::transaction(function () use ($normalized): WeeklyMenu {
            if ($normalized['status'] === WeeklyMenu::STATUS_PUBLISHED) {
                $this->repository->archiveOtherActiveMenus();
            }

            return $this->repository->create($normalized);
        });
    }

    public function update(WeeklyMenu $weeklyMenu, WeeklyMenuUpdateData $data): WeeklyMenu
    {
        $normalized = $this->normalizeMenuPayload($data->toPayload(), $weeklyMenu);
        $normalized['slug'] = $this->resolveUniqueSlug((string) $normalized['slug'], $weeklyMenu->id);

        return DB::transaction(function () use ($weeklyMenu, $normalized): WeeklyMenu {
            if ($normalized['status'] === WeeklyMenu::STATUS_PUBLISHED) {
                $this->repository->archiveOtherActiveMenus($weeklyMenu->id);
            }

            return $this->repository->update($weeklyMenu, $normalized);
        });
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
            $this->repository->archiveOtherActiveMenus($weeklyMenu->id);

            return $this->repository->publish($weeklyMenu, Carbon::now());
        });
    }

    public function unpublish(WeeklyMenu $weeklyMenu): WeeklyMenu
    {
        return $this->repository->unpublish($weeklyMenu);
    }

    public function createItem(WeeklyMenu $weeklyMenu, WeeklyMenuItemStoreData $data): WeeklyMenuItem
    {
        return $this->itemService->addItem($weeklyMenu, $data);
    }

    public function updateItem(WeeklyMenu $weeklyMenu, WeeklyMenuItem $item, WeeklyMenuItemUpdateData $data): WeeklyMenuItem
    {
        return $this->itemService->updateItem($weeklyMenu, $item, $data);
    }

    public function deleteItem(WeeklyMenuItem $item): void
    {
        $this->itemService->removeItem($item);
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
     * @param  array<string, mixed>  $payload
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

        if (! \in_array($status, WeeklyMenu::statuses(), true)) {
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
