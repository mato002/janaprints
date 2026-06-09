<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HandlesModuleWorkspaceDesk;
use App\Http\Controllers\Controller;
use App\Support\Navigation\ModuleShellPresenter;
use App\Support\Navigation\WorkspacePresenter;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WorkspaceController extends Controller
{
    use HandlesModuleWorkspaceDesk;

    public function show(Request $request, string $workspace, WorkspacePresenter $presenter): View|\Illuminate\Http\RedirectResponse
    {
        abort_unless($presenter->exists($workspace), 404);
        abort_unless($presenter->isVisible($workspace), 403);

        if (array_key_exists($workspace, app(ModuleShellPresenter::class)->moduleDefinitions())) {
            return $this->renderModuleDesk($request, $workspace);
        }

        $payload = $presenter->present($workspace);

        abort_if($payload === null || $payload['groups'] === [], 403);

        return view('admin.workspaces.show', [
            'workspace' => $payload,
            'cards' => collect($payload['groups'])
                ->flatMap(fn (array $group) => collect($group['items'])->map(fn (array $item) => array_merge($item, [
                    'group_label' => $group['label'],
                    'search_text' => strtolower(implode(' ', array_filter([
                        $group['label'],
                        $item['label'],
                        $item['description'],
                    ]))),
                ])))
                ->values()
                ->all(),
        ]);
    }

    public function section(Request $request, string $workspace, string $section): View|\Illuminate\Http\RedirectResponse
    {
        abort_unless(app(ModuleShellPresenter::class)->moduleDefinitions()[$workspace] ?? false, 404);

        return $this->renderModuleDesk($request, $workspace, $section);
    }
}
