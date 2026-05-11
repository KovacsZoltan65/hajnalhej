<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Data\Branches\BranchFormOptionsData;
use App\Data\Branches\BranchIndexData;
use App\Data\Branches\BranchListItemData;
use App\Data\Branches\BranchStoreData;
use App\Data\Branches\BranchUpdateData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Branch\IndexBranchRequest;
use App\Http\Requests\Admin\Branch\StoreBranchRequest;
use App\Http\Requests\Admin\Branch\UpdateBranchRequest;
use App\Models\Branch;
use App\Services\BranchService;
use App\Support\InertiaPage;
use App\Support\PermissionRegistry;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class BranchController extends Controller
{
    public function __construct(private readonly BranchService $service) {}

    public function index(IndexBranchRequest $request): Response
    {
        $filters = BranchIndexData::from($request->validated());

        $paginator = $this->service
            ->paginateForAdmin($filters)
            ->through(fn (Branch $branch): array => BranchListItemData::from($branch)->toArray());

        return Inertia::render(InertiaPage::ADMIN_BRANCHES_INDEX->value, [
            'branches' => $paginator,
            'filters' => $filters->toFrontendFilters(),
            'options' => BranchFormOptionsData::make()->toArray(),
            'can' => [
                'create' => $request->user()?->can('create', Branch::class) ?? false,
                'update' => $request->user()?->can(PermissionRegistry::BRANCHES_UPDATE) ?? false,
                'delete' => $request->user()?->can(PermissionRegistry::BRANCHES_DELETE) ?? false,
            ],
        ]);
    }

    public function store(StoreBranchRequest $request): RedirectResponse
    {
        $this->service->create(BranchStoreData::from($request->validated()));

        return redirect()
            ->route('admin.branches.index')
            ->with('success', __('admin_branches.branch_created').'.');
    }

    public function update(UpdateBranchRequest $request, Branch $branch): RedirectResponse
    {
        $this->service->update($branch, BranchUpdateData::from($request->validated()));

        return redirect()
            ->route('admin.branches.index')
            ->with('success', __('admin_branches.branch_updated').'.');
    }

    public function destroy(Branch $branch): RedirectResponse
    {
        $this->authorize('delete', $branch);

        $this->service->delete($branch);

        return redirect()
            ->route('admin.branches.index')
            ->with('success', __('admin_branches.branch_deleted').'.');
    }
}
