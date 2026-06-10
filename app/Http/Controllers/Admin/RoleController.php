<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\ExportsTabularIndex;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\AccessControl\PermissionCatalog;
use App\Support\AccessControl\RoleDeactivationRegistry;
use App\Support\AccessControl\RoleGovernancePresenter;
use App\Support\ActivityLogger;
use App\Support\Export\TabularExportWriter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RoleController extends Controller
{
    use ExportsTabularIndex;
    public function __construct(
        protected PermissionCatalog $catalog,
        protected RoleGovernancePresenter $governance,
        protected RoleDeactivationRegistry $deactivations,
    ) {}

    public function index(): View
    {
        $this->authorize('viewAny', Role::class);

        $roles = Role::query()
            ->where('guard_name', 'web')
            ->with(['permissions:id,name'])
            ->withCount(['permissions', 'users'])
            ->orderBy('name')
            ->paginate(config('platform.pagination.admin', 20));

        $usersByRole = $this->governance->usersGroupedByRole($roles->pluck('id')->all());

        return view('admin.roles.index', [
            'roles' => $roles,
            'panel' => $this->governance->governancePanel(),
            'insights' => $this->governance->governanceInsights(),
            'profiles' => $this->governance->profilesForPage($roles, $usersByRole),
        ]);
    }

    public function export(Request $request, string $format, TabularExportWriter $writer): StreamedResponse
    {
        $this->authorize('viewAny', Role::class);

        $roles = Role::query()
            ->where('guard_name', 'web')
            ->withCount(['permissions', 'users'])
            ->orderBy('name')
            ->get();

        $usersByRole = $this->governance->usersGroupedByRole($roles->pluck('id')->all());

        $headers = [__('Role'), __('Category'), __('Users assigned'), __('Permissions'), __('Modules'), __('Status')];
        $rows = $roles->map(function (Role $role) use ($usersByRole) {
            $profile = $this->governance->profile($role, $usersByRole->get($role->id, collect()));

            return [
                $profile['name'],
                $profile['category']['label'],
                (string) $profile['users_count'],
                (string) $profile['permissions_count'],
                $profile['modules_display'],
                $profile['health']['label'],
            ];
        })->all();

        return $this->downloadTabularExport($writer, $format, 'roles', $headers, $rows, __('Roles'));
    }

    public function create(): View
    {
        $this->authorize('create', Role::class);

        $cloneOptions = Role::query()
            ->where('guard_name', 'web')
            ->withCount('permissions')
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('admin.roles.create', compact('cloneOptions'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Role::class);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('roles', 'name')->where('guard_name', 'web')],
            'clone_from' => ['nullable', 'integer', Rule::exists('roles', 'id')->where('guard_name', 'web')],
        ]);

        $role = Role::create(['name' => $validated['name'], 'guard_name' => 'web']);

        if (! empty($validated['clone_from'])) {
            $source = Role::query()
                ->where('guard_name', 'web')
                ->with('permissions')
                ->findOrFail($validated['clone_from']);

            $role->syncPermissions($source->permissions->pluck('name'));
            ActivityLogger::log('created', $role, null, ['cloned_from' => $source->id]);

            return redirect()
                ->route('admin.roles.show', $role)
                ->with('status', __('Role created with permissions cloned from :source.', ['source' => $source->name]));
        }

        ActivityLogger::log('created', $role);

        return redirect()
            ->route('admin.roles.show', $role)
            ->with('status', __('Role created. Assign access rights below.'));
    }

    public function show(Role $role): View
    {
        $this->authorize('viewAny', Role::class);

        $role->loadCount(['permissions', 'users']);
        $assigned = $role->permissions->pluck('name')->all();
        $assignedUsers = User::query()
            ->role($role->name)
            ->with(['company', 'defaultBranch'])
            ->orderBy('name')
            ->limit(50)
            ->get();

        return view('admin.roles.show', [
            'role' => $role,
            'assignedUsers' => $assignedUsers,
            'workspace' => $this->catalog->workspacePayload($assigned),
            'roleSummary' => $this->catalog->roleSummaryStats($assigned),
            'canAssignPermissions' => auth()->user()->can('assignPermissions', $role),
            'canUpdate' => auth()->user()->can('update', $role),
        ]);
    }

    public function edit(Role $role): View
    {
        $this->authorize('update', $role);

        return view('admin.roles.edit', compact('role'));
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        $this->authorize('update', $role);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('roles', 'name')->where('guard_name', 'web')->ignore($role->id)],
        ]);

        $role->update($validated);
        ActivityLogger::log('updated', $role);

        return redirect()
            ->route('admin.roles.show', $role)
            ->with('status', __('Role updated.'));
    }

    public function duplicate(Role $role): RedirectResponse
    {
        return $this->cloneRole($role);
    }

    public function deactivate(Role $role): RedirectResponse
    {
        $this->authorize('delete', $role);

        if ($role->users()->count() > 0) {
            return back()->withErrors(['role' => __('Remove all users before deactivating this role.')]);
        }

        $this->deactivations->deactivate($role->id);
        ActivityLogger::log('deactivated', $role);

        return back()->with('status', __('Role deactivated. Permissions are preserved for audit history.'));
    }

    public function reactivate(Role $role): RedirectResponse
    {
        $this->authorize('delete', $role);

        $this->deactivations->reactivate($role->id);
        ActivityLogger::log('reactivated', $role);

        return back()->with('status', __('Role reactivated.'));
    }

    protected function cloneRole(Role $role): RedirectResponse
    {
        $this->authorize('create', Role::class);

        $role->load('permissions');

        $baseName = $role->name.' '.__('Copy');
        $name = $baseName;
        $counter = 2;

        while (Role::query()->where('guard_name', 'web')->where('name', $name)->exists()) {
            $name = $baseName.' '.$counter;
            $counter++;
        }

        $duplicate = Role::create(['name' => $name, 'guard_name' => 'web']);
        $duplicate->syncPermissions($role->permissions->pluck('name'));
        ActivityLogger::log('created', $duplicate, null, ['cloned_from' => $role->id]);

        return redirect()
            ->route('admin.roles.show', $duplicate)
            ->with('status', __('Role cloned. Review access rights before assigning users.'));
    }

    public function destroy(Role $role): RedirectResponse
    {
        $this->authorize('delete', $role);

        if ($role->users()->count() > 0) {
            return back()->withErrors(['role' => __('Cannot delete a role that is assigned to users.')]);
        }

        ActivityLogger::log('deleted', $role);
        $role->delete();

        return redirect()
            ->route('admin.access-control.roles')
            ->with('status', __('Role deleted.'));
    }
}
