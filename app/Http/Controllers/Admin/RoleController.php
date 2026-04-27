<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreRoleRequest;
use App\Http\Requests\Admin\SyncRolePermissionsRequest;
use App\Http\Requests\Admin\UpdateRoleRequest;
use App\Services\RoleManagementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    /**
     * @param RoleManagementService $service
     */
    public function __construct(private readonly RoleManagementService $service)
    {
    }

    /**
     * @param Request $request
     * @return \Inertia\Response
     */
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Role::class);

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'per_page' => ['nullable', 'integer', 'min:10', 'max:100'],
        ]);

        $roles = $this->service->paginateForAdmin($filters)
            ->through(fn (Role $role): array => [
                'id' => $role->id,
                'name' => $role->name,
                'guard_name' => $role->guard_name,
                'permissions_count' => $role->permissions_count,
                'users_count' => $role->users_count,
                'is_system_role' => $this->service->isSystemRole($role->name),
            ]);

        return Inertia::render('Admin/Roles/Index', [
            'roles' => $roles,
            'filters' => [
                'search' => (string) ($filters['search'] ?? ''),
                'per_page' => (int) ($filters['per_page'] ?? 15),
            ],
            'can' => [
                'create' => $request->user()?->can('create', Role::class) ?? false,
                'update' => $request->user()?->can('update', Role::class) ?? false,
                'delete' => $request->user()?->can('delete', Role::class) ?? false,
                'assign_permissions' => $request->user()?->can('syncPermissions', Role::class) ?? false,
            ],
        ]);
    }

    /**
     * @param Request $request
     * @param Role $role
     * @return \Inertia\Response
     */
    public function show(Request $request, Role $role): Response
    {
        $this->authorize('view', $role);

        return Inertia::render('Admin/Roles/Show', [
            'role' => $this->service->buildRolePayload($role),
            'permission_groups' => $this->service->permissionGroups(),
            'can' => [
                'update' => $request->user()?->can('update', $role) ?? false,
                'delete' => $request->user()?->can('delete', $role) ?? false,
                'assign_permissions' => $request->user()?->can('syncPermissions', $role) ?? false,
            ],
        ]);
    }

    /**
     * @param StoreRoleRequest $request
     * @return RedirectResponse
     */
    public function store(StoreRoleRequest $request): RedirectResponse
    {
        $this->service->create($request->validated(), $request->user());

        return redirect()
            ->route('admin.roles.index')
            ->with('success', __('commerce.roles.created'));
    }

    /**
     * @param UpdateRoleRequest $request
     * @param Role $role
     * @return RedirectResponse
     */
    public function update(UpdateRoleRequest $request, Role $role): RedirectResponse
    {
        $this->authorize('update', $role);

        $this->service->update($role, $request->validated(), $request->user());

        return back()->with('success', __('commerce.roles.updated'));
    }

    /**
     * @param Request $request
     * @param Role $role
     * @return RedirectResponse
     */
    public function destroy(Request $request, Role $role): RedirectResponse
    {
        $this->authorize('delete', $role);

        $this->service->delete($role, $request->user());

        return redirect()
            ->route('admin.roles.index')
            ->with('success', __('commerce.roles.deleted'));
    }

    /**
     * @param SyncRolePermissionsRequest $request
     * @param Role $role
     * @return RedirectResponse
     */
    public function syncPermissions(SyncRolePermissionsRequest $request, Role $role): RedirectResponse
    {
        $this->authorize('syncPermissions', $role);

        $this->service->syncPermissions($role, $request->validated()['permissions'], $request->user());

        return back()->with('success', __('commerce.roles.permissions_synced'));
    }
}
