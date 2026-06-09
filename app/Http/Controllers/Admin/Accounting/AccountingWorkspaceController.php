<?php

namespace App\Http\Controllers\Admin\Accounting;

use App\Http\Controllers\Admin\Accounting\Concerns\ResolvesAccountingTenant;
use App\Http\Controllers\Admin\Concerns\HandlesModuleWorkspaceDesk;
use App\Http\Controllers\Controller;
use App\Support\Navigation\AccountingWorkspacePresenter;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccountingWorkspaceController extends Controller
{
    use HandlesModuleWorkspaceDesk;
    use ResolvesAccountingTenant;

    public function __construct(
        protected AccountingWorkspacePresenter $presenter,
    ) {}

    public function hub(Request $request): View|\Illuminate\Http\RedirectResponse
    {
        abort_unless($this->presenter->isVisible(), 403);

        return $this->renderModuleDesk($request, 'accounting');
    }

    public function section(Request $request, string $section): View|\Illuminate\Http\RedirectResponse
    {
        abort_unless($this->presenter->sectionExists($section), 404);

        return $this->renderModuleDesk($request, 'accounting', $section);
    }
}
