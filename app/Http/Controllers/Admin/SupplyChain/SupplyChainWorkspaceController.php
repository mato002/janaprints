<?php

namespace App\Http\Controllers\Admin\SupplyChain;

use App\Http\Controllers\Controller;
use App\Support\Navigation\SupplyChainWorkspacePresenter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupplyChainWorkspaceController extends Controller
{
    public function __construct(
        protected SupplyChainWorkspacePresenter $presenter,
    ) {}

    public function hub(Request $request): View
    {
        $payload = $this->presenter->presentHub();

        abort_if($payload === null, 403);

        return view('admin.supply-chain.workspaces.hub', [
            'workspace' => $payload,
            'cards' => collect($payload['items'])->map(fn (array $item) => array_merge($item, [
                'group_label' => __('Workspaces'),
                'search_text' => strtolower(implode(' ', array_filter([
                    __('Workspaces'),
                    $item['label'],
                    $item['description'],
                ]))),
            ]))->all(),
        ]);
    }

    public function section(Request $request, string $section): View|RedirectResponse
    {
        if ($section === 'assets') {
            return redirect()->route('admin.workspaces.assets');
        }

        abort_unless($this->presenter->sectionExists($section), 404);

        $payload = $this->presenter->presentSection($section);

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

        return view('admin.supply-chain.workspaces.section', [
            'workspace' => $payload,
            'section' => $section,
            'cards' => $cards,
        ]);
    }
}
