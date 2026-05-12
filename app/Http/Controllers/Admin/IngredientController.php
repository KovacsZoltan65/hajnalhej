<?php

namespace App\Http\Controllers\Admin;

use App\Data\Ingredients\IngredientIndexData;
use App\Data\Ingredients\IngredientInlineUpdateData;
use App\Data\Ingredients\IngredientListItemData;
use App\Data\Ingredients\IngredientStoreData;
use App\Data\Ingredients\IngredientUpdateData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\InlineUpdateIngredientRequest;
use App\Http\Requests\StoreIngredientRequest;
use App\Http\Requests\UpdateIngredientRequest;
use App\Models\Ingredient;
use App\Services\IngredientService;
use App\Support\InertiaPage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;

class IngredientController extends Controller
{
    public function __construct(private readonly IngredientService $service) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Ingredient::class);

        $filters = IngredientIndexData::from($request->validate([
            'search' => ['nullable', 'string', 'max:160'],
            'is_active' => ['nullable', 'in:0,1'],
            'unit' => ['nullable', 'string', 'in:g,kg,ml,l,db'],
            'sort_field' => ['nullable', 'in:name,unit,estimated_unit_cost,current_stock,minimum_stock,is_active'],
            'sort_direction' => ['nullable', 'in:asc,desc'],
            'per_page' => ['nullable', 'integer', 'min:5', 'max:50'],
        ]));

        $paginator = $this->service
            ->paginateForAdmin($filters)
            ->through(fn (Ingredient $ingredient): array => IngredientListItemData::from($ingredient)->toArray());

        return InertiaPage::ADMIN_INGREDIENTS_INDEX->render([
            'ingredients' => $paginator,
            'filters' => $filters->toFrontendFilters(),
            'units' => Ingredient::allowedUnits(),
        ]);
    }

    public function store(StoreIngredientRequest $request): RedirectResponse
    {
        $this->service->create(IngredientStoreData::from($request->validated()));

        return redirect()
            ->route('admin.ingredients.index')
            ->with('success', __('admin_ingredients.material_created').'.');
    }

    public function update(UpdateIngredientRequest $request, Ingredient $ingredient): RedirectResponse
    {
        $this->service->update($ingredient, IngredientUpdateData::from($request->validated()));

        return redirect()
            ->route('admin.ingredients.index')
            ->with('success', __('admin_ingredients.material_updated').'.');
    }

    public function updateInline(InlineUpdateIngredientRequest $request, Ingredient $ingredient): RedirectResponse
    {
        $this->service->updateInline($ingredient, IngredientInlineUpdateData::from($request->validated()));

        return back(303)->with('success', __('admin.common.inline_edit.saved'));
    }

    public function destroy(Ingredient $ingredient): RedirectResponse
    {
        $this->authorize('delete', $ingredient);

        $this->service->delete($ingredient);

        return redirect()
            ->route('admin.ingredients.index')
            ->with('success', __('admin_ingredients.material_deleted').'.');
    }
}
