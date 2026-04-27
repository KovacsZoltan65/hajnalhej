<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRecipeStepRequest;
use App\Http\Requests\UpdateRecipeStepRequest;
use App\Models\Product;
use App\Models\RecipeStep;
use App\Services\RecipeStepService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RecipeStepController extends Controller
{
    /**
     * @param RecipeStepService $service
     */
    public function __construct(private readonly RecipeStepService $service)
    {
    }

    /**
     * @param StoreRecipeStepRequest $request
     * @param Product $product
     * @return RedirectResponse
     */
    public function store(StoreRecipeStepRequest $request, Product $product): RedirectResponse
    {
        $this->service->create($product, $request->validated());

        return $this->redirectToOrigin($request)->with('success', 'Receptlépés hozzáadva.');
    }

    /**
     * @param UpdateRecipeStepRequest $request
     * @param Product $product
     * @param RecipeStep $recipeStep
     * @return RedirectResponse
     */
    public function update(UpdateRecipeStepRequest $request, Product $product, RecipeStep $recipeStep): RedirectResponse
    {
        $this->service->update($product, $recipeStep, $request->validated());

        return $this->redirectToOrigin($request)->with('success', 'Receptlépés frissítve.');
    }

    /**
     * @param Request $request
     * @param Product $product
     * @param RecipeStep $recipeStep
     * @return RedirectResponse
     */
    public function destroy(Request $request, Product $product, RecipeStep $recipeStep): RedirectResponse
    {
        $this->authorize('update', $product);

        $this->service->delete($product, $recipeStep);

        return $this->redirectToOrigin($request)->with('success', 'Receptlépés törölve.');
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    private function redirectToOrigin(Request $request): RedirectResponse
    {
        $fallback = route('admin.recipes.index');
        $referer = (string) $request->headers->get('referer', '');

        if (str_contains($referer, '/admin/recipes') || str_contains($referer, '/admin/products')) {
            return redirect()->to($referer);
        }

        return redirect()->to($fallback);
    }
}



