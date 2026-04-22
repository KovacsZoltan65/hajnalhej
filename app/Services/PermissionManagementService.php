<?php

namespace App\Services;

use App\Repositories\PermissionRepository;
use App\Services\Audit\PermissionAuditService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class PermissionManagementService
{
    public function __construct(
        private readonly PermissionRepository $permissionRepository,
        private readonly PermissionAuditService $auditService,
    ) {
    }

    /**
     * @param array<string, mixed> $filters
     */
    public function paginateForAdmin(array $filters): LengthAwarePaginator
    {
        $allRows = $this->collectPermissionRows();

        $search = trim((string) ($filters['search'] ?? ''));
        $module = trim((string) ($filters['module'] ?? ''));
        $dangerousOnly = (bool) ($filters['dangerous_only'] ?? false);
        $usageState = trim((string) ($filters['usage_state'] ?? ''));
        $registryState = trim((string) ($filters['registry_state'] ?? ''));
        $sortField = (string) ($filters['sort_field'] ?? 'name');
        $sortDirection = (string) ($filters['sort_direction'] ?? 'asc');
        $perPage = (int) ($filters['per_page'] ?? 20);
        $page = max(1, (int) ($filters['page'] ?? 1));

        $filtered = $allRows
            ->when($search !== '', function (Collection $collection) use ($search): Collection {
                $needle = mb_strtolower($search);

                return $collection->filter(function (array $row) use ($needle): bool {
                    $haystack = mb_strtolower(implode(' ', [
                        $row['name'],
                        $row['label'] ?? '',
                        $row['description'] ?? '',
                        $row['module'] ?? '',
                    ]));

                    return str_contains($haystack, $needle);
                })->values();
            })
            ->when($module !== '', fn (Collection $collection): Collection => $collection->where('module', $module)->values())
            ->when($dangerousOnly, fn (Collection $collection): Collection => $collection->where('dangerous', true)->values())
            ->when($usageState !== '', function (Collection $collection) use ($usageState): Collection {
                if ($usageState === 'used') {
                    return $collection->where('roles_count', '>', 0)->values();
                }

                if ($usageState === 'unused') {
                    return $collection->where('roles_count', 0)->values();
                }

                return $collection;
            })
            ->when($registryState !== '', fn (Collection $collection): Collection => $collection->where('registry_state', $registryState)->values());

        $direction = $sortDirection === 'desc' ? 'desc' : 'asc';
        $allowedSortFields = ['name', 'module', 'roles_count', 'users_count', 'registry_state'];
        if (! in_array($sortField, $allowedSortFields, true)) {
            $sortField = 'name';
        }

        $sorted = $filtered->sortBy($sortField, SORT_NATURAL | SORT_FLAG_CASE, $direction === 'desc')->values();
        $total = $sorted->count();
        $results = $sorted->forPage($page, $perPage)->values();

        return new LengthAwarePaginator(
            items: $results,
            total: $total,
            perPage: $perPage,
            currentPage: $page,
            options: [
                'path' => request()->url(),
                'query' => request()->query(),
            ],
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function detail(string $permissionName): array
    {
        $definitions = $this->permissionRepository->definitionsByName();
        $definition = $definitions[$permissionName] ?? null;
        $dbPermission = $this->permissionRepository->findByName($permissionName);
        $roleNames = $this->permissionRepository->roleNamesForPermission($permissionName);
        $usersUsageMap = $this->permissionRepository->userUsageCountByPermissionNames([$permissionName]);

        return [
            'name' => $permissionName,
            'guard_name' => $dbPermission?->guard_name ?? 'web',
            'label' => $definition['label'] ?? $permissionName,
            'description' => $definition['description'] ?? 'Registry definicio nem talalhato.',
            'module' => $definition['module'] ?? 'Orphan / Custom',
            'dangerous' => (bool) ($definition['dangerous'] ?? false),
            'system' => (bool) ($definition['system'] ?? false),
            'audit_sensitive' => (bool) ($definition['audit_sensitive'] ?? false),
            'registry_state' => $definition !== null
                ? ($dbPermission instanceof Permission ? 'synced' : 'missing_in_db')
                : 'orphan_db_only',
            'role_names' => $roleNames,
            'roles_count' => count($roleNames),
            'users_count' => (int) ($usersUsageMap[$permissionName] ?? 0),
            'is_registry_defined' => $definition !== null,
            'exists_in_database' => $dbPermission instanceof Permission,
            'registry_meta' => $definition,
        ];
    }

    /**
     * @return array<string, array<int, string>|int|bool>
     */
    public function syncFromRegistry(User $actor, bool $dryRun = false): array
    {
        $registryNames = $this->permissionRepository->allowedPermissionNames();
        $missingNames = $this->permissionRepository->missingNamesFromDatabase($registryNames);
        $orphanNames = $this->permissionRepository->orphanNames($registryNames);

        $created = [];
        if (! $dryRun && $missingNames !== []) {
            $created = $this->permissionRepository->createMissing($missingNames);
        }

        $existing = array_values(array_diff($registryNames, $missingNames));
        sort($existing);

        $summary = [
            'created_permissions' => $dryRun ? $missingNames : $created,
            'existing_permissions' => $existing,
            'orphan_permissions' => $orphanNames,
            'created_count' => count($dryRun ? $missingNames : $created),
            'existing_count' => count($existing),
            'orphan_count' => count($orphanNames),
            'dry_run' => $dryRun,
        ];

        if (! $dryRun) {
            $this->auditService->logPermissionsSynced(
                actor: $actor,
                createdPermissions: $summary['created_permissions'],
                existingPermissions: $summary['existing_permissions'],
                orphanPermissions: $summary['orphan_permissions'],
                context: ['operation' => 'permissions.registry.sync'],
            );
        }

        return $summary;
    }

    /**
     * @return array<int, string>
     */
    public function modules(): array
    {
        return $this->collectPermissionRows()
            ->pluck('module')
            ->filter(fn (mixed $module): bool => is_string($module) && $module !== '')
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function collectPermissionRows(): Collection
    {
        $definitions = $this->permissionRepository->definitionsByName();
        $dbPermissions = $this->permissionRepository->allWithUsage()->keyBy('name');
        $usageCounts = $this->permissionRepository->userUsageCountByPermissionNames($dbPermissions->keys()->all());

        $rows = collect();

        foreach ($definitions as $name => $definition) {
            /** @var Permission|null $permission */
            $permission = $dbPermissions->get($name);
            $roleNames = $permission?->roles?->pluck('name')->sort()->values()->all() ?? [];

            $rows->push([
                'name' => $name,
                'label' => $definition['label'],
                'description' => $definition['description'],
                'module' => $definition['module'],
                'dangerous' => (bool) $definition['dangerous'],
                'system' => (bool) $definition['system'],
                'audit_sensitive' => (bool) $definition['audit_sensitive'],
                'guard_name' => $permission?->guard_name ?? 'web',
                'is_registry_defined' => true,
                'exists_in_database' => $permission instanceof Permission,
                'registry_state' => $permission instanceof Permission ? 'synced' : 'missing_in_db',
                'roles_count' => count($roleNames),
                'users_count' => (int) ($usageCounts[$name] ?? 0),
                'role_names' => $roleNames,
                'sort' => (int) $definition['sort'],
            ]);
        }

        foreach ($dbPermissions as $name => $permission) {
            if (isset($definitions[$name])) {
                continue;
            }

            $roleNames = $permission->roles->pluck('name')->sort()->values()->all();

            $rows->push([
                'name' => $name,
                'label' => $name,
                'description' => 'Csak adatbazisban letezo permission (registry drift).',
                'module' => 'Orphan / Custom',
                'dangerous' => false,
                'system' => false,
                'audit_sensitive' => false,
                'guard_name' => $permission->guard_name,
                'is_registry_defined' => false,
                'exists_in_database' => true,
                'registry_state' => 'orphan_db_only',
                'roles_count' => count($roleNames),
                'users_count' => (int) ($usageCounts[$name] ?? 0),
                'role_names' => $roleNames,
                'sort' => PHP_INT_MAX,
            ]);
        }

        return $rows;
    }
}
