<?php

namespace App\Http\Controllers\Admin\Assets;

use App\Http\Controllers\Controller;
use App\Services\Assets\AssetAcquisitionDashboardService;
use Illuminate\View\View;

class AssetAcquisitionDashboardController extends Controller
{
    public function __construct(
        protected AssetAcquisitionDashboardService $dashboard,
    ) {}

    public function __invoke(): View
    {
        abort_unless(auth()->user()?->can('assets.acquisition.view'), 403);

        return view('admin.assets.acquisitions.dashboard', [
            'stats' => $this->dashboard->build(
                (int) tenant()->companyId(),
                tenant()->branchId(),
            ),
        ]);
    }
}
