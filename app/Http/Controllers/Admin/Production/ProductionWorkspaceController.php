<?php

namespace App\Http\Controllers\Admin\Production;

use App\Http\Controllers\Admin\Concerns\HandlesModuleWorkspaceDesk;
use App\Http\Controllers\Controller;
use App\Support\Navigation\ProductionWorkspacePresenter;
use App\Support\Production\ProductionOperatorMode;
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

        if (ProductionOperatorMode::enabledFor($request->user()) && ! $request->boolean('desk')) {
            return redirect()->to(ProductionOperatorMode::homeUrl());
        }

        return $this->renderModuleDesk($request, 'production');
    }

    public function section(Request $request, string $section): View|\Illuminate\Http\RedirectResponse
    {
        abort_unless($this->presenter->sectionExists($section), 404);

        if (ProductionOperatorMode::enabledFor($request->user()) && ! $request->boolean('desk')) {
            return redirect()->to(ProductionOperatorMode::homeUrl());
        }

        return $this->renderModuleDesk($request, 'production', $section);
    }
}
