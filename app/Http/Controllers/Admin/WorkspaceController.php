<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Navigation\WorkspacePresenter;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WorkspaceController extends Controller
{
    public function show(Request $request, string $workspace, WorkspacePresenter $presenter): View
    {
        abort_unless($presenter->exists($workspace), 404);
        abort_unless($presenter->isVisible($workspace), 403);

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
}
