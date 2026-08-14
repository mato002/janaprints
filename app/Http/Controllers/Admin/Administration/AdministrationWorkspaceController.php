<?php

namespace App\Http\Controllers\Admin\Administration;

use App\Http\Controllers\Admin\Concerns\HandlesModuleWorkspaceDesk;
use App\Http\Controllers\Controller;
use App\Support\Navigation\AdministrationWorkspacePresenter;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdministrationWorkspaceController extends Controller
{
    use HandlesModuleWorkspaceDesk;

    public function __construct(
        protected AdministrationWorkspacePresenter $presenter,
    ) {}

    public function hub(Request $request): View|\Illuminate\Http\RedirectResponse
    {
        abort_unless($this->presenter->isVisible(), 403);

        return $this->renderModuleDesk($request, 'administration');
    }

    public function section(Request $request, string $section): View|\Illuminate\Http\RedirectResponse
    {
        abort_unless($this->presenter->sectionExists($section), 404);

        $resolved = $this->presenter->resolveSectionKey($section);

        if ($resolved !== $section) {
            return redirect()->route('admin.workspaces.administration.section', [
                'section' => $resolved,
                ...$request->query(),
            ]);
        }

        $extras = in_array($resolved, ['website', 'website-content'], true)
            ? ['showWebsiteCmsSupport' => true]
            : [];

        return $this->renderModuleDesk($request, 'administration', $resolved, null, $extras);
    }

    public function catalog(Request $request, string $section): View
    {
        abort_unless($this->presenter->sectionExists($section), 404);

        $resolved = $this->presenter->resolveSectionKey($section);
        $workspace = $this->presenter->presentSection($resolved);

        abort_unless($workspace !== null, 403);

        foreach ($workspace['groups'] as $groupIndex => $group) {
            foreach ($group['items'] as $itemIndex => $item) {
                if (! empty($item['comingSoon'])) {
                    continue;
                }

                $tabKey = $item['key'] ?? \Illuminate\Support\Str::slug((string) ($item['label'] ?? 'item'));
                $workspace['groups'][$groupIndex]['items'][$itemIndex]['href'] = route(
                    'admin.workspaces.administration.section',
                    ['section' => $resolved, 'tab' => $tabKey],
                );
            }
        }

        $cards = collect($workspace['groups'])
            ->flatMap(fn (array $group) => collect($group['items'])->map(fn (array $item) => array_merge($item, [
                'group_label' => $group['label'],
            ])))
            ->values()
            ->all();

        return view('admin.administration.workspaces.section', [
            'workspace' => $workspace,
            'cards' => $cards,
        ]);
    }
}
