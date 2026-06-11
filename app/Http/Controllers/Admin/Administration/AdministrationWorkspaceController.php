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

        $extras = $section === 'website-content'
            ? ['showWebsiteCmsSupport' => true]
            : [];

        return $this->renderModuleDesk($request, 'administration', $section, null, $extras);
    }
}
