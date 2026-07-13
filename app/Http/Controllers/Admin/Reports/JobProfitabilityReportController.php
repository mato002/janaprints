<?php

namespace App\Http\Controllers\Admin\Reports;

use App\Http\Controllers\Controller;
use App\Support\Production\CustomerProfitabilityService;
use App\Support\Production\JobProfitabilityService;
use App\Support\Production\ProductProfitabilityService;
use App\Support\Production\SalespersonProfitabilityService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class JobProfitabilityReportController extends Controller
{
    public function index(
        Request $request,
        CustomerProfitabilityService $customers,
        ProductProfitabilityService $products,
        SalespersonProfitabilityService $salespersons,
    ): View {
        abort_unless(auth()->user()?->can('reports.costing.view|reports.view'), 403);

        $companyId = (int) tenant()->companyId();
        $branchId = tenant()->branchId();
        $filters = $request->only(['customer_id', 'production_type', 'branch_id', 'date_from', 'date_to', 'salesperson_id']);

        if (! empty($filters['branch_id'])) {
            $branchId = (int) $filters['branch_id'];
        }

        $baseQueryFilters = array_filter([
            'date_from' => $filters['date_from'] ?? now()->startOfMonth()->toDateString(),
            'date_to' => $filters['date_to'] ?? now()->toDateString(),
            'customer_id' => $filters['customer_id'] ?? null,
            'production_type' => $filters['production_type'] ?? null,
        ]);

        $topProfitable = JobProfitabilityService::topProfitableJobs($companyId, $branchId, 20);
        $leastProfitable = JobProfitabilityService::lossMakingJobs($companyId, $branchId, 20);

        return view('admin.reports.job-profitability.index', [
            'filters' => $filters,
            'topProfitable' => $topProfitable,
            'leastProfitable' => $leastProfitable,
            'customerProfitability' => $customers->ranking($companyId, $branchId, $baseQueryFilters, 20),
            'productProfitability' => $products->ranking($companyId, $branchId, $baseQueryFilters, 20),
            'salespersonProfitability' => $salespersons->ranking($companyId, $branchId, $baseQueryFilters, 20),
        ]);
    }
}
