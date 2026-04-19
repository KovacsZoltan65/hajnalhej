<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductIngredientRequest;
use App\Http\Requests\UpdateProductIngredientRequest;
use App\Models\Product;
use App\Models\ProductIngredient;
use App\Services\ProductIngredientService;
use Illuminate\Http\RedirectResponse;
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
            return redirect()->route('admin.products.index')->with('error', $exception->getMessage());
        }

        return redirect()->route('admin.products.index')->with('success', 'Recept tetel hozzaadva.');
    }

    public function update(UpdateProductIngredientRequest $request, Product $product, ProductIngredient $productIngredient): RedirectResponse
    {
        if ($productIngredient->product_id !== $product->id) {
            abort(404);
        }

        try {
            $this->service->update($product, $productIngredient, $request->validated());
        } catch (RuntimeException $exception) {
            return redirect()->route('admin.products.index')->with('error', $exception->getMessage());
        }

        return redirect()->route('admin.products.index')->with('success', 'Recept tetel frissitve.');
    }

    public function destroy(Product $product, ProductIngredient $productIngredient): RedirectResponse
    {
        $this->authorize('update', $product);

        if ($productIngredient->product_id !== $product->id) {
            abort(404);
        }

        $this->service->delete($productIngredient);

        return redirect()->route('admin.products.index')->with('success', 'Recept tetel torolve.');
    }
}
