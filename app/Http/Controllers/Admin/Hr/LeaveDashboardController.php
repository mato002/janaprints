<?php

namespace App\Http\Controllers\Admin\Hr;

use App\Http\Controllers\Controller;
use App\Models\Hr\LeaveRequest;
use App\Support\Hr\LeaveRequestService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LeaveDashboardController extends Controller
{
    public function __construct(
        protected LeaveRequestService $leaveRequests,
    ) {}

    public function __invoke(Request $request): View
    {
        $this->authorize('viewAny', LeaveRequest::class);

        $companyId = tenant()->companyId() ?? $request->user()->company_id;

        return view('admin.hr.leave.dashboard', [
            'stats' => $this->leaveRequests->dashboardStats($companyId),
            'formData' => $this->leaveRequests->formData($companyId),
        ]);
    }
}
