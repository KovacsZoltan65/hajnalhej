<?php

namespace App\Http\Controllers\Admin;

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
    public function __construct(private readonly CategoryService $service)
    {
    }

    /**
     * @param Request $request
     * @return \Inertia\Response
     */
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Category::class);

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'sort_field' => ['nullable', 'in:name,sort_order,is_active'],
            'sort_direction' => ['nullable', 'in:asc,desc'],
            'per_page' => ['nullable', 'integer', 'min:5', 'max:50'],
        ]);

        $paginator = $this->service
            ->paginateForAdmin($filters)
            ->through(fn (Category $category): array => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'description' => $category->description,
                'is_active' => $category->is_active,
                'sort_order' => $category->sort_order,
                'products_count' => $category->products_count,
                'updated_at' => $category->updated_at?->toDateTimeString(),
            ]);

        return Inertia::render('Admin/Categories/Index', [
            'categories' => $paginator,
            'filters' => [
                'search' => (string) ($filters['search'] ?? ''),
                'sort_field' => (string) ($filters['sort_field'] ?? 'sort_order'),
                'sort_direction' => (string) ($filters['sort_direction'] ?? 'asc'),
                'per_page' => (int) ($filters['per_page'] ?? 10),
            ],
        ]);
    }

    /**
     * @param StoreCategoryRequest $request
     * @return RedirectResponse
     */
    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $this->service->create($request->validated());

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Kategória létrehozva.');
    }

    /**
     * @param UpdateCategoryRequest $request
     * @param Category $category
     * @return RedirectResponse
     */
    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        $this->service->update($category, $request->validated());

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Kategória frissítve.');
    }

    /**
     * @param Category $category
     * @return RedirectResponse
     */
    public function destroy(Category $category): RedirectResponse
    {
        $this->authorize('delete', $category);

        $this->service->delete($category);

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Kategória törölve.');
    }
}


