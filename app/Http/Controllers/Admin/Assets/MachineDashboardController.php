<?php

namespace App\Http\Controllers\Admin\Assets;

use App\Http\Controllers\Controller;
use App\Models\Assets\MachineProfile;
use App\Services\Assets\MachineDashboardService;
use Illuminate\View\View;

class MachineDashboardController extends Controller
{
    public function __invoke(MachineDashboardService $dashboard): View
    {
        $this->authorize('viewAny', MachineProfile::class);

        $companyId = (int) tenant()->companyId();
        $branchId = tenant()->branchId();

        return view('admin.assets.machines.dashboard', [
            'stats' => $dashboard->build($companyId, $branchId),
        ]);
    }
}
