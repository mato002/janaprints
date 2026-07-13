<?php

namespace App\Http\Controllers\Admin\Hr;

use App\Enums\LeaveRequestStatus;
use App\Http\Controllers\Controller;
use App\Models\Hr\LeaveRequest;
use App\Support\Hr\LeaveExportService;
use App\Support\Hr\LeaveRequestService;
use App\Support\Platform\FormSettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LeaveRequestController extends Controller
{
    public function __construct(
        protected LeaveRequestService $leaveRequests,
        protected LeaveExportService $exports,
        protected FormSettingsService $formSettings,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', LeaveRequest::class);

        $companyId = tenant()->companyId() ?? $request->user()->company_id;
        $filters = $request->only(['status', 'employee_id', 'branch_id', 'department_id', 'leave_type_id']);

        return view('admin.hr.leave.index', [
            'requests' => $this->leaveRequests->paginate($companyId, $filters),
            'filters' => $filters,
            'formData' => $this->leaveRequests->formData($companyId),
            'statuses' => LeaveRequestStatus::cases(),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', LeaveRequest::class);

        $companyId = tenant()->companyId() ?? $request->user()->company_id;
        $branchId = tenant()->branchId() ?? $request->user()->default_branch_id;

        return view('admin.hr.leave.create', [
            'formData' => $this->leaveRequests->formData($companyId),
            'defaultEmployeeId' => $request->user()->employee_id,
            'formFields' => $this->formSettings->resolvedFields('leave_request.create', $companyId, $branchId),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', LeaveRequest::class);

        $data = $this->validateRequest($request);
        $submit = $request->boolean('submit');

        $leaveRequest = $this->leaveRequests->create($data, $request->user(), $submit);

        return redirect()
            ->route('admin.hr.leave.show', $leaveRequest)
            ->with('status', $submit
                ? __('Leave request submitted.')
                : __('Leave request saved as draft.'));
    }

    public function show(LeaveRequest $leaveRequest): View
    {
        $this->authorize('view', $leaveRequest);

        $leaveRequest->load([
            'employee', 'leaveType', 'branch', 'department',
            'submittedBy', 'supervisorApprovedBy', 'hrApprovedBy', 'rejectedBy',
        ]);

        $balance = app(\App\Support\Hr\LeaveBalanceService::class)
            ->balanceFor($leaveRequest->employee, $leaveRequest->leaveType);

        return view('admin.hr.leave.show', [
            'request' => $leaveRequest,
            'balance' => $balance,
            'balanceSummary' => app(\App\Support\Hr\LeaveBalanceService::class)->summary($balance),
        ]);
    }

    public function approveSupervisor(Request $request, LeaveRequest $leaveRequest): RedirectResponse
    {
        $this->authorize('approve', $leaveRequest);

        $this->leaveRequests->approveSupervisor($leaveRequest, $request->user());

        return back()->with('status', __('Supervisor approval recorded.'));
    }

    public function approveHr(Request $request, LeaveRequest $leaveRequest): RedirectResponse
    {
        $this->authorize('approve', $leaveRequest);

        $this->leaveRequests->approveHr($leaveRequest, $request->user());

        return back()->with('status', __('Leave request approved. Attendance updated.'));
    }

    public function reject(Request $request, LeaveRequest $leaveRequest): RedirectResponse
    {
        $this->authorize('reject', $leaveRequest);

        $data = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:2000'],
        ]);

        $this->leaveRequests->reject($leaveRequest, $request->user(), $data['rejection_reason']);

        return back()->with('status', __('Leave request rejected.'));
    }

    public function cancel(Request $request, LeaveRequest $leaveRequest): RedirectResponse
    {
        $this->authorize('create', LeaveRequest::class);

        $this->leaveRequests->cancel($leaveRequest, $request->user());

        return redirect()
            ->route('admin.hr.leave.index')
            ->with('status', __('Leave request cancelled.'));
    }

    public function export(Request $request): StreamedResponse
    {
        $this->authorize('export', LeaveRequest::class);

        $companyId = tenant()->companyId() ?? $request->user()->company_id;

        return $this->exports->export(
            $request->input('format', 'csv'),
            $companyId,
            $request->only(['status', 'employee_id', 'branch_id', 'department_id', 'leave_type_id']),
        );
    }

    /**
     * @return array<string, mixed>
     */
    protected function validateRequest(Request $request): array
    {
        $companyId = tenant()->companyId() ?? $request->user()->company_id;
        $branchId = tenant()->branchId() ?? $request->user()->default_branch_id;

        $rules = [
            'employee_id' => [
                'required',
                Rule::exists('employees', 'id')->where(fn ($q) => $q->where('company_id', $companyId)),
            ],
            'leave_type_id' => [
                'required',
                Rule::exists('leave_types', 'id')->where(fn ($q) => $q->where('company_id', $companyId)),
            ],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'is_half_day_start' => ['sometimes', 'boolean'],
            'is_half_day_end' => ['sometimes', 'boolean'],
            'reason' => ['nullable', 'string', 'max:2000'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];

        return $this->formSettings->validateRequest($request, 'leave_request.create', $rules, $companyId, $branchId);
    }
}
