<?php

namespace App\Support\AccessControl;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Role;

class RoleGovernancePresenter
{
    public function __construct(
        protected PermissionCatalog $catalog,
        protected RoleDeactivationRegistry $deactivations,
    ) {}

    /**
     * @return array{
     *     total_roles: int,
     *     active: int,
     *     draft: int,
     *     broken: int,
     *     unused: int,
     *     deactivated: int,
     *     assigned_users: int
     * }
     */
    public function governancePanel(): array
    {
        $roles = Role::query()
            ->where('guard_name', 'web')
            ->withCount(['permissions', 'users'])
            ->get();

        $counts = [
            'active' => 0,
            'draft' => 0,
            'broken' => 0,
            'unused' => 0,
            'deactivated' => 0,
        ];

        foreach ($roles as $role) {
            $status = $this->healthFor($role)['status'];

            if (isset($counts[$status])) {
                $counts[$status]++;
            }
        }

        return [
            'total_roles' => $roles->count(),
            'active' => $counts['active'],
            'draft' => $counts['draft'],
            'broken' => $counts['broken'],
            'unused' => $counts['unused'],
            'deactivated' => $counts['deactivated'],
            'assigned_users' => User::query()
                ->whereHas('roles', fn ($query) => $query->where('guard_name', 'web'))
                ->count(),
        ];
    }

    /**
     * @return array{
     *     most_used: array{name: string, users_count: int}|null,
     *     least_used: array{name: string, users_count: int}|null,
     *     roles_without_users: int,
     *     roles_without_permissions: int,
     *     broken_roles: int,
     *     draft_roles: int
     * }
     */
    public function governanceInsights(): array
    {
        $roles = Role::query()
            ->where('guard_name', 'web')
            ->withCount(['users', 'permissions'])
            ->orderBy('name')
            ->get(['id', 'name', 'updated_at']);

        if ($roles->isEmpty()) {
            return [
                'most_used' => null,
                'least_used' => null,
                'roles_without_users' => 0,
                'roles_without_permissions' => 0,
                'broken_roles' => 0,
                'draft_roles' => 0,
            ];
        }

        $mostUsed = $roles->sortByDesc('users_count')->first();
        $usedRoles = $roles->where('users_count', '>', 0);
        $leastUsed = ($usedRoles->isNotEmpty() ? $usedRoles : $roles)->sortBy('users_count')->first();

        $brokenRoles = 0;
        $draftRoles = 0;
        $rolesWithoutUsers = 0;
        $rolesWithoutPermissions = 0;

        foreach ($roles as $role) {
            $health = $this->healthFor($role);

            if ($health['status'] === 'broken') {
                $brokenRoles++;
            }

            if ($health['status'] === 'draft') {
                $draftRoles++;
            }

            if ($role->users_count === 0 && $health['status'] !== 'deactivated') {
                $rolesWithoutUsers++;
            }

            if ($role->permissions_count === 0 && $health['status'] !== 'deactivated') {
                $rolesWithoutPermissions++;
            }
        }

        return [
            'most_used' => [
                'name' => $mostUsed->name,
                'users_count' => $mostUsed->users_count,
            ],
            'least_used' => [
                'name' => $leastUsed->name,
                'users_count' => $leastUsed->users_count,
            ],
            'roles_without_users' => $rolesWithoutUsers,
            'roles_without_permissions' => $rolesWithoutPermissions,
            'broken_roles' => $brokenRoles,
            'draft_roles' => $draftRoles,
        ];
    }

    /**
     * @param  list<int>  $roleIds
     * @return Collection<int, Collection<int, array{id: int, name: string, email: string, edit_url: string|null}>>
     */
    public function usersGroupedByRole(array $roleIds): Collection
    {
        if ($roleIds === []) {
            return collect();
        }

        $grouped = collect();

        User::query()
            ->whereHas('roles', fn ($query) => $query->whereIn('roles.id', $roleIds)->where('guard_name', 'web'))
            ->with(['roles' => fn ($query) => $query->whereIn('roles.id', $roleIds)->where('guard_name', 'web')])
            ->orderBy('name')
            ->get()
            ->each(function (User $user) use (&$grouped, $roleIds) {
                foreach ($user->roles as $role) {
                    if (! in_array($role->id, $roleIds, true)) {
                        continue;
                    }

                    $entry = [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'edit_url' => auth()->user()?->can('update', $user)
                            ? route('admin.users.edit', $user)
                            : null,
                    ];

                    $grouped[$role->id] = ($grouped[$role->id] ?? collect())->push($entry);
                }
            });

        return $grouped;
    }

    /**
     * @return array<string, mixed>
     */
    public function profile(Role $role, Collection $users): array
    {
        $assigned = $role->permissions->pluck('name')->all();
        $summary = $this->catalog->roleSummaryStats($assigned);
        $category = $this->categoryFor($role->name);
        $health = $this->healthFor($role);
        $moduleCoverage = $this->catalog->moduleCoverage($assigned);
        $moduleLabels = $this->catalog->enabledModuleLabels($assigned);
        $userList = $users->values()->all();

        $searchText = collect([
            $role->name,
            $category['label'],
            (string) $role->users_count,
            (string) $role->permissions_count,
            $health['label'],
            ...$moduleLabels,
            ...collect($userList)->pluck('name'),
            ...collect($userList)->pluck('email'),
        ])->filter()->implode(' ');

        return [
            'id' => $role->id,
            'name' => $role->name,
            'category' => $category,
            'health' => $health,
            'modules_enabled' => $summary['modules_enabled'],
            'permissions_enabled' => $summary['permissions_enabled'],
            'module_labels' => $moduleLabels,
            'modules_display' => $moduleLabels !== []
                ? implode(' • ', $moduleLabels)
                : __('None'),
            'module_coverage' => $moduleCoverage,
            'users_count' => $role->users_count,
            'permissions_count' => $role->permissions_count,
            'users' => $userList,
            'search_text' => $searchText,
            'show_url' => route('admin.roles.show', $role),
            'edit_url' => auth()->user()?->can('update', $role) ? route('admin.roles.edit', $role) : null,
            'can_clone' => auth()->user()?->can('create', Role::class) ?? false,
            'can_deactivate' => auth()->user()?->can('delete', $role) ?? false,
            'deactivate_blocked' => $role->users_count > 0,
            'is_deactivated' => $health['status'] === 'deactivated',
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function profilesForPage(LengthAwarePaginator $roles, Collection $usersByRole): array
    {
        return $roles->getCollection()
            ->map(fn (Role $role) => $this->profile($role, $usersByRole->get($role->id, collect())))
            ->values()
            ->all();
    }

    /**
     * @return array{key: string, label: string, tone: string}
     */
    public function categoryFor(string $roleName): array
    {
        $key = config("role_catalog.assignments.{$roleName}");

        if (! $key) {
            $key = 'general';
        }

        $meta = config("role_catalog.categories.{$key}", config('role_catalog.categories.general'));

        return [
            'key' => $key,
            'label' => __($meta['label']),
            'tone' => $meta['tone'],
        ];
    }

    /**
     * @return array{status: string, label: string, tone: string}
     */
    public function healthFor(Role $role): array
    {
        if ($this->deactivations->isDeactivated($role->id)) {
            return [
                'status' => 'deactivated',
                'label' => __('Deactivated'),
                'tone' => 'inactive',
            ];
        }

        $users = (int) $role->users_count;
        $permissions = (int) $role->permissions_count;

        if ($users > 0 && $permissions > 0) {
            return [
                'status' => 'active',
                'label' => __('Active'),
                'tone' => 'success',
            ];
        }

        if ($users === 0 && $permissions > 0) {
            return [
                'status' => 'draft',
                'label' => __('Draft'),
                'tone' => 'info',
            ];
        }

        if ($users === 0 && $permissions === 0) {
            return [
                'status' => 'unused',
                'label' => __('Unused'),
                'tone' => 'neutral',
            ];
        }

        return [
            'status' => 'broken',
            'label' => __('Broken'),
            'tone' => 'danger',
        ];
    }
}
