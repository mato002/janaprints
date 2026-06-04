<?php

namespace App\Http\Controllers\Admin\Production;

use App\Http\Controllers\Controller;
use App\Models\Production\ProductionJobCard;
use App\Services\Production\JobProfitabilityDashboardService;
use App\Support\Production\JobCostingService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class JobCostingController extends Controller
{
    public function dashboard(Request $request, JobProfitabilityDashboardService $dashboardService): View
    {
        abort_unless(auth()->user()?->can('production.costing.view'), 403);

        $dashboard = $dashboardService->build($request);

        return view('admin.production.costing.dashboard', [
            'dashboard' => $dashboard,
            'filters' => $dashboard['filters'],
            'filterOptions' => $dashboard['filter_options'],
            'activeChips' => $dashboard['active_filter_chips'],
        ]);
    }

    public function show(ProductionJobCard $jobCard): View
    {
        abort_unless(auth()->user()?->can('production.costing.view'), 403);

        $this->authorizeJobTenant($jobCard);

        $costSheet = JobCostingService::buildOrRefresh($jobCard);

        return view('admin.production.costing.show', compact('jobCard', 'costSheet'));
    }

    private function authorizeJobTenant(ProductionJobCard $jobCard): void
    {
        if (tenant()->companyId() && $jobCard->company_id !== tenant()->companyId()) {
            abort(403);
        }
    }
}
