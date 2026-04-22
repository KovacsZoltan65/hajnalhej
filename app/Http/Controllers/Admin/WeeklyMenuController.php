<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreWeeklyMenuItemRequest;
use App\Http\Requests\StoreWeeklyMenuRequest;
use App\Http\Requests\UpdateWeeklyMenuItemRequest;
use App\Http\Requests\UpdateWeeklyMenuRequest;
use App\Models\WeeklyMenu;
use App\Models\WeeklyMenuItem;
use App\Services\WeeklyMenuService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use RuntimeException;

class WeeklyMenuController extends Controller
{
    public function __construct(private readonly WeeklyMenuService $service)
    {
    }

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', WeeklyMenu::class);

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:160'],
            'status' => ['nullable', 'in:draft,published,archived'],
            'sort_field' => ['nullable', 'in:week_start,status,title'],
            'sort_direction' => ['nullable', 'in:asc,desc'],
            'per_page' => ['nullable', 'integer', 'min:5', 'max:50'],
        ]);

        $paginator = $this->service
            ->paginateForAdmin($filters)
            ->through(fn (WeeklyMenu $menu): array => [
                'id' => $menu->id,
                'title' => $menu->title,
                'slug' => $menu->slug,
                'week_start' => $menu->week_start?->toDateString(),
                'week_end' => $menu->week_end?->toDateString(),
                'status' => $menu->status,
                'public_note' => $menu->public_note,
                'internal_note' => $menu->internal_note,
                'is_featured' => $menu->is_featured,
                'published_at' => $menu->published_at?->toDateTimeString(),
                'items_count' => $menu->items_count,
                'items' => $menu->items
                    ->map(fn (WeeklyMenuItem $item): array => [
                        'id' => $item->id,
                        'weekly_menu_id' => $item->weekly_menu_id,
                        'product_id' => $item->product_id,
                        'product_name' => $item->product?->name,
                        'category_name' => $item->product?->category?->name,
                        'override_name' => $item->override_name,
                        'override_short_description' => $item->override_short_description,
                        'override_price' => $item->override_price !== null ? (float) $item->override_price : null,
                        'sort_order' => $item->sort_order,
                        'is_active' => $item->is_active,
                        'badge_text' => $item->badge_text,
                        'stock_note' => $item->stock_note,
                    ])
                    ->values()
                    ->all(),
            ]);

        return Inertia::render('Admin/WeeklyMenus/Index', [
            'weeklyMenus' => $paginator,
            'filters' => [
                'search' => (string) ($filters['search'] ?? ''),
                'status' => (string) ($filters['status'] ?? ''),
                'sort_field' => (string) ($filters['sort_field'] ?? 'week_start'),
                'sort_direction' => (string) ($filters['sort_direction'] ?? 'desc'),
                'per_page' => (int) ($filters['per_page'] ?? 10),
            ],
            'statuses' => [
                ['value' => WeeklyMenu::STATUS_DRAFT, 'label' => 'Piszkozat'],
                ['value' => WeeklyMenu::STATUS_PUBLISHED, 'label' => 'Közzétéve'],
                ['value' => WeeklyMenu::STATUS_ARCHIVED, 'label' => 'Archivált'],
            ],
            'products' => $this->service->listSelectableProducts(),
        ]);
    }

    public function store(StoreWeeklyMenuRequest $request): RedirectResponse
    {
        $this->service->create($request->validated());

        return redirect()->route('admin.weekly-menus.index')->with('success', 'Heti menü létrehozva.');
    }

    public function update(UpdateWeeklyMenuRequest $request, WeeklyMenu $weeklyMenu): RedirectResponse
    {
        $this->service->update($weeklyMenu, $request->validated());

        return redirect()->route('admin.weekly-menus.index')->with('success', 'Heti menü frissítve.');
    }

    public function destroy(WeeklyMenu $weeklyMenu): RedirectResponse
    {
        $this->authorize('delete', $weeklyMenu);

        $this->service->delete($weeklyMenu);

        return redirect()->route('admin.weekly-menus.index')->with('success', 'Heti menü törölve.');
    }

    public function publish(WeeklyMenu $weeklyMenu): RedirectResponse
    {
        $this->authorize('update', $weeklyMenu);

        try {
            $this->service->publish($weeklyMenu);
        } catch (RuntimeException $exception) {
            return redirect()->route('admin.weekly-menus.index')->with('error', $exception->getMessage());
        }

        return redirect()->route('admin.weekly-menus.index')->with('success', 'Heti menü publikálva.');
    }

    public function unpublish(WeeklyMenu $weeklyMenu): RedirectResponse
    {
        $this->authorize('update', $weeklyMenu);

        $this->service->unpublish($weeklyMenu);

        return redirect()->route('admin.weekly-menus.index')->with('success', 'Heti menü visszaállítva piszkozat állapotba.');
    }

    public function storeItem(StoreWeeklyMenuItemRequest $request, WeeklyMenu $weeklyMenu): RedirectResponse
    {
        try {
            $this->service->createItem($weeklyMenu, $request->validated());
        } catch (RuntimeException $exception) {
            return redirect()->route('admin.weekly-menus.index')->with('error', $exception->getMessage());
        }

        return redirect()->route('admin.weekly-menus.index')->with('success', 'Heti menü tétel létrehozva.');
    }

    public function updateItem(UpdateWeeklyMenuItemRequest $request, WeeklyMenu $weeklyMenu, WeeklyMenuItem $item): RedirectResponse
    {
        if ($item->weekly_menu_id !== $weeklyMenu->id) {
            abort(404);
        }

        try {
            $this->service->updateItem($weeklyMenu, $item, $request->validated());
        } catch (RuntimeException $exception) {
            return redirect()->route('admin.weekly-menus.index')->with('error', $exception->getMessage());
        }

        return redirect()->route('admin.weekly-menus.index')->with('success', 'Heti menü tétel frissítve.');
    }

    public function destroyItem(WeeklyMenu $weeklyMenu, WeeklyMenuItem $item): RedirectResponse
    {
        $this->authorize('update', $weeklyMenu);

        if ($item->weekly_menu_id !== $weeklyMenu->id) {
            abort(404);
        }

        $this->service->deleteItem($item);

        return redirect()->route('admin.weekly-menus.index')->with('success', 'Heti menü tétel törölve.');
    }
}




