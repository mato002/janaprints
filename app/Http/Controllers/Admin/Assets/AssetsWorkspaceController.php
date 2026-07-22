<?php

namespace App\Http\Controllers\Admin\Assets;

use App\Http\Controllers\Admin\Concerns\HandlesModuleWorkspaceDesk;
use App\Http\Controllers\Controller;
use App\Support\Navigation\AssetsWorkspacePresenter;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AssetsWorkspaceController extends Controller
{
    use HandlesModuleWorkspaceDesk {
        resolveWorkspaceTab as protected resolveWorkspaceTabFromRequest;
    }

    /**
     * @var array<string, string>
     */
    protected array $legacyAssetManagementTabs = [
        'asset-register' => 'asset-management',
        'asset-categories' => 'asset-management',
        'asset-dashboard' => 'asset-management',
        'acquisition-dashboard' => 'acquisitions',
        'capitalization-queue' => 'acquisitions',
        'warranty-center' => 'acquisitions',
        'capitalization-reconciliation' => 'acquisitions',
        'executive-asset-dashboard' => 'intelligence',
        'branch-asset-intelligence' => 'intelligence',
        'asset-analytics-center' => 'intelligence',
        'asset-360' => 'intelligence',
    ];

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

    protected function resolveWorkspaceTab(Request $request): ?string
    {
        $tab = $this->resolveWorkspaceTabFromRequest($request);

        if ($tab === null) {
            return null;
        }

        return $this->legacyAssetManagementTabs[$tab] ?? $tab;
    }
}
