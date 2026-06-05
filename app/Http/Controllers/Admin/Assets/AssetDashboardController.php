<?php

namespace App\Http\Controllers\Admin\Assets;

use App\Http\Controllers\Controller;
use App\Models\Assets\FixedAsset;
use App\Services\Assets\AssetDashboardService;
use Illuminate\View\View;

class AssetDashboardController extends Controller
{
    public function __construct(
        protected AssetDashboardService $dashboard,
    ) {}

    public function __invoke(): View
    {
        $this->authorize('viewAny', FixedAsset::class);

        $companyId = (int) tenant()->companyId();
        $branchId = tenant()->branchId();

        return view('admin.assets.dashboard', [
            'stats' => $this->dashboard->build($companyId, $branchId),
        ]);
    }
}
