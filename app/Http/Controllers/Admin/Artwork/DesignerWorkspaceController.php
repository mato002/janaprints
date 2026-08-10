<?php

namespace App\Http\Controllers\Admin\Artwork;

use App\Http\Controllers\Admin\Concerns\HandlesModuleWorkspaceDesk;
use App\Http\Controllers\Controller;
use App\Support\Navigation\DesignerWorkspacePresenter;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DesignerWorkspaceController extends Controller
{
    use HandlesModuleWorkspaceDesk;

    public function __construct(
        protected DesignerWorkspacePresenter $presenter,
    ) {}

    public function hub(Request $request): View|\Illuminate\Http\RedirectResponse
    {
        abort_unless($this->presenter->isVisible(), 403);

        return $this->renderModuleDesk($request, 'designer');
    }

    public function section(Request $request, string $section): View|\Illuminate\Http\RedirectResponse
    {
        abort_unless($this->presenter->sectionExists($section), 404);

        return $this->renderModuleDesk($request, 'designer', $section);
    }
}
