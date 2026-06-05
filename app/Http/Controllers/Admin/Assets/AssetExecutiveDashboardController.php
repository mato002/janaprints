<?php

namespace App\Http\Controllers\Admin\Assets;

use App\Http\Controllers\Controller;
use App\Services\Assets\AssetExecutiveIntelligenceService;
use Illuminate\View\View;

class AssetExecutiveDashboardController extends Controller
{
    public function __construct(
        protected AssetExecutiveIntelligenceService $dashboard,
    ) {}

    public function __invoke(): View
    {
        abort_unless(auth()->user()?->can('assets.analytics.view'), 403);

        return view('admin.assets.intelligence.executive', [
            'stats' => $this->dashboard->build(
                (int) tenant()->companyId(),
                tenant()->branchId(),
            ),
        ]);
    }
}
