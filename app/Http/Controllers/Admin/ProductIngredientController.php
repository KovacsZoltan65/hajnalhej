<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductIngredientRequest;
use App\Http\Requests\UpdateProductIngredientRequest;
use App\Models\Product;
use App\Models\ProductIngredient;
use App\Services\ProductIngredientService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use RuntimeException;

class ProductIngredientController extends Controller
{
    public function __construct(private readonly ProductIngredientService $service)
    {
    }

    public function store(StoreProductIngredientRequest $request, Product $product): RedirectResponse
    {
        try {
            $this->service->create($product, $request->validated());
        } catch (RuntimeException $exception) {
            return $this->redirectToOrigin($request)->with('error', $exception->getMessage());
        }

        return $this->redirectToOrigin($request)->with('success', 'Recept tétel hozzáadva.');
    }

    public function update(UpdateProductIngredientRequest $request, Product $product, ProductIngredient $productIngredient): RedirectResponse
    {
        if ($productIngredient->product_id !== $product->id) {
            abort(404);
        }

        try {
            $this->service->update($product, $productIngredient, $request->validated());
        } catch (RuntimeException $exception) {
            return $this->redirectToOrigin($request)->with('error', $exception->getMessage());
        }

        return $this->redirectToOrigin($request)->with('success', 'Recept tétel frissítve.');
    }

    public function destroy(Request $request, Product $product, ProductIngredient $productIngredient): RedirectResponse
    {
        $this->authorize('update', $product);

        if ($productIngredient->product_id !== $product->id) {
            abort(404);
        }

        $this->service->delete($productIngredient);

        return $this->redirectToOrigin($request)->with('success', 'Recept tétel törölve.');
    }

    private function redirectToOrigin(Request $request): RedirectResponse
    {
        $fallback = route('admin.products.index');
        $referer = (string) $request->headers->get('referer', '');

        if (str_contains($referer, '/admin/recipes') || str_contains($referer, '/admin/products')) {
            return redirect()->to($referer);
        }

        return redirect()->to($fallback);
    }
}


