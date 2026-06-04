<?php

namespace App\Http\Controllers\Admin\Dispatch;

use App\Http\Controllers\Controller;
use App\Models\Dispatch\DeliveryNote;
use App\Services\Dispatch\DispatchDashboardService;
use Illuminate\View\View;

class DispatchDashboardController extends Controller
{
    public function __invoke(DispatchDashboardService $dashboard): View
    {
        $this->authorize('viewAny', DeliveryNote::class);

        $companyId = (int) tenant()->companyId();
        $branchId = tenant()->branchId();

        return view('admin.dispatch.dashboard', [
            'dashboard' => $dashboard->build($companyId, $branchId),
        ]);
    }
}
