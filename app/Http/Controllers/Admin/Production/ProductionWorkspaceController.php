<?php

namespace App\Http\Controllers\Admin\Production;

use App\Http\Controllers\Admin\Concerns\HandlesModuleWorkspaceDesk;
use App\Http\Controllers\Controller;
use App\Support\Navigation\ProductionWorkspacePresenter;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductionWorkspaceController extends Controller
{
    use HandlesModuleWorkspaceDesk;

    public function __construct(
        protected ProductionWorkspacePresenter $presenter,
    ) {}

    public function hub(Request $request): View|\Illuminate\Http\RedirectResponse
    {
        abort_unless($this->presenter->isVisible(), 403);

        return $this->renderModuleDesk($request, 'production');
    }

    public function section(Request $request, string $section): View|\Illuminate\Http\RedirectResponse
    {
        abort_unless($this->presenter->sectionExists($section), 404);

        return $this->renderModuleDesk($request, 'production', $section);
    }
}
