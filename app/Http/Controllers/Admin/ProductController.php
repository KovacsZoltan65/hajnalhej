<?php

namespace App\Http\Controllers\Admin;

use App\Data\Products\ProductIndexData;
use App\Data\Products\ProductInlineUpdateData;
use App\Data\Products\ProductStoreData;
use App\Data\Products\ProductUpdateData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\InlineUpdateProductRequest;
use App\Http\Requests\Admin\StoreProductCreateFlowRequest;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use App\Services\ProductCreateFlowService;
use App\Services\ProductIngredientService;
use App\Services\ProductService;
use App\Support\InertiaPage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function __construct(
        private readonly ProductService $service,
        private readonly ProductIngredientService $productIngredientService,
        private readonly ProductCreateFlowService $createFlowService,
    ) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Product::class);

        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:160'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'is_active' => ['nullable', 'in:0,1'],
            'active' => ['nullable', 'boolean'],
            'sort_field' => ['nullable', 'in:name,slug,price,is_active,sort_order'],
            'sort_direction' => ['nullable', 'in:asc,desc'],
            'per_page' => ['nullable', 'integer', 'min:5', 'max:50'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $filters = ProductIndexData::from($validated);
        $paginator = $this->service->paginateForAdmin($filters);

        return Inertia::render(InertiaPage::ADMIN_PRODUCTS_INDEX->value, [
            'products' => $paginator,
            'categories' => $this->service->listSelectableCategories(),
            'ingredients' => $this->productIngredientService->listSelectableIngredients(),
            'stockStatuses' => [
                ['value' => Product::STOCK_IN_STOCK, 'label' => __('admin_product.status_in_stock')],
                ['value' => Product::STOCK_PREORDER, 'label' => __('admin_product.status_available_for_preorder')],
                ['value' => Product::STOCK_OUT_OF_STOCK, 'label' => __('admin_product.status_out_of_stock')],
            ],
            'filters' => $filters->toFrontendFilters(),
        ]);
    }

    public function createFlow(): Response
    {
        $this->authorize('create', Product::class);

        return Inertia::render(InertiaPage::ADMIN_PRODUCTS_CREATE_FLOW->value, [
            'categories' => $this->service->listSelectableCategories(),
            'ingredients' => $this->productIngredientService->listSelectableIngredients(),
            'stockStatuses' => [
                ['value' => Product::STOCK_IN_STOCK, 'label' => __('admin_product.status_in_stock')],
                ['value' => Product::STOCK_PREORDER, 'label' => __('admin_product.status_available_for_preorder')],
                ['value' => Product::STOCK_OUT_OF_STOCK, 'label' => __('admin_product.status_out_of_stock')],
            ],
        ]);
    }

    public function storeFlow(StoreProductCreateFlowRequest $request): RedirectResponse
    {
        $product = $this->createFlowService->store($request->validated());

        return redirect()
            ->route('admin.recipes.index', ['product_id' => $product->id])
            ->with('success', __('admin.products.flow.saved').'.');
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $this->service->store(ProductStoreData::from($request->validated()));

        return redirect()
            ->route('admin.products.index')
            ->with('success', __('admin_product.created').'.');
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $this->service->update($product, ProductUpdateData::from($request->validated()));

        return redirect()
            ->route('admin.products.index')
            ->with('success', __('admin_product.updated').'.');
    }

    public function updateInline(InlineUpdateProductRequest $request, Product $product): RedirectResponse
    {
        $this->service->updateInline($product, ProductInlineUpdateData::from($request->validated()));

        return back(303)->with('success', __('admin.common.inline_edit.saved'));
    }

    public function destroy(Product $product): RedirectResponse
    {
        $this->authorize('delete', $product);

        $this->service->delete($product);

        return redirect()
            ->route('admin.products.index')
            ->with('success', __('admin_product.deleted').'.');
    }
}
