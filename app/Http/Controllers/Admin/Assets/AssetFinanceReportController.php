<?php

namespace App\Http\Controllers\Admin\Assets;

use App\Http\Controllers\Controller;
use App\Models\Assets\DepreciationRun;
use App\Services\Assets\AssetFinanceReportService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AssetFinanceReportController extends Controller
{
    public function __construct(
        protected AssetFinanceReportService $reports,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', DepreciationRun::class);

        $report = $request->string('report', 'register')->toString();
        $companyId = (int) tenant()->companyId();
        $filters = array_filter([
            'branch_id' => tenant()->branchId(),
            'category_id' => $request->integer('category_id') ?: null,
            'from' => $request->string('from')->toString() ?: null,
            'to' => $request->string('to')->toString() ?: null,
        ]);

        $data = match ($report) {
            'valuation' => $this->reports->valuationReport($companyId, $filters),
            'depreciation_schedule' => $this->reports->depreciationSchedule($companyId, $filters),
            'fully_depreciated' => $this->reports->fullyDepreciated($companyId, tenant()->branchId()),
            'near_end_of_life' => $this->reports->nearEndOfLife($companyId, tenant()->branchId()),
            default => $this->reports->registerReport($companyId, $filters),
        };

        return view('admin.assets.finance.reports.index', [
            'report' => $report,
            'data' => $data,
            'filters' => $filters,
        ]);
    }
}
