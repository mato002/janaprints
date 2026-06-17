<?php

namespace App\Http\Controllers\Admin\Hr;

use App\Http\Controllers\Controller;
use App\Models\Hr\EmployeeCompensation;
use App\Models\Hr\PayrollGroupDefinition;
use App\Support\Hr\PayrollGroupService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PayrollGroupDefinitionController extends Controller
{
    public function __construct(
        protected PayrollGroupService $payrollGroups,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', EmployeeCompensation::class);

        $companyId = tenant()->companyId() ?? $request->user()->company_id;

        return view('admin.hr.compensation.payroll-groups.index', [
            'groups' => $this->payrollGroups->allForCompany($companyId),
        ]);
    }

    public function deactivate(PayrollGroupDefinition $payrollGroupDefinition): RedirectResponse
    {
        $this->authorize('create', EmployeeCompensation::class);
        $this->ensureAccess($payrollGroupDefinition);

        $this->payrollGroups->deactivate($payrollGroupDefinition);

        return back()->with('status', __('Payroll group deactivated.'));
    }

    public function reactivate(PayrollGroupDefinition $payrollGroupDefinition): RedirectResponse
    {
        $this->authorize('create', EmployeeCompensation::class);
        $this->ensureAccess($payrollGroupDefinition);

        $this->payrollGroups->reactivate($payrollGroupDefinition);

        return back()->with('status', __('Payroll group reactivated.'));
    }

    protected function ensureAccess(PayrollGroupDefinition $payrollGroup): void
    {
        $companyId = tenant()->companyId() ?? auth()->user()?->company_id;

        abort_unless($payrollGroup->company_id === $companyId, 404);
    }
}
