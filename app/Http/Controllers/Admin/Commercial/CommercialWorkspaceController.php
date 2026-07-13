<?php

namespace App\Http\Controllers\Admin\Commercial;

use App\Http\Controllers\Admin\Concerns\HandlesModuleWorkspaceDesk;
use App\Http\Controllers\Controller;
use App\Services\Commercial\PublicQuoteRequestCountService;
use App\Support\Commercial\PublicLeadsDashboardPresenter;
use App\Support\Navigation\CommercialWorkspacePresenter;
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

        return $this->renderModuleDesk($request, 'commercial');
    }

    public function section(Request $request, string $section): View|\Illuminate\Http\RedirectResponse
    {
        abort_unless($this->presenter->sectionExists($section), 404);

        return $this->renderModuleDesk($request, 'commercial', $section);
    }
}
