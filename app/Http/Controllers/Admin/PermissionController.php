<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PermissionIndexRequest;
use App\Http\Requests\Admin\SyncPermissionsRequest;
use App\Services\PermissionManagementService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function __construct(private readonly PermissionManagementService $service)
    {
    }

    /**
     * @param PermissionIndexRequest $request
     * @return \Inertia\Response
     */
    public function index(PermissionIndexRequest $request): Response
    {
        $this->authorize('viewAny', Permission::class);

        $filters = $request->validated();
        $canViewUsage = $request->user()?->can('viewUsage', Permission::class) ?? false;
        $permissions = $this->service
            ->paginateForAdmin($filters)
            ->through(function (array $row) use ($canViewUsage): array {
                if ($canViewUsage) {
                    return $row;
                }

                $row['roles_count'] = 0;
                $row['users_count'] = 0;
                $row['role_names'] = [];

                return $row;
            });

        return Inertia::render('Admin/Permissions/Index', [
            'permissions' => $permissions,
            'modules' => $this->service->modules(),
            'filters' => [
                'search' => (string) ($filters['search'] ?? ''),
                'module' => (string) ($filters['module'] ?? ''),
                'dangerous_only' => (bool) ($filters['dangerous_only'] ?? false),
                'usage_state' => (string) ($filters['usage_state'] ?? ''),
                'registry_state' => (string) ($filters['registry_state'] ?? ''),
                'sort_field' => (string) ($filters['sort_field'] ?? 'name'),
                'sort_direction' => (string) ($filters['sort_direction'] ?? 'asc'),
                'per_page' => (int) ($filters['per_page'] ?? 20),
            ],
            'can' => [
                'view_usage' => $canViewUsage,
                'sync' => $request->user()?->can('sync', Permission::class) ?? false,
            ],
        ]);
    }

    /**
     * @param Request $request
     * @param string $permissionName
     * @return \Inertia\Response
     */
    public function show(Request $request, string $permissionName): Response
    {
        $this->authorize('viewAny', Permission::class);

        $canViewUsage = $request->user()?->can('viewUsage', Permission::class) ?? false;
        $detail = $this->service->detail($permissionName);
        if (! $canViewUsage) {
            $detail['roles_count'] = 0;
            $detail['users_count'] = 0;
            $detail['role_names'] = [];
        }

        return Inertia::render('Admin/Permissions/Show', [
            'permission' => $detail,
            'can' => [
                'view_usage' => $canViewUsage,
            ],
        ]);
    }

    /**
     * @param SyncPermissionsRequest $request
     * @return RedirectResponse
     */
    public function sync(SyncPermissionsRequest $request): RedirectResponse
    {
        $this->authorize('sync', Permission::class);

        $summary = $this->service->syncFromRegistry(
            actor: $request->user(),
            dryRun: (bool) ($request->validated('dry_run') ?? false),
        );

        return back()
            ->with('success', __('commerce.permissions.synced'))
            ->with('sync_summary', $summary);
    }
}
