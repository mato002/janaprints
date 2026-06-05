<?php

namespace App\Http\Controllers\Admin\Assets;

use App\Http\Controllers\Controller;
use App\Models\Assets\MaintenanceWorkOrder;
use App\Services\Assets\MaintenanceDashboardService;
use Illuminate\View\View;

class MaintenanceDashboardController extends Controller
{
    public function __invoke(MaintenanceDashboardService $dashboard): View
    {
        $this->authorize('viewAny', MaintenanceWorkOrder::class);

        return view('admin.assets.maintenance.dashboard', [
            'stats' => $dashboard->build((int) tenant()->companyId(), tenant()->branchId()),
        ]);
    }
}
