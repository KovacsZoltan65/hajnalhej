<?php

namespace App\Http\Controllers\Admin;

use App\Data\IngredientSupplierTerms\IngredientSupplierTermIndexData;
use App\Data\IngredientSupplierTerms\IngredientSupplierTermInlineUpdateData;
use App\Data\IngredientSupplierTerms\IngredientSupplierTermListItemData;
use App\Data\IngredientSupplierTerms\IngredientSupplierTermStoreData;
use App\Data\IngredientSupplierTerms\IngredientSupplierTermUpdateData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\IngredientSupplierTermIndexRequest;
use App\Http\Requests\Admin\InlineUpdateIngredientSupplierTermRequest;
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
    public function __construct(private readonly IngredientSupplierTermService $service) {}

    public function index(IngredientSupplierTermIndexRequest $request): Response
    {
        $filters = IngredientSupplierTermIndexData::from($request->validated());
        $terms = $this->service
            ->paginateForAdmin($filters)
            ->through(static fn (IngredientSupplierTerm $term): array => IngredientSupplierTermListItemData::from($term)->toArray());

        return Inertia::render('Admin/IngredientSupplierTerms/Index', [
            'terms' => $terms,
            'filters' => $filters->toFrontendFilters(),
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

    public function store(StoreIngredientSupplierTermRequest $request): RedirectResponse
    {
        $this->service->create(IngredientSupplierTermStoreData::from($request->validated()));

        return back()->with('success', __('admin_supplier_terms.created').'.');
    }

    public function update(UpdateIngredientSupplierTermRequest $request, IngredientSupplierTerm $ingredientSupplierTerm): RedirectResponse
    {
        $this->service->update($ingredientSupplierTerm, IngredientSupplierTermUpdateData::from($request->validated()));

        return back()->with('success', __('admin_supplier_terms.updated').'.');
    }

    public function updateInline(InlineUpdateIngredientSupplierTermRequest $request, IngredientSupplierTerm $ingredientSupplierTerm): RedirectResponse
    {
        $this->service->updateInline($ingredientSupplierTerm, IngredientSupplierTermInlineUpdateData::from($request->validated()));

        return back(303)->with('success', __('admin.common.inline_edit.saved'));
    }

    public function destroy(IngredientSupplierTerm $ingredientSupplierTerm): RedirectResponse
    {
        $this->authorize('delete', $ingredientSupplierTerm);

        $this->service->delete($ingredientSupplierTerm);

        return back()->with('success', __('admin_supplier_terms.deleted').'.');
    }
}
