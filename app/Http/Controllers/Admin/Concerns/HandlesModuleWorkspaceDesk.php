<?php

namespace App\Http\Controllers\Admin\Concerns;

use App\Support\Navigation\ModuleShellPresenter;
use App\Support\Navigation\WorkspaceEmbed;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
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
            // ?desk=1 keeps the multi-tab shell (skip open_full operator desks).
            if ($request->boolean('desk')) {
                $default = $shell->defaultShellDesk($moduleKey);

                if ($default !== null) {
                    return redirect()->to($default['url']);
                }
            }

            $default = $shell->defaultDesk($moduleKey);

            if ($default !== null) {
                return redirect()->to($default['url']);
            }
        }

        $tabKey ??= $this->resolveWorkspaceTab($request);
        $desk = $shell->presentDesk($moduleKey, $primaryKey, is_string($tabKey) ? $tabKey : null, $request);

        abort_if($desk === null, 403);

        // open_full tabs (Sales Desk) leave the shell — same as dashboard shortcuts.
        // ?desk=1 is the escape hatch back into the multi-tab Commercial desk.
        if (
            ! $request->boolean('desk')
            && ! WorkspaceEmbed::rendersEmbeddedFragment()
            && ! empty($desk['active_secondary']['open_full'])
            && is_string($desk['active_secondary']['href'] ?? null)
            && $desk['active_secondary']['href'] !== ''
        ) {
            if ($request->hasSession()) {
                $request->session()->reflash();
            }

            return redirect()->to($desk['active_secondary']['href']);
        }

        // Nested #module-workspace-content fetches must never re-render the desk shell —
        // that duplicates tabs/header inside the outer workspace frame.
        if (WorkspaceEmbed::rendersEmbeddedFragment()) {
            $contentUrl = $desk['content_url'] ?? null;

            if (is_string($contentUrl) && $contentUrl !== '') {
                if ($request->hasSession()) {
                    $request->session()->reflash();
                }

                return redirect()->to($contentUrl);
            }
        }

        // When ?desk=1 lands on an open_full tab, fall through to the first embeddable tab.
        if (
            $request->boolean('desk')
            && ! empty($desk['active_secondary']['open_full'])
        ) {
            $shellDefault = $shell->defaultShellDesk($moduleKey);

            if ($shellDefault !== null) {
                return redirect()->to($shellDefault['url']);
            }
        }

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

        return is_string($tab) && $tab !== '' ? Str::slug($tab) : null;
    }
}
