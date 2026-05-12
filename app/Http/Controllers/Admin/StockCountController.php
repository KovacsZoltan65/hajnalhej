<?php

namespace App\Http\Controllers\Admin;

use App\Data\StockCounts\StockCountDetailData;
use App\Data\StockCounts\StockCountIndexData;
use App\Data\StockCounts\StockCountListItemData;
use App\Data\StockCounts\StockCountStoreData;
use App\Data\StockCounts\StockCountUpdateData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StockCountIndexRequest;
use App\Http\Requests\StoreStockCountRequest;
use App\Http\Requests\UpdateStockCountRequest;
use App\Models\StockCount;
use App\Services\IngredientService;
use App\Services\StockCountService;
use App\Support\InertiaPage;
use Illuminate\Http\RedirectResponse;
use Inertia\Response;
use RuntimeException;

class StockCountController extends Controller
{
    public function __construct(
        private readonly StockCountService $service,
        private readonly IngredientService $ingredientService,
    ) {}

    public function index(StockCountIndexRequest $request): Response
    {
        $this->authorize('viewAny', StockCount::class);

        $filters = StockCountIndexData::from($request->validated());
        $stockCounts = $this->service
            ->paginateForAdmin($filters)
            ->through(static fn (StockCount $count): array => StockCountListItemData::fromModel($count)->toArray());

        return InertiaPage::ADMIN_STOCK_COUNTS_INDEX->render([
            'stock_counts' => $stockCounts,
            'statuses' => StockCount::statuses(),
            'ingredient_options' => $this->ingredientService->listSelectableActive()->values()->all(),
            'filters' => $filters->toFrontendFilters(),
        ]);
    }

    public function show(StockCount $stockCount): Response
    {
        $this->authorize('view', $stockCount);

        $stockCount = $this->service->findWithItems($stockCount->id) ?? $stockCount;

        return InertiaPage::ADMIN_STOCK_COUNTS_SHOW->render([
            'stock_count' => StockCountDetailData::fromModel($stockCount)->toArray(),
        ]);
    }

    public function store(StoreStockCountRequest $request): RedirectResponse
    {
        try {
            $this->service->create(StockCountStoreData::from($request->validated()), $request->user());
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', __('admin_stock_count.created').'.');
    }

    public function update(UpdateStockCountRequest $request, StockCount $stockCount): RedirectResponse
    {
        try {
            $this->service->update($stockCount, StockCountUpdateData::from($request->validated()));
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', __('admin_stock_count.updated').'.');
    }

    public function close(StockCount $stockCount): RedirectResponse
    {
        $this->authorize('update', $stockCount);

        try {
            $this->service->close($stockCount, request()->user());
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', __('admin_stock_count.deleted').'.');
    }
}
