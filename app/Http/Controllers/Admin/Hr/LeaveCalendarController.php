<?php

namespace App\Http\Controllers\Admin\Hr;

use App\Http\Controllers\Controller;
use App\Models\Hr\LeaveRequest;
use App\Support\Hr\LeaveCalendarService;
use App\Support\Hr\LeaveRequestService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class LeaveCalendarController extends Controller
{
    public function __construct(
        protected LeaveCalendarService $calendar,
        protected LeaveRequestService $leaveRequests,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', LeaveRequest::class);

        $companyId = tenant()->companyId() ?? $request->user()->company_id;
        $view = $request->input('view', 'month');
        $filters = $request->only(['branch_id', 'department_id', 'employee_id']);

        $year = (int) $request->input('year', now()->year);
        $month = (int) $request->input('month', now()->month);
        $weekStart = now()->startOfWeek();

        if ($view === 'week') {
            $weekStart = $request->filled('week')
                ? Carbon::parse($request->input('week'))->startOfWeek()
                : now()->startOfWeek();
            $events = $this->calendar->weekGrid($companyId, $weekStart, $filters);
            $periodLabel = $weekStart->format('M j').' – '.$weekStart->copy()->endOfWeek()->format('M j, Y');
        } else {
            $events = $this->calendar->monthGrid($companyId, $year, $month, $filters);
            $periodLabel = Carbon::create($year, $month, 1)->format('F Y');
        }

        return view('admin.hr.leave.calendar', [
            'events' => $events,
            'view' => $view,
            'filters' => $filters,
            'formData' => $this->leaveRequests->formData($companyId),
            'periodLabel' => $periodLabel,
            'year' => $year,
            'month' => $month,
            'weekStart' => $weekStart,
        ]);
    }
}
