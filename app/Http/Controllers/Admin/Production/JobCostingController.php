<?php

namespace App\Http\Controllers\Admin\Production;

use App\Http\Controllers\Controller;
use App\Models\Production\ProductionJobCard;
use App\Support\Production\JobCostingService;
use App\Support\Production\JobProfitabilityService;
use Illuminate\View\View;

class JobCostingController extends Controller
{
    public function dashboard(): View
    {
        abort_unless(auth()->user()?->can('production.costing.view'), 403);

        $companyId = (int) tenant()->companyId();
        $branchId = tenant()->branchId();

        $stats = JobProfitabilityService::dashboard($companyId, $branchId);

        return view('admin.production.costing.dashboard', compact('stats'));
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
