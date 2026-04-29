<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use App\Models\UserDiscount;
use App\Models\UserTemporaryPermission;
use App\Repositories\RoleRepository;
use App\Repositories\UserDiscountRepository;
use App\Repositories\UserRepository;
use App\Repositories\UserTemporaryPermissionRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserAdminService
{
    public function __construct(
        private readonly UserRepository $users,
        private readonly RoleRepository $roles,
        private readonly UserTemporaryPermissionRepository $temporaryPermissions,
        private readonly UserDiscountRepository $discounts,
    ) {
    }

    public function paginateForAdmin(array $filters): LengthAwarePaginator
    {
        return $this->users->paginateForAdmin($filters);
    }

    public function create(array $payload, User $actingUser): User
    {
        return DB::transaction(function () use ($payload): User {
            $roles = $payload['roles'] ?? [];
            $user = $this->users->create($this->userPayload($payload, true));

            $this->syncRoles($user, $roles);

            return $user->refresh();
        });
    }

    public function update(User $user, array $payload, User $actingUser): User
    {
        return DB::transaction(function () use ($user, $payload): User {
            $roles = $payload['roles'] ?? null;

            $this->users->update($user, $this->userPayload($payload, false));

            if (\is_array($roles)) {
                $this->syncRoles($user, $roles);
            }

            return $user->refresh();
        });
    }

    public function deactivate(User $user): User
    {
        return DB::transaction(fn (): User => $this->users->update($user, [
            'status' => User::STATUS_INACTIVE,
        ]));
    }

    public function createTemporaryPermission(User $user, array $payload, User $actingUser): UserTemporaryPermission
    {
        return DB::transaction(fn (): UserTemporaryPermission => $this->temporaryPermissions->create($user, [
            'permission_name' => $payload['permission_name'],
            'starts_at' => $payload['starts_at'] ?? null,
            'expires_at' => $payload['expires_at'] ?? null,
            'reason' => $payload['reason'] ?? null,
            'granted_by' => $actingUser->id,
        ]));
    }

    public function revokeTemporaryPermission(UserTemporaryPermission $temporaryPermission): UserTemporaryPermission
    {
        return DB::transaction(fn (): UserTemporaryPermission => $this->temporaryPermissions->revoke($temporaryPermission));
    }

    public function revokeExpiredTemporaryPermissions(): int
    {
        return $this->temporaryPermissions->revokeExpired();
    }

    public function createDiscount(User $user, array $payload, User $actingUser): UserDiscount
    {
        return DB::transaction(fn (): UserDiscount => $this->discounts->create($user, [
            ...$payload,
            'created_by' => $actingUser->id,
        ]));
    }

    public function updateDiscount(UserDiscount $discount, array $payload): UserDiscount
    {
        return DB::transaction(fn (): UserDiscount => $this->discounts->update($discount, $payload));
    }

    public function deactivateDiscount(UserDiscount $discount): UserDiscount
    {
        return DB::transaction(fn (): UserDiscount => $this->discounts->deactivate($discount));
    }

    public function currentTemporaryPermissions(User $user)
    {
        return $this->temporaryPermissions->currentForUser($user);
    }

    public function currentDiscounts(User $user)
    {
        return $this->discounts->currentForUser($user);
    }

    public function roleOptions(): array
    {
        return collect($this->roles->existingRoleNames())
            ->map(fn (string $name): array => ['name' => $name])
            ->values()
            ->all();
    }

    private function userPayload(array $payload, bool $creating): array
    {
        $data = [
            'name' => trim((string) $payload['name']),
            'email' => trim((string) $payload['email']),
            'phone' => $payload['phone'] ?? null,
            'status' => $payload['status'] ?? User::STATUS_ACTIVE,
        ];

        if ($creating || filled($payload['password'] ?? null)) {
            $data['password'] = Hash::make((string) $payload['password']);
        }

        return $data;
    }

    private function syncRoles(User $user, array $roleNames): void
    {
        $existingRoleNames = $this->roles->existingRoleNames();
        $invalid = array_values(array_diff($roleNames, $existingRoleNames));

        if ($invalid !== []) {
            throw ValidationException::withMessages(['roles' => 'Ismeretlen szerepkör szerepel a listában.']);
        }

        $this->users->syncRoles($user, $roleNames);
    }

    public function serializeUser(User $user, bool $canViewOrders): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'status' => $user->status ?? User::STATUS_ACTIVE,
            'roles' => $user->roles->pluck('name')->sort()->values()->all(),
            'orders_count' => (int) ($user->orders_count ?? 0),
            'orders' => $canViewOrders
                ? $user->orders->map(fn (Order $order): array => [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'status' => $order->status,
                    'total' => (float) $order->total,
                    'pickup_date' => $order->pickup_date?->toDateString(),
                    'pickup_time_slot' => $order->pickup_time_slot,
                    'created_at' => $order->created_at?->toDateTimeString(),
                    'show_url' => route('admin.orders.show', $order, false),
                ])->values()->all()
                : [],
            'temporary_permissions' => $user->temporaryPermissions->map(fn (UserTemporaryPermission $permission): array => [
                'id' => $permission->id,
                'permission_name' => $permission->permission_name,
                'starts_at' => $permission->starts_at?->toDateTimeString(),
                'expires_at' => $permission->expires_at?->toDateTimeString(),
                'reason' => $permission->reason,
                'revoked_at' => $permission->revoked_at?->toDateTimeString(),
                'is_active' => $permission->revoked_at === null
                    && ($permission->starts_at === null || $permission->starts_at->lte(now()))
                    && ($permission->expires_at === null || $permission->expires_at->gt(now())),
            ])->values()->all(),
            'discounts' => $user->discounts->map(fn (UserDiscount $discount): array => [
                'id' => $discount->id,
                'type' => $discount->type,
                'value' => (float) $discount->value,
                'starts_at' => $discount->starts_at?->toDateTimeString(),
                'expires_at' => $discount->expires_at?->toDateTimeString(),
                'active' => $discount->active,
                'reason' => $discount->reason,
            ])->values()->all(),
            'created_at' => $user->created_at?->toDateTimeString(),
        ];
    }
}
