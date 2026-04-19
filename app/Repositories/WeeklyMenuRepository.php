<?php

namespace App\Repositories;

use App\Models\Product;
use App\Models\WeeklyMenu;
use App\Models\WeeklyMenuItem;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class WeeklyMenuRepository
{
    /**
     * @param array<string, mixed> $filters
     */
    public function paginateForAdmin(array $filters): LengthAwarePaginator
    {
        $perPage = (int) ($filters['per_page'] ?? 10);

        return $this->adminQuery($filters)
            ->with(['items.product.category'])
            ->withCount('items')
            ->paginate($perPage)
            ->withQueryString();
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
     * @param array<string, mixed> $data
     */
    public function create(array $data): WeeklyMenu
    {
        return WeeklyMenu::query()->create($data);
    }

    /**
     * @param array<string, mixed> $data
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
     * @param array<string, mixed> $data
     */
    public function createItem(WeeklyMenu $weeklyMenu, array $data): WeeklyMenuItem
    {
        return $weeklyMenu->items()->create($data);
    }

    /**
     * @param array<string, mixed> $data
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

    /**
     * @param array<string, mixed> $filters
     */
    private function adminQuery(array $filters): Builder
    {
        $search = trim((string) ($filters['search'] ?? ''));
        $status = (string) ($filters['status'] ?? '');
        $sortField = (string) ($filters['sort_field'] ?? 'week_start');
        $sortDirection = (string) ($filters['sort_direction'] ?? 'desc');

        $sortableFields = ['week_start', 'status', 'title'];

        if (! in_array($sortField, $sortableFields, true)) {
            $sortField = 'week_start';
        }

        if (! in_array($sortDirection, ['asc', 'desc'], true)) {
            $sortDirection = 'desc';
        }

        return WeeklyMenu::query()
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $innerQuery) use ($search): void {
                    $innerQuery
                        ->where('title', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%");
                });
            })
            ->when($status !== '', fn (Builder $query): Builder => $query->where('status', $status))
            ->orderBy($sortField, $sortDirection)
            ->orderBy('id');
    }
}
