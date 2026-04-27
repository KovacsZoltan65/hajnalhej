<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StockCountIndexRequest;
use App\Http\Requests\StoreStockCountRequest;
use App\Http\Requests\UpdateStockCountRequest;
use App\Models\StockCount;
use App\Services\IngredientService;
use App\Services\StockCountService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use RuntimeException;

class StockCountController extends Controller
{
    /**
     * @param StockCountService $service
     * @param IngredientService $ingredientService
     */
    public function __construct(
        private readonly StockCountService $service,
        private readonly IngredientService $ingredientService,
    ) {
    }

    /**
     * @param StockCountIndexRequest $request
     * @return \Inertia\Response
     */
    public function index(StockCountIndexRequest $request): Response
    {
        $this->authorize('viewAny', StockCount::class);

        $filters = $request->validated();
        $stockCounts = $this->service->paginateForAdmin($filters)->through(static fn (StockCount $count): array => [
            'id' => $count->id,
            'count_date' => $count->count_date?->toDateString(),
            'status' => $count->status,
            'notes' => $count->notes,
            'items_count' => (int) ($count->items_count ?? 0),
            'created_by' => $count->creator?->name,
            'closed_at' => $count->closed_at?->toDateTimeString(),
        ]);

        return Inertia::render('Admin/StockCounts/Index', [
            'stock_counts' => $stockCounts,
            'statuses' => StockCount::statuses(),
            'ingredient_options' => $this->ingredientService->listSelectableActive()->values()->all(),
            'filters' => [
                'status' => (string) ($filters['status'] ?? ''),
                'date_from' => $filters['date_from'] ?? '',
                'date_to' => $filters['date_to'] ?? '',
                'per_page' => (int) ($filters['per_page'] ?? 10),
            ],
        ]);
    }

    /**
     * @param StockCount $stockCount
     * @return \Inertia\Response
     */
    public function show(StockCount $stockCount): Response
    {
        $this->authorize('view', $stockCount);

        $stockCount = $this->service->findWithItems($stockCount->id) ?? $stockCount;

        return Inertia::render('Admin/StockCounts/Show', [
            'stock_count' => [
                'id' => $stockCount->id,
                'count_date' => $stockCount->count_date?->toDateString(),
                'status' => $stockCount->status,
                'notes' => $stockCount->notes,
                'closed_at' => $stockCount->closed_at?->toDateTimeString(),
                'items' => $stockCount->items->map(static fn ($item): array => [
                    'id' => $item->id,
                    'ingredient_id' => $item->ingredient_id,
                    'ingredient_name' => $item->ingredient?->name,
                    'unit' => $item->ingredient?->unit,
                    'expected_quantity' => (float) $item->expected_quantity,
                    'counted_quantity' => (float) $item->counted_quantity,
                    'difference' => (float) $item->difference,
                ])->values()->all(),
            ],
        ]);
    }

    /**
     * @param StoreStockCountRequest $request
     * @return RedirectResponse
     */
    public function store(StoreStockCountRequest $request): RedirectResponse
    {
        try {
            $this->service->create($request->validated(), $request->user());
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', 'Leltár létrehozva.');
    }

    /**
     * @param UpdateStockCountRequest $request
     * @param StockCount $stockCount
     * @return RedirectResponse
     */
    public function update(UpdateStockCountRequest $request, StockCount $stockCount): RedirectResponse
    {
        try {
            $this->service->update($stockCount, $request->validated());
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', 'Leltár frissítve.');
    }

    /**
     * @param StockCount $stockCount
     * @return RedirectResponse
     */
    public function close(StockCount $stockCount): RedirectResponse
    {
        $this->authorize('update', $stockCount);

        try {
            $this->service->close($stockCount, request()->user());
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', 'Leltár lezárva, korrekciók könyvelve.');
    }
}

