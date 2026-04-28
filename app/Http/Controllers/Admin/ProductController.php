<?php

namespace App\Http\Controllers\Admin;

use App\Data\Products\ProductIndexData;
use App\Data\Products\ProductStoreData;
use App\Data\Products\ProductUpdateData;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use App\Services\ProductIngredientService;
use App\Services\ProductService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function __construct(
        private readonly ProductService $service,
        private readonly ProductIngredientService $productIngredientService,
    ) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Product::class);

        $request->validate([
            'search' => ['nullable', 'string', 'max:160'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'is_active' => ['nullable', 'in:0,1'],
            'active' => ['nullable', 'boolean'],
            'sort_field' => ['nullable', 'in:name,slug,price,is_active,sort_order'],
            'sort_direction' => ['nullable', 'in:asc,desc'],
            'per_page' => ['nullable', 'integer', 'min:5', 'max:50'],
        ]);

        $filters = ProductIndexData::from($request->all());
        $paginator = $this->service->paginateForAdmin($filters);

        return Inertia::render('Admin/Products/Index', [
            'products' => $paginator,
            'categories' => $this->service->listSelectableCategories(),
            'ingredients' => $this->productIngredientService->listSelectableIngredients(),
            'stockStatuses' => [
                ['value' => Product::STOCK_IN_STOCK, 'label' => 'Raktaron'],
                ['value' => Product::STOCK_PREORDER, 'label' => 'Elojegyezheto'],
                ['value' => Product::STOCK_OUT_OF_STOCK, 'label' => 'Nincs keszleten'],
            ],
            'filters' => $filters->toFrontendFilters(),
        ]);
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $this->service->store(ProductStoreData::from($request));

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Termék létrehozva.');
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $this->service->update($product, ProductUpdateData::from($request));

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Termék frissítve.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $this->authorize('delete', $product);

        $this->service->delete($product);

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Termék törölve.');
    }
}
