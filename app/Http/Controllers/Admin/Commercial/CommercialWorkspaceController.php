<?php

namespace App\Http\Controllers\Admin\Commercial;

use App\Http\Controllers\Admin\Concerns\HandlesModuleWorkspaceDesk;
use App\Http\Controllers\Controller;
use App\Support\Artwork\DesignerOperatorMode;
use App\Support\Commercial\PublicLeadsDashboardPresenter;
use App\Support\Navigation\CommercialWorkspacePresenter;
use App\Support\Sales\SalesOperatorMode;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CommercialWorkspaceController extends Controller
{
    use HandlesModuleWorkspaceDesk;

    public function __construct(
        protected CommercialWorkspacePresenter $presenter,
        protected PublicLeadsDashboardPresenter $publicLeads,
    ) {}

    public function hub(Request $request): View|\Illuminate\Http\RedirectResponse
    {
        abort_unless($this->presenter->isVisible(), 403);

        if ($redirect = $this->operatorDeskRedirect($request)) {
            return $redirect;
        }

        return $this->renderModuleDesk($request, 'commercial');
    }

    public function section(Request $request, string $section): View|\Illuminate\Http\RedirectResponse
    {
        abort_unless($this->presenter->sectionExists($section), 404);

        if ($redirect = $this->operatorDeskRedirect($request)) {
            return $redirect;
        }

        return $this->renderModuleDesk($request, 'commercial', $section);
    }

    protected function operatorDeskRedirect(Request $request): ?\Illuminate\Http\RedirectResponse
    {
        // Designers never use the Commercial multi-tab shell — even with ?desk=1.
        if (DesignerOperatorMode::enabledFor($request->user())) {
            return redirect()->to(DesignerOperatorMode::homeUrl());
        }

        if ($request->boolean('desk')) {
            return null;
        }

        if (SalesOperatorMode::enabledFor($request->user())) {
            return redirect()->to(SalesOperatorMode::homeUrl());
        }

        return null;
    }
}
