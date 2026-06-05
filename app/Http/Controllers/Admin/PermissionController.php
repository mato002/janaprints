<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\AccessControl\PermissionCatalog;
use App\Support\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionController extends Controller
{
    public function __construct(
        protected PermissionCatalog $catalog,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Role::class);

        $roles = Role::query()
            ->where('guard_name', 'web')
            ->orderBy('name')
            ->get(['id', 'name']);

        $selectedRole = null;
        $workspace = null;
        $roleSummary = null;

        if ($request->filled('role')) {
            $selectedRole = Role::query()
                ->where('guard_name', 'web')
                ->where('name', $request->input('role'))
                ->firstOrFail();
            $assigned = $selectedRole->permissions->pluck('name')->all();
            $workspace = $this->catalog->workspacePayload($assigned);
            $roleSummary = $this->catalog->roleSummaryStats($assigned);
        }

        return view('admin.permissions.matrix', [
            'roles' => $roles,
            'selectedRole' => $selectedRole,
            'workspace' => $workspace,
            'roleSummary' => $roleSummary ?? null,
        ]);
    }

    public function edit(Role $role): RedirectResponse
    {
        $this->authorize('assignPermissions', $role);

        return redirect()->route('admin.roles.show', $role);
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        $this->authorize('assignPermissions', $role);

        $validated = $request->validate([
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        $beforePermissions = $role->permissions->pluck('name')->sort()->values()->all();

        $role->syncPermissions($validated['permissions'] ?? []);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $afterPermissions = collect($validated['permissions'] ?? [])->sort()->values()->all();

        ActivityLogger::log('permission_assignment', $role, null, [
            'permissions' => $afterPermissions,
        ], [
            'permissions' => $beforePermissions,
        ]);

        return redirect()
            ->route('admin.roles.show', $role)
            ->with('status', __('Role access rights updated.'));
    }
}
