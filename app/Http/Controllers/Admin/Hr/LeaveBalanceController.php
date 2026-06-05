<?php

namespace App\Http\Controllers\Admin\Hr;

use App\Http\Controllers\Controller;
use App\Models\Hr\LeaveBalance;
use App\Support\Hr\LeaveBalanceService;
use App\Support\Hr\LeaveRequestService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LeaveBalanceController extends Controller
{
    public function __construct(
        protected LeaveBalanceService $balances,
        protected LeaveRequestService $leaveRequests,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', \App\Models\Hr\LeaveRequest::class);

        $companyId = tenant()->companyId() ?? $request->user()->company_id;
        $year = (int) $request->input('year', now()->year);

        $balances = LeaveBalance::query()
            ->forTenant()
            ->where('company_id', $companyId)
            ->where('balance_year', $year)
            ->with(['employee', 'leaveType'])
            ->orderBy('employee_id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.hr.leave.balances', [
            'balances' => $balances,
            'year' => $year,
            'formData' => $this->leaveRequests->formData($companyId),
        ]);
    }
}
