<?php

namespace App\Http\Controllers\Admin\Hr;

use App\Enums\LeaveRequestStatus;
use App\Http\Controllers\Controller;
use App\Models\Hr\LeaveBalance;
use App\Models\Hr\LeaveRequest;
use App\Models\Hr\LeaveType;
use App\Support\Hr\LeaveCalendarService;
use App\Support\Hr\LeaveConfigurationService;
use App\Support\Hr\LeaveRequestService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class LeaveDashboardController extends Controller
{
    public function __construct(
        protected LeaveRequestService $leaveRequests,
        protected LeaveCalendarService $calendar,
        protected LeaveConfigurationService $leaveConfig,
    ) {}

    public function __invoke(Request $request): View
    {
        $this->authorize('viewAny', LeaveRequest::class);

        $companyId = tenant()->companyId() ?? $request->user()->company_id;
        $canManageSetup = $request->user()?->can('viewAny', LeaveType::class) ?? false;
        $tab = $this->resolveTab($request, $canManageSetup);
        $formData = $this->leaveRequests->formData($companyId);

        $payload = [
            'stats' => $this->leaveRequests->dashboardStats($companyId),
            'formData' => $formData,
            'tab' => $tab,
            'statuses' => LeaveRequestStatus::cases(),
            'canManageSetup' => $canManageSetup,
        ];

        return view('admin.hr.leave.dashboard', match ($tab) {
            'balances' => array_merge($payload, $this->balancesPayload($request, $companyId)),
            'calendar' => array_merge($payload, $this->calendarPayload($request, $companyId, $formData)),
            'setup' => array_merge($payload, $this->setupPayload($companyId)),
            default => array_merge($payload, $this->requestsPayload($request, $companyId, $formData)),
        });
    }

    protected function resolveTab(Request $request, bool $canManageSetup): string
    {
        $tab = $request->string('tab')->toString();

        if ($tab === 'setup' && $canManageSetup) {
            return 'setup';
        }

        return in_array($tab, ['requests', 'balances', 'calendar'], true) ? $tab : 'requests';
    }

    /**
     * @return array<string, mixed>
     */
    protected function setupPayload(int $companyId): array
    {
        $this->authorize('viewAny', LeaveType::class);

        $data = $this->leaveConfig->centerData($companyId);

        return [
            'setupStats' => $data['stats'],
            'leaveTypes' => $data['leaveTypes'],
            'holidays' => $data['holidays'],
            'policies' => $data['policies'],
            'accrualRules' => $data['accrualRules'],
            'carryForwardRules' => $data['carryForwardRules'],
            'leaveTypeOptions' => $data['leaveTypeOptions'],
            'policyOptions' => $data['policyOptions'],
            'branches' => $data['branches'],
            'units' => $data['units'],
            'frequencies' => $data['frequencies'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function requestsPayload(Request $request, int $companyId, array $formData): array
    {
        $filters = $request->only(['status', 'employee_id', 'branch_id', 'department_id', 'leave_type_id']);

        return [
            'requests' => $this->leaveRequests->paginate($companyId, $filters),
            'filters' => $filters,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function balancesPayload(Request $request, int $companyId): array
    {
        $year = (int) $request->input('year', now()->year);

        $balances = LeaveBalance::query()
            ->forTenant()
            ->where('company_id', $companyId)
            ->where('balance_year', $year)
            ->with(['employee', 'leaveType'])
            ->orderBy('employee_id')
            ->paginate(20)
            ->withQueryString();

        return [
            'balances' => $balances,
            'year' => $year,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function calendarPayload(Request $request, int $companyId, array $formData): array
    {
        $view = $request->input('view', 'month') === 'week' ? 'week' : 'month';
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

        return [
            'events' => $events,
            'calendarView' => $view,
            'filters' => $filters,
            'periodLabel' => $periodLabel,
            'year' => $year,
            'month' => $month,
            'weekStart' => $weekStart,
        ];
    }
}
