<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SyncUserRolesRequest;
use App\Models\User;
use App\Services\UserRoleAssignmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserRoleController extends Controller
{
    public function __construct(private readonly UserRoleAssignmentService $service)
    {
    }

    public function index(Request $request): Response
    {
        $canAssignRoles = $request->user()?->can('assignRoles', User::class) ?? false;
        $canViewPermissions = $request->user()?->can('viewPermissions', User::class) ?? false;

        abort_unless($canAssignRoles || $canViewPermissions, 403);

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'per_page' => ['nullable', 'integer', 'min:10', 'max:100'],
        ]);

        $users = $this->service
            ->paginateUsers($filters)
            ->through(fn (User $user): array => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->roles->pluck('name')->sort()->values()->all(),
                'permissions' => $canViewPermissions
                    ? $user->getAllPermissions()->pluck('name')->sort()->values()->all()
                    : [],
            ]);

        return Inertia::render('Admin/UserRoles/Index', [
            'users' => $users,
            'role_options' => $this->service->roleOptions(),
            'filters' => [
                'search' => (string) ($filters['search'] ?? ''),
                'per_page' => (int) ($filters['per_page'] ?? 15),
            ],
            'can' => [
                'assign_roles' => $canAssignRoles,
                'view_permissions' => $canViewPermissions,
            ],
        ]);
    }

    public function update(SyncUserRolesRequest $request, User $user): RedirectResponse
    {
        $this->authorize('assignRoles', User::class);

        $this->service->syncUserRoles($user, $request->validated()['roles'], $request->user());

        return back()->with('success', __('commerce.user_roles.updated'));
    }
}
