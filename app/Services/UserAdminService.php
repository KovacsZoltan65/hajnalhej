<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\RoleRepository;
use App\Repositories\UserRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserAdminService
{
    public function __construct(
        private readonly UserRepository $users,
        private readonly RoleRepository $roles,
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

    public function serializeUser(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'status' => $user->status ?? User::STATUS_ACTIVE,
            'roles' => $user->roles->pluck('name')->sort()->values()->all(),
            'created_at' => $user->created_at?->toDateTimeString(),
        ];
    }
}
