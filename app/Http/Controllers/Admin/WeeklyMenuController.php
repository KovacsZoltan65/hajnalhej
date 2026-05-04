<?php

namespace App\Http\Controllers\Admin;

use App\Data\WeeklyMenu\WeeklyMenuIndexData;
use App\Data\WeeklyMenu\WeeklyMenuStoreData;
use App\Data\WeeklyMenu\WeeklyMenuUpdateData;
use App\Data\WeeklyMenuItem\WeeklyMenuItemStoreData;
use App\Data\WeeklyMenuItem\WeeklyMenuItemUpdateData;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreWeeklyMenuItemRequest;
use App\Http\Requests\StoreWeeklyMenuRequest;
use App\Http\Requests\UpdateWeeklyMenuItemRequest;
use App\Http\Requests\UpdateWeeklyMenuRequest;
use App\Models\WeeklyMenu;
use App\Models\WeeklyMenuItem;
use App\Services\WeeklyMenuItemService;
use App\Services\WeeklyMenuService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use RuntimeException;

class WeeklyMenuController extends Controller
{
    public function __construct(
        private readonly WeeklyMenuService $service,
        private readonly WeeklyMenuItemService $itemService,
    ) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', WeeklyMenu::class);

        $request->validate([
            'search' => ['nullable', 'string', 'max:160'],
            'status' => ['nullable', 'in:draft,published,archived'],
            'active' => ['nullable', 'boolean'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'sort_field' => ['nullable', 'in:week_start,week_end,status,title'],
            'sort_direction' => ['nullable', 'in:asc,desc'],
            'per_page' => ['nullable', 'integer', 'min:5', 'max:50'],
        ]);

        $filters = WeeklyMenuIndexData::from($request->all());
        $paginator = $this->service->paginate($filters);

        return Inertia::render('Admin/WeeklyMenus/Index', [
            'weeklyMenus' => $paginator,
            'filters' => $filters->toFrontendFilters(),
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
        $this->service->store(WeeklyMenuStoreData::from($request));

        return redirect()->route('admin.weekly-menus.index')
            ->with('success', __('weekly_menu.created') . '.');
    }

    public function update(UpdateWeeklyMenuRequest $request, WeeklyMenu $weeklyMenu): RedirectResponse
    {
        $this->service->update($weeklyMenu, WeeklyMenuUpdateData::from($request));

        return redirect()->route('admin.weekly-menus.index')
            ->with('success', __('weekly_menu.updated') . '.');
    }

    public function destroy(WeeklyMenu $weeklyMenu): RedirectResponse
    {
        $this->authorize('delete', $weeklyMenu);

        $this->service->delete($weeklyMenu);

        return redirect()->route('admin.weekly-menus.index')
            ->with('success', __('weekly_menu.deleted') . '.');
    }

    public function publish(WeeklyMenu $weeklyMenu): RedirectResponse
    {
        $this->authorize('update', $weeklyMenu);

        try {
            $this->service->publish($weeklyMenu);
        } catch (RuntimeException $exception) {
            return redirect()->route('admin.weekly-menus.index')
                ->with('error', $exception->getMessage());
        }

        return redirect()->route('admin.weekly-menus.index')
            ->with('success', __('weekly_menu.publicated') . '.');
    }

    public function unpublish(WeeklyMenu $weeklyMenu): RedirectResponse
    {
        $this->authorize('update', $weeklyMenu);

        $this->service->unpublish($weeklyMenu);

        return redirect()->route('admin.weekly-menus.index')
            ->with('success', __('weekly_menu.restored_draft_mode') . '.');
    }

    public function storeItem(StoreWeeklyMenuItemRequest $request, WeeklyMenu $weeklyMenu): RedirectResponse
    {
        try {
            $this->itemService->addItem($weeklyMenu, WeeklyMenuItemStoreData::from($request));
        } catch (RuntimeException $exception) {
            return redirect()->route('admin.weekly-menus.index')
                ->with('error', $exception->getMessage());
        }

        return redirect()->route('admin.weekly-menus.index')
            ->with('success', __('weekly_menu.item_created') . '.');
    }

    public function updateItem(UpdateWeeklyMenuItemRequest $request, WeeklyMenu $weeklyMenu, WeeklyMenuItem $item): RedirectResponse
    {
        if ($item->weekly_menu_id !== $weeklyMenu->id) {
            abort(404);
        }

        try {
            $this->itemService->updateItem($weeklyMenu, $item, WeeklyMenuItemUpdateData::from($request));
        } catch (RuntimeException $exception) {
            return redirect()->route('admin.weekly-menus.index')->with('error', $exception->getMessage());
        }

        return redirect()->route('admin.weekly-menus.index')
            ->with('success', __('weekly_menu.item_updated') . '.');
    }

    public function destroyItem(WeeklyMenu $weeklyMenu, WeeklyMenuItem $item): RedirectResponse
    {
        $this->authorize('update', $weeklyMenu);

        if ($item->weekly_menu_id !== $weeklyMenu->id) {
            abort(404);
        }

        $this->itemService->removeItem($item);

        return redirect()->route('admin.weekly-menus.index')
            ->with('success', __('weekly_menu.item_deleted') . '.');
    }
}
