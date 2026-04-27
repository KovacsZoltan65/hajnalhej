<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\IngredientSupplierTermIndexRequest;
use App\Http\Requests\Admin\StoreIngredientSupplierTermRequest;
use App\Http\Requests\Admin\UpdateIngredientSupplierTermRequest;
use App\Models\Ingredient;
use App\Models\IngredientSupplierTerm;
use App\Models\Supplier;
use App\Services\IngredientSupplierTermService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class IngredientSupplierTermController extends Controller
{
    /**
     * @param IngredientSupplierTermService $service
     */
    public function __construct(private readonly IngredientSupplierTermService $service)
    {
    }

    /**
     * @param IngredientSupplierTermIndexRequest $request
     * @return \Inertia\Response
     */
    public function index(IngredientSupplierTermIndexRequest $request): Response
    {
        $filters = $request->validated();
        $terms = $this->service->paginateForAdmin($filters)->through(static fn (IngredientSupplierTerm $term): array => [
            'id' => $term->id,
            'ingredient_id' => $term->ingredient_id,
            'supplier_id' => $term->supplier_id,
            'ingredient_name' => $term->ingredient?->name,
            'ingredient_unit' => $term->ingredient?->unit,
            'supplier_name' => $term->supplier?->name,
            'lead_time_days' => $term->lead_time_days,
            'minimum_order_quantity' => $term->minimum_order_quantity,
            'pack_size' => $term->pack_size,
            'unit_cost_override' => $term->unit_cost_override,
            'preferred' => $term->preferred,
            'active' => $term->active,
            'meta' => $term->meta,
            'created_at' => $term->created_at?->toDateTimeString(),
            'updated_at' => $term->updated_at?->toDateTimeString(),
        ]);

        return Inertia::render('Admin/IngredientSupplierTerms/Index', [
            'terms' => $terms,
            'filters' => [
                'search' => (string) ($filters['search'] ?? ''),
                'active' => (string) ($filters['active'] ?? ''),
                'sort_field' => (string) ($filters['sort_field'] ?? 'ingredient'),
                'sort_direction' => (string) ($filters['sort_direction'] ?? 'asc'),
                'per_page' => (int) ($filters['per_page'] ?? 10),
            ],
            'ingredients' => Ingredient::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'unit']),
            'suppliers' => Supplier::query()
                ->where('active', true)
                ->orderBy('name')
                ->get(['id', 'name']),
        ]);
    }

    /**
     * @param StoreIngredientSupplierTermRequest $request
     * @return RedirectResponse
     */
    public function store(StoreIngredientSupplierTermRequest $request): RedirectResponse
    {
        $this->service->create($request->validated());

        return back()->with('success', 'Beszállítói feltétel létrehozva.');
    }

    /**
     * @param UpdateIngredientSupplierTermRequest $request
     * @param IngredientSupplierTerm $ingredientSupplierTerm
     * @return RedirectResponse
     */
    public function update(UpdateIngredientSupplierTermRequest $request, IngredientSupplierTerm $ingredientSupplierTerm): RedirectResponse
    {
        $this->service->update($ingredientSupplierTerm, $request->validated());

        return back()->with('success', 'Beszállítói feltétel frissítve.');
    }

    /**
     * @param IngredientSupplierTerm $ingredientSupplierTerm
     * @return RedirectResponse
     */
    public function destroy(IngredientSupplierTerm $ingredientSupplierTerm): RedirectResponse
    {
        $this->authorize('delete', $ingredientSupplierTerm);

        $this->service->delete($ingredientSupplierTerm);

        return back()->with('success', 'Beszállítói feltétel törölve.');
    }
}
