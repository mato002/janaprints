<?php

namespace App\Http\Controllers\Admin\Hr;

use App\Http\Controllers\Controller;
use App\Models\Hr\EmployeeCompensation;
use App\Support\Hr\CompensationService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CompensationDashboardController extends Controller
{
    public function __construct(
        protected CompensationService $compensation,
    ) {}

    public function __invoke(Request $request): View
    {
        $this->authorize('viewAny', EmployeeCompensation::class);

        $companyId = tenant()->companyId() ?? $request->user()->company_id;

        return view('admin.hr.compensation.dashboard', [
            'stats' => $this->compensation->dashboardStats($companyId),
            'missingEmployees' => $this->compensation->employeesMissingCompensation($companyId),
        ]);
    }
}
