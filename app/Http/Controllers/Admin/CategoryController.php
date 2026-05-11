<?php

namespace App\Http\Controllers\Admin;

use App\Data\Categories\CategoryIndexData;
use App\Data\Categories\CategoryListItemData;
use App\Data\Categories\CategoryStoreData;
use App\Data\Categories\CategoryUpdateData;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
{
    public function __construct(private readonly CategoryService $service) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Category::class);

        $filters = CategoryIndexData::from($request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'sort_field' => ['nullable', 'in:name,sort_order,is_active'],
            'sort_direction' => ['nullable', 'in:asc,desc'],
            'per_page' => ['nullable', 'integer', 'min:5', 'max:50'],
        ]));

        $paginator = $this->service
            ->paginateForAdmin($filters)
            ->through(fn (Category $category): array => CategoryListItemData::from($category)->toArray());

        return Inertia::render('Admin/Categories/Index', [
            'categories' => $paginator,
            'filters' => $filters->toFrontendFilters(),
        ]);
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $this->service->create(CategoryStoreData::from($request->validated()));

        return redirect()
            ->route('admin.categories.index')
            ->with('success', __('admin_categories.category_created').'.');
    }

    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        $this->service->update($category, CategoryUpdateData::from($request->validated()));

        return redirect()
            ->route('admin.categories.index')
            ->with('success', __('admin_categories.category_updated').'.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        $this->authorize('delete', $category);

        $this->service->delete($category);

        return redirect()
            ->route('admin.categories.index')
            ->with('success', __('admin_categories.category_deleted').'.');
    }
}
