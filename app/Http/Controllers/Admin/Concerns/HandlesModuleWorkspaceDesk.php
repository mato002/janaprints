<?php

namespace App\Http\Controllers\Admin\Concerns;

use App\Support\Navigation\ModuleShellPresenter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

trait HandlesModuleWorkspaceDesk
{
    protected function renderModuleDesk(
        Request $request,
        string $moduleKey,
        ?string $primaryKey = null,
        ?string $tabKey = null,
        array $extras = [],
    ): View|RedirectResponse {
        $shell = app(ModuleShellPresenter::class);

        if ($primaryKey === null && ! $request->boolean('picker')) {
            $default = $shell->defaultDesk($moduleKey);

            if ($default !== null) {
                return redirect()->to($default['url']);
            }
        }

        $tabKey ??= $this->resolveWorkspaceTab($request);
        $desk = $shell->presentDesk($moduleKey, $primaryKey, is_string($tabKey) ? $tabKey : null, $request);

        abort_if($desk === null, 403);

        return view('admin.workspaces.module-desk', array_merge([
            'shell' => $desk,
            'moduleKey' => $moduleKey,
            'primaryKey' => $primaryKey ?? ($desk['active_primary']['key'] ?? null),
            'tabKey' => is_string($tabKey) ? $tabKey : ($desk['active_secondary']['key'] ?? null),
        ], $extras));
    }

    protected function resolveWorkspaceTab(Request $request): ?string
    {
        $tab = $request->query('tab');

        if (is_array($tab)) {
            $tab = $tab[0] ?? null;
        }

        return is_string($tab) && $tab !== '' ? $tab : null;
    }
}
