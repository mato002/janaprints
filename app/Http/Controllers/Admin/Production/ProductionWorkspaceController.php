<?php

namespace App\Http\Controllers\Admin\Production;

use App\Http\Controllers\Controller;
use App\Support\Navigation\ProductionWorkspacePresenter;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductionWorkspaceController extends Controller
{
    public function __construct(
        protected ProductionWorkspacePresenter $presenter,
    ) {}

    public function hub(Request $request): View
    {
        $payload = $this->presenter->presentHub();

        abort_if($payload === null, 403);

        $cards = collect($payload['groups'])
            ->flatMap(fn (array $group) => collect($group['items'])->map(fn (array $item) => array_merge($item, [
                'group_label' => $group['label'],
                'search_text' => strtolower(implode(' ', array_filter([
                    $group['label'],
                    $item['label'],
                    $item['description'],
                ]))),
            ])))
            ->values()
            ->all();

        return view('admin.production.workspaces.hub', [
            'workspace' => $payload,
            'cards' => $cards,
        ]);
    }
}
