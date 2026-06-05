<?php

namespace App\Http\Controllers\Admin\Hr;

use App\Http\Controllers\Admin\Concerns\ScopesToTenant;
use App\Http\Controllers\Controller;
use App\Models\Hr\AttendanceRecord;
use App\Support\Hr\AttendanceService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttendanceDashboardController extends Controller
{
    use ScopesToTenant;

    public function __construct(
        protected AttendanceService $attendance,
    ) {}

    public function __invoke(Request $request): View
    {
        $this->authorize('viewAny', AttendanceRecord::class);

        $companyId = tenant()->companyId() ?? $request->user()->company_id;
        $filters = $request->only(['branch_id', 'department_id', 'shift_id', 'employee_id']);
        $date = $request->input('date', now()->toDateString());

        return view('admin.hr.attendance.dashboard', [
            'stats' => $this->attendance->dashboardMetrics($companyId, \Illuminate\Support\Carbon::parse($date), $filters),
            'filters' => array_merge($filters, ['date' => $date]),
            'formData' => $this->attendance->formData($companyId),
        ]);
    }
}
