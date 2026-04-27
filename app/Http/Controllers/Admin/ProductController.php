<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use App\Models\ProductIngredient;
use App\Services\ProductIngredientService;
use App\Services\ProductService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    /**
     * @param ProductService $service
     * @param ProductIngredientService $productIngredientService
     */
    public function __construct(
        private readonly ProductService $service,
        private readonly ProductIngredientService $productIngredientService,
    ) {}

    /**
     * @param Request $request
     * @return \Inertia\Response
     */
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Product::class);

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:160'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'is_active' => ['nullable', 'in:0,1'],
            'sort_field' => ['nullable', 'in:name,slug,price,is_active,sort_order'],
            'sort_direction' => ['nullable', 'in:asc,desc'],
            'per_page' => ['nullable', 'integer', 'min:5', 'max:50'],
        ]);

        $paginator = $this->service
            ->paginateForAdmin($filters)
            ->through(fn (Product $product): array => [
                'id' => $product->id,
                'category_id' => $product->category_id,
                'category_name' => $product->category?->name,
                'name' => $product->name,
                'slug' => $product->slug,
                'short_description' => $product->short_description,
                'description' => $product->description,
                'price' => (float) $product->price,
                'is_active' => $product->is_active,
                'is_featured' => $product->is_featured,
                'stock_status' => $product->stock_status,
                'image_path' => $product->image_path,
                'sort_order' => $product->sort_order,
                'product_ingredients' => $product->productIngredients
                    ->map(fn (ProductIngredient $item): array => [
                        'id' => $item->id,
                        'product_id' => $item->product_id,
                        'ingredient_id' => $item->ingredient_id,
                        'ingredient_name' => $item->ingredient?->name,
                        'ingredient_unit' => $item->ingredient?->unit,
                        'ingredient_active' => $item->ingredient?->is_active ?? false,
                        'quantity' => (float) $item->quantity,
                        'sort_order' => $item->sort_order,
                        'notes' => $item->notes,
                    ])
                    ->values()
                    ->all(),
                'updated_at' => $product->updated_at?->toDateTimeString(),
            ]);

        return Inertia::render('Admin/Products/Index', [
            'products' => $paginator,
            'categories' => $this->service->listSelectableCategories(),
            'ingredients' => $this->productIngredientService->listSelectableIngredients(),
            'stockStatuses' => [
                ['value' => Product::STOCK_IN_STOCK, 'label' => 'Raktaron'],
                ['value' => Product::STOCK_PREORDER, 'label' => 'Elojegyezheto'],
                ['value' => Product::STOCK_OUT_OF_STOCK, 'label' => 'Nincs keszleten'],
            ],
            'filters' => [
                'search' => (string) ($filters['search'] ?? ''),
                'category_id' => $filters['category_id'] ?? null,
                'is_active' => isset($filters['is_active']) ? (string) $filters['is_active'] : '',
                'sort_field' => (string) ($filters['sort_field'] ?? 'sort_order'),
                'sort_direction' => (string) ($filters['sort_direction'] ?? 'asc'),
                'per_page' => (int) ($filters['per_page'] ?? 10),
            ],
        ]);
    }

    /**
     * @param StoreProductRequest $request
     * @return RedirectResponse
     */
    public function store(StoreProductRequest $request): RedirectResponse
    {
        $this->service->create($request->validated());

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Termék létrehozva.');
    }

    /**
     * @param UpdateProductRequest $request
     * @param Product $product
     * @return RedirectResponse
     */
    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $this->service->update($product, $request->validated());

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Termék frissítve.');
    }

    /**
     * @param Product $product
     * @return RedirectResponse
     */
    public function destroy(Product $product): RedirectResponse
    {
        $this->authorize('delete', $product);

        $this->service->delete($product);

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Termék törölve.');
    }
}


