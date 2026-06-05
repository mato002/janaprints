<?php

namespace App\Http\Controllers\Admin\Assets;

use App\Http\Controllers\Controller;
use App\Models\Assets\AssetHandover;
use App\Services\Assets\AssetCustodyDashboardService;
use Illuminate\View\View;

class AssetCustodyDashboardController extends Controller
{
    public function __construct(
        protected AssetCustodyDashboardService $dashboard,
    ) {}

    public function __invoke(): View
    {
        $this->authorize('viewAny', AssetHandover::class);

        return view('admin.assets.custody.dashboard', [
            'stats' => $this->dashboard->build(
                (int) tenant()->companyId(),
                tenant()->branchId(),
            ),
        ]);
    }
}
