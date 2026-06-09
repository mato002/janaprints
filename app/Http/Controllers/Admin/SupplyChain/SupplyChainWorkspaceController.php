<?php

namespace App\Http\Controllers\Admin\SupplyChain;

use App\Http\Controllers\Admin\Concerns\HandlesModuleWorkspaceDesk;
use App\Http\Controllers\Controller;
use App\Support\Navigation\SupplyChainWorkspacePresenter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupplyChainWorkspaceController extends Controller
{
    use HandlesModuleWorkspaceDesk;

    public function __construct(
        protected SupplyChainWorkspacePresenter $presenter,
    ) {}

    public function hub(Request $request): View|RedirectResponse
    {
        abort_unless($this->presenter->isVisible(), 403);

        return $this->renderModuleDesk($request, 'supply-chain');
    }

    public function section(Request $request, string $section): View|RedirectResponse
    {
        if ($section === 'assets') {
            return redirect()->route('admin.workspaces.assets');
        }

        abort_unless($this->presenter->sectionExists($section), 404);

        return $this->renderModuleDesk($request, 'supply-chain', $section);
    }
}
