<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreIngredientRequest;
use App\Http\Requests\UpdateIngredientRequest;
use App\Models\Ingredient;
use App\Services\IngredientService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class IngredientController extends Controller
{
    public function __construct(private readonly IngredientService $service)
    {
    }

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Ingredient::class);

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:160'],
            'is_active' => ['nullable', 'in:0,1'],
            'unit' => ['nullable', 'string', 'in:g,kg,ml,l,db'],
            'sort_field' => ['nullable', 'in:name,unit,current_stock,minimum_stock,is_active'],
            'sort_direction' => ['nullable', 'in:asc,desc'],
            'per_page' => ['nullable', 'integer', 'min:5', 'max:50'],
        ]);

        $paginator = $this->service
            ->paginateForAdmin($filters)
            ->through(fn (Ingredient $ingredient): array => [
                'id' => $ingredient->id,
                'name' => $ingredient->name,
                'slug' => $ingredient->slug,
                'sku' => $ingredient->sku,
                'unit' => $ingredient->unit,
                'current_stock' => (float) $ingredient->current_stock,
                'minimum_stock' => (float) $ingredient->minimum_stock,
                'is_low_stock' => $ingredient->isLowStock(),
                'is_active' => $ingredient->is_active,
                'notes' => $ingredient->notes,
                'updated_at' => $ingredient->updated_at?->toDateTimeString(),
            ]);

        return Inertia::render('Admin/Ingredients/Index', [
            'ingredients' => $paginator,
            'filters' => [
                'search' => (string) ($filters['search'] ?? ''),
                'is_active' => isset($filters['is_active']) ? (string) $filters['is_active'] : '',
                'unit' => (string) ($filters['unit'] ?? ''),
                'sort_field' => (string) ($filters['sort_field'] ?? 'name'),
                'sort_direction' => (string) ($filters['sort_direction'] ?? 'asc'),
                'per_page' => (int) ($filters['per_page'] ?? 10),
            ],
            'units' => Ingredient::allowedUnits(),
        ]);
    }

    public function store(StoreIngredientRequest $request): RedirectResponse
    {
        $this->service->create($request->validated());

        return redirect()
            ->route('admin.ingredients.index')
            ->with('success', 'Alapanyag letrehozva.');
    }

    public function update(UpdateIngredientRequest $request, Ingredient $ingredient): RedirectResponse
    {
        $this->service->update($ingredient, $request->validated());

        return redirect()
            ->route('admin.ingredients.index')
            ->with('success', 'Alapanyag frissitve.');
    }

    public function destroy(Ingredient $ingredient): RedirectResponse
    {
        $this->authorize('delete', $ingredient);

        $this->service->delete($ingredient);

        return redirect()
            ->route('admin.ingredients.index')
            ->with('success', 'Alapanyag torolve.');
    }
}
