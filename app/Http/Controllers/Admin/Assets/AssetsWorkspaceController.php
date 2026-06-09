<?php

namespace App\Http\Controllers\Admin\Assets;

use App\Http\Controllers\Admin\Concerns\HandlesModuleWorkspaceDesk;
use App\Http\Controllers\Controller;
use App\Support\Navigation\AssetsWorkspacePresenter;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AssetsWorkspaceController extends Controller
{
    use HandlesModuleWorkspaceDesk;

    public function __construct(
        protected AssetsWorkspacePresenter $presenter,
    ) {}

    public function hub(Request $request): View|\Illuminate\Http\RedirectResponse
    {
        abort_unless($this->presenter->isVisible(), 403);

        return $this->renderModuleDesk($request, 'assets');
    }

    public function section(Request $request, string $section): View|\Illuminate\Http\RedirectResponse
    {
        return $this->renderModuleDesk($request, 'assets', $section);
    }
}
