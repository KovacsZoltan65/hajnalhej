<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAdminUserRequest;
use App\Http\Requests\Admin\StoreUserDiscountRequest;
use App\Http\Requests\Admin\StoreUserTemporaryPermissionRequest;
use App\Http\Requests\Admin\UpdateAdminUserRequest;
use App\Http\Requests\Admin\UpdateUserDiscountRequest;
use App\Models\User;
use App\Models\UserDiscount;
use App\Models\UserTemporaryPermission;
use App\Services\UserAdminService;
use App\Support\PermissionRegistry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Permission;

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

        $canViewOrders = $request->user()?->can('viewOrders', User::class) ?? false;

        $users = $this->service
            ->paginateForAdmin($filters)
            ->through(fn (User $user): array => $this->service->serializeUser($user, $canViewOrders));

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'roles' => $this->service->roleOptions(),
            'permissions' => Permission::query()
                ->where('guard_name', 'web')
                ->orderBy('name')
                ->get(['name'])
                ->map(fn (Permission $permission): array => ['name' => $permission->name])
                ->values()
                ->all(),
            'filters' => [
                'search' => (string) ($filters['search'] ?? ''),
                'status' => (string) ($filters['status'] ?? ''),
                'sort_field' => (string) ($filters['sort_field'] ?? 'created_at'),
                'sort_direction' => (string) ($filters['sort_direction'] ?? 'desc'),
                'per_page' => (int) ($filters['per_page'] ?? 15),
            ],
            'status_options' => User::statuses(),
            'discount_types' => UserDiscount::types(),
            'can' => [
                'create' => $request->user()?->can('create', User::class) ?? false,
                'update' => $request->user()?->can(PermissionRegistry::ADMIN_USERS_UPDATE) ?? false,
                'delete' => $request->user()?->can(PermissionRegistry::ADMIN_USERS_DELETE) ?? false,
                'manage_roles' => $request->user()?->can('manageRoles', User::class) ?? false,
                'manage_temporary_permissions' => $request->user()?->can('manageTemporaryPermissions', User::class) ?? false,
                'manage_discounts' => $request->user()?->can('manageDiscounts', User::class) ?? false,
                'view_orders' => $canViewOrders,
            ],
        ]);
    }

    public function store(StoreAdminUserRequest $request): RedirectResponse
    {
        if (($request->validated()['roles'] ?? []) !== []) {
            $this->authorize('manageRoles', User::class);
        }

        $this->service->create($request->validated(), $request->user());

        return redirect()->route('admin.users.index')
            ->with('success', __('admin_user.created') . '.');
    }

    public function update(UpdateAdminUserRequest $request, User $user): RedirectResponse
    {
        if (\array_key_exists('roles', $request->validated())) {
            $this->authorize('manageRoles', User::class);
        }

        $this->service->update($user, $request->validated(), $request->user());

        return redirect()->route('admin.users.index')
            ->with('success', __('admin_user.updated') . '.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->authorize('delete', $user);

        $this->service->deactivate($user);

        return redirect()->route('admin.users.index')
            ->with('success', __('admin_user.deleted') . '.');
    }

    public function storeTemporaryPermission(StoreUserTemporaryPermissionRequest $request, User $user): RedirectResponse
    {
        $this->service->createTemporaryPermission($user, $request->validated(), $request->user());

        return back()->with('success', __('admin_user.temporary_authorization_recorded') . '.');
    }

    public function revokeTemporaryPermission(User $user, UserTemporaryPermission $temporaryPermission): RedirectResponse
    {
        $this->authorize('manageTemporaryPermissions', User::class);
        abort_unless($temporaryPermission->user_id === $user->id, 404);

        $this->service->revokeTemporaryPermission($temporaryPermission);

        return back()
            ->with('success', __('admin_user.temporary_authorization_revoked') . '.');
    }

    public function storeDiscount(StoreUserDiscountRequest $request, User $user): RedirectResponse
    {
        $this->service->createDiscount($user, $request->validated(), $request->user());

        return back()->with('success', __('admin_user.discount_recorded') . '.');
    }

    public function updateDiscount(UpdateUserDiscountRequest $request, User $user, UserDiscount $discount): RedirectResponse
    {
        $this->authorize('manageDiscounts', User::class);
        abort_unless($discount->user_id === $user->id, 404);

        $this->service->updateDiscount($discount, $request->validated());

        return back()->with('success', __('admin_user.updated') . '.');
    }

    public function destroyDiscount(User $user, UserDiscount $discount): RedirectResponse
    {
        $this->authorize('manageDiscounts', User::class);
        abort_unless($discount->user_id === $user->id, 404);

        $this->service->deactivateDiscount($discount);

        return back()->with('success', __('admin_user.discount_deactivated') . '.');
    }
}
