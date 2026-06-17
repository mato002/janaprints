<?php

namespace App\Http\Controllers\Admin\Hr;

use App\Http\Controllers\Controller;
use App\Models\Hr\PayrollRun;
use App\Support\Hr\PayrollRunService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PayrollDashboardController extends Controller
{
    public function __construct(
        protected PayrollRunService $payroll,
    ) {}

    public function __invoke(Request $request): View
    {
        $this->authorize('viewAny', PayrollRun::class);

        $companyId = tenant()->companyId() ?? $request->user()->company_id;

        return view('admin.hr.payroll.dashboard', [
            'stats' => $this->payroll->dashboardStats($companyId),
            'recentRuns' => $this->payroll->recentRuns($companyId),
        ]);
    }
}
