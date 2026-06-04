<?php

namespace App\Http\Controllers\Admin\Production;

use App\Http\Controllers\Controller;
use App\Models\Production\ProductionJobCard;
use App\Services\Production\ProductionDashboardCommandCenterService;
use Illuminate\View\View;

class ProductionDashboardController extends Controller
{
    public function __invoke(ProductionDashboardCommandCenterService $dashboard): View
    {
        $this->authorize('viewAny', ProductionJobCard::class);

        return view('admin.production.dashboard', [
            'dashboard' => $dashboard->build(),
        ]);
    }
}
