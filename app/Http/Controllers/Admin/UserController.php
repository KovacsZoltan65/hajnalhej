<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAdminUserRequest;
use App\Http\Requests\Admin\UpdateAdminUserRequest;
use App\Models\User;
use App\Services\UserAdminService;
use App\Support\PermissionRegistry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function __construct(private readonly UserAdminService $service)
    {
    }

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', User::class);

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', 'in:active,inactive'],
            'sort_field' => ['nullable', 'in:name,email,status,created_at'],
            'sort_direction' => ['nullable', 'in:asc,desc'],
            'per_page' => ['nullable', 'integer', 'min:10', 'max:100'],
        ]);

        $users = $this->service
            ->paginateForAdmin($filters)
            ->through(fn (User $user): array => $this->service->serializeUser($user));

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'roles' => $this->service->roleOptions(),
            'filters' => [
                'search' => (string) ($filters['search'] ?? ''),
                'status' => (string) ($filters['status'] ?? ''),
                'sort_field' => (string) ($filters['sort_field'] ?? 'created_at'),
                'sort_direction' => (string) ($filters['sort_direction'] ?? 'desc'),
                'per_page' => (int) ($filters['per_page'] ?? 15),
            ],
            'status_options' => User::statuses(),
            'can' => [
                'create' => $request->user()?->can('create', User::class) ?? false,
                'update' => $request->user()?->can(PermissionRegistry::ADMIN_USERS_UPDATE) ?? false,
                'delete' => $request->user()?->can(PermissionRegistry::ADMIN_USERS_DELETE) ?? false,
                'manage_roles' => $request->user()?->can('manageRoles', User::class) ?? false,
            ],
        ]);
    }

    public function store(StoreAdminUserRequest $request): RedirectResponse
    {
        if (($request->validated()['roles'] ?? []) !== []) {
            $this->authorize('manageRoles', User::class);
        }

        $this->service->create($request->validated(), $request->user());

        return redirect()->route('admin.users.index')->with('success', 'Felhasználó létrehozva.');
    }

    public function update(UpdateAdminUserRequest $request, User $user): RedirectResponse
    {
        if (\array_key_exists('roles', $request->validated())) {
            $this->authorize('manageRoles', User::class);
        }

        $this->service->update($user, $request->validated(), $request->user());

        return redirect()->route('admin.users.index')->with('success', 'Felhasználó frissítve.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->authorize('delete', $user);

        $this->service->deactivate($user);

        return redirect()->route('admin.users.index')->with('success', 'Felhasználó inaktiválva.');
    }
}
