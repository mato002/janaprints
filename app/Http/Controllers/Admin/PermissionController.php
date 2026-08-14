<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\AccessControl\PermissionCatalog;
use App\Support\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionController extends Controller
{
    public function __construct(
        protected PermissionCatalog $catalog,
    ) {}

    public function index(Request $request): RedirectResponse
    {
        $this->authorize('viewAny', Role::class);

        if ($request->filled('role')) {
            $role = Role::query()
                ->where('guard_name', 'web')
                ->where('name', $request->input('role'))
                ->firstOrFail();

            return redirect()->route('admin.roles.show', [
                'role' => $role,
                'tab' => 'modules',
            ]);
        }

        return redirect()->route('admin.access-control.roles');
    }

    public function edit(Role $role): RedirectResponse
    {
        $this->authorize('assignPermissions', $role);

        return redirect()->route('admin.roles.show', [
            'role' => $role,
            'tab' => 'modules',
        ]);
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
            ->route('admin.roles.show', array_filter([
                'role' => $role,
                'tab' => 'modules',
                'module' => $request->query('module') ?: $request->input('_module'),
            ]))
            ->with('status', __('Role access rights updated.'));
    }
}
