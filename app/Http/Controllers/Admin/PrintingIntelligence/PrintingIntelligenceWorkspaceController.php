<?php

namespace App\Http\Controllers\Admin\PrintingIntelligence;

use App\Http\Controllers\Admin\Concerns\HandlesModuleWorkspaceDesk;
use App\Http\Controllers\Controller;
use App\Support\Navigation\PrintingIntelligenceWorkspacePresenter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PrintingIntelligenceWorkspaceController extends Controller
{
    use HandlesModuleWorkspaceDesk;

    public function __construct(
        protected PrintingIntelligenceWorkspacePresenter $presenter,
    ) {}

    public function hub(Request $request): View|RedirectResponse
    {
        abort_unless($this->presenter->isVisible(), 403);

        return $this->renderModuleDesk($request, 'printing-intelligence');
    }

    public function section(Request $request, string $section): View|RedirectResponse
    {
        abort_unless($this->presenter->sectionExists($section), 404);

        return $this->renderModuleDesk($request, 'printing-intelligence', $section);
    }
}
