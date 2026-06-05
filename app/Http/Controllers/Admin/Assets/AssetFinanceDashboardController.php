<?php

namespace App\Http\Controllers\Admin\Assets;

use App\Http\Controllers\Controller;
use App\Models\Assets\DepreciationRun;
use App\Services\Assets\AssetFinanceDashboardService;
use Illuminate\View\View;

class AssetFinanceDashboardController extends Controller
{
    public function __construct(
        protected AssetFinanceDashboardService $dashboard,
    ) {}

    public function __invoke(): View
    {
        $this->authorize('viewAny', DepreciationRun::class);

        return view('admin.assets.finance.dashboard', [
            'stats' => $this->dashboard->build(
                (int) tenant()->companyId(),
                tenant()->branchId(),
            ),
        ]);
    }
}
