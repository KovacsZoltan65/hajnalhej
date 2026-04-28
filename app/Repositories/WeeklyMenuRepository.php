<?php

namespace App\Repositories;

use App\Data\WeeklyMenu\WeeklyMenuIndexData;
use App\Data\WeeklyMenu\WeeklyMenuListItemData;
use App\Models\Product;
use App\Models\WeeklyMenu;
use App\Models\WeeklyMenuItem;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class WeeklyMenuRepository
{
    public function paginate(WeeklyMenuIndexData $filters): LengthAwarePaginator
    {
        return $this->adminQuery($filters)
            ->with(['items.product.category'])
            ->withCount('items')
            ->paginate($filters->per_page)
            ->withQueryString()
            ->through(fn (WeeklyMenu $menu): array => WeeklyMenuListItemData::from($menu)->toArray());
    }

    /**
     * @return Collection<int, array{id:int,name:string,category_name:string|null,price:float}>
     */
    public function listSelectableProducts(): Collection
    {
        return Product::query()
            ->with('category:id,name')
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'category_id', 'name', 'price'])
            ->map(fn (Product $product): array => [
                'id' => $product->id,
                'name' => $product->name,
                'category_name' => $product->category?->name,
                'price' => (float) $product->price,
            ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): WeeklyMenu
    {
        return WeeklyMenu::query()->create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(WeeklyMenu $weeklyMenu, array $data): WeeklyMenu
    {
        $weeklyMenu->update($data);

        return $weeklyMenu->refresh()->load(['items.product.category']);
    }

    public function delete(WeeklyMenu $weeklyMenu): void
    {
        $weeklyMenu->delete();
    }

    public function slugExists(string $slug, ?int $ignoreId = null): bool
    {
        return WeeklyMenu::query()
            ->where('slug', $slug)
            ->when($ignoreId !== null, fn (Builder $query): Builder => $query->whereKeyNot($ignoreId))
            ->exists();
    }

    public function hasAnyItems(WeeklyMenu $weeklyMenu): bool
    {
        return $weeklyMenu->items()->exists();
    }

    public function archiveOtherMenusForWeek(WeeklyMenu $weeklyMenu): void
    {
        WeeklyMenu::query()
            ->whereKeyNot($weeklyMenu->id)
            ->where('week_start', $weeklyMenu->week_start)
            ->where('week_end', $weeklyMenu->week_end)
            ->where('status', WeeklyMenu::STATUS_PUBLISHED)
            ->update([
                'status' => WeeklyMenu::STATUS_ARCHIVED,
            ]);
    }

    public function archiveOtherActiveMenus(?int $ignoreId = null): void
    {
        WeeklyMenu::query()
            ->when($ignoreId !== null, fn (Builder $query): Builder => $query->whereKeyNot($ignoreId))
            ->where('status', WeeklyMenu::STATUS_PUBLISHED)
            ->update([
                'status' => WeeklyMenu::STATUS_ARCHIVED,
                'published_at' => null,
            ]);
    }

    public function publish(WeeklyMenu $weeklyMenu, Carbon $publishedAt): WeeklyMenu
    {
        $weeklyMenu->update([
            'status' => WeeklyMenu::STATUS_PUBLISHED,
            'published_at' => $publishedAt,
        ]);

        return $weeklyMenu->refresh();
    }

    public function unpublish(WeeklyMenu $weeklyMenu): WeeklyMenu
    {
        $weeklyMenu->update([
            'status' => WeeklyMenu::STATUS_DRAFT,
            'published_at' => null,
        ]);

        return $weeklyMenu->refresh();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createItem(WeeklyMenu $weeklyMenu, array $data): WeeklyMenuItem
    {
        return $weeklyMenu->items()->create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateItem(WeeklyMenuItem $item, array $data): WeeklyMenuItem
    {
        $item->update($data);

        return $item->refresh()->load('product.category');
    }

    public function deleteItem(WeeklyMenuItem $item): void
    {
        $item->delete();
    }

    public function existsMenuProduct(WeeklyMenu $weeklyMenu, int $productId, ?int $ignoreItemId = null): bool
    {
        return WeeklyMenuItem::query()
            ->where('weekly_menu_id', $weeklyMenu->id)
            ->where('product_id', $productId)
            ->when($ignoreItemId !== null, fn (Builder $query): Builder => $query->whereKeyNot($ignoreItemId))
            ->exists();
    }

    public function findPublishedForDateOrLatest(Carbon $date): ?WeeklyMenu
    {
        $current = WeeklyMenu::query()
            ->where('status', WeeklyMenu::STATUS_PUBLISHED)
            ->whereDate('week_start', '<=', $date->toDateString())
            ->whereDate('week_end', '>=', $date->toDateString())
            ->orderByDesc('is_featured')
            ->orderByDesc('published_at')
            ->with([
                'items' => fn ($query) => $query
                    ->where('is_active', true)
                    ->orderBy('sort_order')
                    ->orderBy('id')
                    ->with(['product.category']),
            ])
            ->first();

        if ($current) {
            return $current;
        }

        return WeeklyMenu::query()
            ->where('status', WeeklyMenu::STATUS_PUBLISHED)
            ->orderByDesc('published_at')
            ->orderByDesc('week_start')
            ->with([
                'items' => fn ($query) => $query
                    ->where('is_active', true)
                    ->orderBy('sort_order')
                    ->orderBy('id')
                    ->with(['product.category']),
            ])
            ->first();
    }

    private function adminQuery(WeeklyMenuIndexData $filters): Builder
    {
        $query = WeeklyMenu::query()
            ->when($filters->search !== null, function (Builder $query) use ($filters): void {
                $search = (string) $filters->search;

                $query->where(function (Builder $innerQuery) use ($search): void {
                    $innerQuery
                        ->where('title', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%");
                });
            })
            ->when($filters->status !== null, function (Builder $query) use ($filters): void {
                $query->where('status', $filters->status);
            })
            ->when($filters->date_from !== null, function (Builder $query) use ($filters): void {
                $query->whereDate('week_end', '>=', $filters->date_from);
            })
            ->when($filters->date_to !== null, function (Builder $query) use ($filters): void {
                $query->whereDate('week_start', '<=', $filters->date_to);
            });

        $query
            ->orderBy($filters->sort_field, $filters->sort_direction)
            ->orderBy('id');

        return $query;
    }
}
