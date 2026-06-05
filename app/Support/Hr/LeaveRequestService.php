<?php

namespace App\Support\Hr;

use App\Enums\AttendanceMethod;
use App\Enums\AttendanceStatus;
use App\Enums\EmploymentStatus;
use App\Enums\LeaveRequestStatus;
use App\Models\Employee;
use App\Models\Hr\AttendanceRecord;
use App\Models\Hr\LeaveRequest;
use App\Models\Hr\LeaveType;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class LeaveRequestService
{
    public function __construct(
        protected LeaveBalanceService $balances,
        protected LeaveConflictService $conflicts,
        protected OvertimeCalculationService $overtime,
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     */
    public function paginate(int $companyId, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = LeaveRequest::query()
            ->forTenant()
            ->where('company_id', $companyId)
            ->with(['employee', 'leaveType', 'branch', 'department']);

        $this->applyFilters($query, $filters);

        return $query
            ->orderByDesc('submitted_at')
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data, User $user, bool $submit = false): LeaveRequest
    {
        $employee = Employee::query()->findOrFail($data['employee_id']);
        $leaveType = LeaveType::query()->findOrFail($data['leave_type_id']);
        $start = Carbon::parse($data['start_date']);
        $end = Carbon::parse($data['end_date']);

        if ($end->lt($start)) {
            throw ValidationException::withMessages([
                'end_date' => __('End date must be on or after start date.'),
            ]);
        }

        $days = $this->calculateDays(
            $start,
            $end,
            (bool) ($data['is_half_day_start'] ?? false),
            (bool) ($data['is_half_day_end'] ?? false),
        );

        $request = LeaveRequest::query()->create([
            'company_id' => $employee->company_id,
            'branch_id' => $employee->branch_id,
            'department_id' => $employee->department_id,
            'employee_id' => $employee->id,
            'leave_type_id' => $leaveType->id,
            'start_date' => $start,
            'end_date' => $end,
            'is_half_day_start' => (bool) ($data['is_half_day_start'] ?? false),
            'is_half_day_end' => (bool) ($data['is_half_day_end'] ?? false),
            'days_requested' => $days,
            'reason' => $data['reason'] ?? null,
            'status' => LeaveRequestStatus::Draft,
            'notes' => $data['notes'] ?? null,
        ]);

        if ($submit) {
            return $this->submit($request, $user);
        }

        return $request->fresh(['employee', 'leaveType']);
    }

    public function submit(LeaveRequest $request, User $user): LeaveRequest
    {
        if (! in_array($request->status, [LeaveRequestStatus::Draft, LeaveRequestStatus::Rejected], true)) {
            throw ValidationException::withMessages([
                'status' => __('This leave request cannot be submitted.'),
            ]);
        }

        $employee = $request->employee;
        $balance = $this->balances->balanceFor($employee, $request->leaveType);
        $this->balances->assertSufficientBalance($balance, (float) $request->days_requested);

        $warnings = $this->conflicts->check(
            $employee,
            $request->start_date,
            $request->end_date,
            $request->id,
        );

        $request->update([
            'reference' => $request->reference ?? $this->generateReference($request),
            'status' => LeaveRequestStatus::Submitted,
            'conflict_warnings' => $warnings,
            'submitted_at' => now(),
            'submitted_by_user_id' => $user->id,
            'rejected_at' => null,
            'rejected_by_user_id' => null,
            'rejection_reason' => null,
        ]);

        $this->balances->addPending($balance, (float) $request->days_requested);

        return $request->fresh(['employee', 'leaveType']);
    }

    public function approveSupervisor(LeaveRequest $request, User $user): LeaveRequest
    {
        if ($request->status !== LeaveRequestStatus::Submitted) {
            throw ValidationException::withMessages([
                'status' => __('Only submitted requests can receive supervisor approval.'),
            ]);
        }

        if (! $request->leaveType->requires_supervisor_approval) {
            return $this->finalizeApproval($request, $user);
        }

        $nextStatus = $request->leaveType->requires_hr_approval
            ? LeaveRequestStatus::SupervisorApproved
            : LeaveRequestStatus::Approved;

        $request->update([
            'status' => $nextStatus,
            'supervisor_approved_at' => now(),
            'supervisor_approved_by_user_id' => $user->id,
        ]);

        if ($nextStatus === LeaveRequestStatus::Approved) {
            $this->finalizeApprovalEffects($request);
        }

        return $request->fresh(['employee', 'leaveType']);
    }

    public function approveHr(LeaveRequest $request, User $user): LeaveRequest
    {
        $allowed = [LeaveRequestStatus::Submitted, LeaveRequestStatus::SupervisorApproved];

        if (! in_array($request->status, $allowed, true)) {
            throw ValidationException::withMessages([
                'status' => __('This request is not awaiting HR approval.'),
            ]);
        }

        if ($request->status === LeaveRequestStatus::Submitted && $request->leaveType->requires_supervisor_approval) {
            throw ValidationException::withMessages([
                'status' => __('Supervisor approval is required first.'),
            ]);
        }

        return $this->finalizeApproval($request, $user);
    }

    public function reject(LeaveRequest $request, User $user, string $reason): LeaveRequest
    {
        if (! in_array($request->status, [
            LeaveRequestStatus::Submitted,
            LeaveRequestStatus::SupervisorApproved,
        ], true)) {
            throw ValidationException::withMessages([
                'status' => __('This request cannot be rejected.'),
            ]);
        }

        $balance = $this->balances->balanceFor($request->employee, $request->leaveType);
        $this->balances->releasePending($balance, (float) $request->days_requested);

        $request->update([
            'status' => LeaveRequestStatus::Rejected,
            'rejected_at' => now(),
            'rejected_by_user_id' => $user->id,
            'rejection_reason' => $reason,
        ]);

        return $request->fresh(['employee', 'leaveType']);
    }

    public function cancel(LeaveRequest $request, User $user): LeaveRequest
    {
        if ($request->status === LeaveRequestStatus::Approved) {
            $this->revertAttendance($request);
            $balance = $this->balances->balanceFor($request->employee, $request->leaveType);
            $balance->decrement('taken', min((float) $balance->taken, (float) $request->days_requested));
        } elseif (in_array($request->status, [LeaveRequestStatus::Submitted, LeaveRequestStatus::SupervisorApproved], true)) {
            $balance = $this->balances->balanceFor($request->employee, $request->leaveType);
            $this->balances->releasePending($balance, (float) $request->days_requested);
        }

        $request->update([
            'status' => LeaveRequestStatus::Cancelled,
            'cancelled_at' => now(),
        ]);

        $this->syncEmploymentStatus($request->employee);

        return $request->fresh(['employee', 'leaveType']);
    }

    protected function finalizeApproval(LeaveRequest $request, User $user): LeaveRequest
    {
        $request->update([
            'status' => LeaveRequestStatus::Approved,
            'hr_approved_at' => now(),
            'hr_approved_by_user_id' => $user->id,
            'supervisor_approved_at' => $request->supervisor_approved_at ?? now(),
            'supervisor_approved_by_user_id' => $request->supervisor_approved_by_user_id ?? $user->id,
        ]);

        $this->finalizeApprovalEffects($request);

        return $request->fresh(['employee', 'leaveType']);
    }

    protected function finalizeApprovalEffects(LeaveRequest $request): void
    {
        $balance = $this->balances->balanceFor($request->employee, $request->leaveType);
        $this->balances->confirmTaken($balance, (float) $request->days_requested);
        $this->syncAttendance($request);
        $this->syncEmploymentStatus($request->employee);
    }

    public function syncAttendance(LeaveRequest $request): void
    {
        $employee = $request->employee;
        $shift = $employee->shift;
        $scheduledHours = $this->overtime->scheduledHours($shift);
        $cursor = $request->start_date->copy();

        while ($cursor->lte($request->end_date)) {
            AttendanceRecord::query()->updateOrCreate(
                [
                    'employee_id' => $employee->id,
                    'attendance_date' => $cursor->toDateString(),
                ],
                [
                    'company_id' => $employee->company_id,
                    'branch_id' => $employee->branch_id,
                    'department_id' => $employee->department_id,
                    'shift_id' => $shift?->id,
                    'leave_request_id' => $request->id,
                    'scheduled_hours' => $scheduledHours,
                    'actual_hours' => 0,
                    'overtime_hours' => 0,
                    'late_minutes' => 0,
                    'status' => AttendanceStatus::Leave,
                    'method' => AttendanceMethod::Manual,
                    'notes' => __('Leave: :ref (:type)', [
                        'ref' => $request->reference,
                        'type' => $request->leaveType?->name,
                    ]),
                    'is_manual' => true,
                ],
            );

            $cursor->addDay();
        }
    }

    protected function revertAttendance(LeaveRequest $request): void
    {
        AttendanceRecord::query()
            ->where('leave_request_id', $request->id)
            ->delete();
    }

    protected function syncEmploymentStatus(Employee $employee): void
    {
        $today = Carbon::today();
        $onLeaveToday = LeaveRequest::query()
            ->where('employee_id', $employee->id)
            ->where('status', LeaveRequestStatus::Approved)
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->exists();

        $employee->update([
            'employment_status' => $onLeaveToday
                ? EmploymentStatus::OnLeave
                : EmploymentStatus::Active,
        ]);
    }

    public function calculateDays(Carbon $start, Carbon $end, bool $halfStart, bool $halfEnd): float
    {
        $days = $start->diffInDays($end) + 1;

        if ($halfStart) {
            $days -= 0.5;
        }

        if ($halfEnd) {
            $days -= 0.5;
        }

        return round(max(0.5, $days), 1);
    }

    protected function generateReference(LeaveRequest $request): string
    {
        return sprintf('LV-%s-%04d', $request->start_date->format('Y'), $request->id);
    }

    /**
     * @param  Builder<LeaveRequest>  $query
     * @param  array<string, mixed>  $filters
     */
    protected function applyFilters(Builder $query, array $filters): void
    {
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['employee_id'])) {
            $query->where('employee_id', $filters['employee_id']);
        }

        if (! empty($filters['branch_id'])) {
            $query->where('branch_id', $filters['branch_id']);
        }

        if (! empty($filters['department_id'])) {
            $query->where('department_id', $filters['department_id']);
        }

        if (! empty($filters['leave_type_id'])) {
            $query->where('leave_type_id', $filters['leave_type_id']);
        }
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return \Illuminate\Database\Eloquent\Collection<int, LeaveRequest>
     */
    public function exportRows(int $companyId, array $filters = [])
    {
        $query = LeaveRequest::query()
            ->where('company_id', $companyId)
            ->with(['employee', 'leaveType', 'branch', 'department']);

        $this->applyFilters($query, $filters);

        return $query->orderByDesc('start_date')->get();
    }

    /**
     * @return array<string, mixed>
     */
    public function dashboardStats(int $companyId): array
    {
        $base = LeaveRequest::query()->where('company_id', $companyId);

        return [
            'pending' => (clone $base)->whereIn('status', [
                LeaveRequestStatus::Submitted->value,
                LeaveRequestStatus::SupervisorApproved->value,
            ])->count(),
            'approved_this_month' => (clone $base)
                ->where('status', LeaveRequestStatus::Approved->value)
                ->whereMonth('hr_approved_at', now()->month)
                ->whereYear('hr_approved_at', now()->year)
                ->count(),
            'on_leave_today' => (clone $base)
                ->where('status', LeaveRequestStatus::Approved->value)
                ->whereDate('start_date', '<=', today())
                ->whereDate('end_date', '>=', today())
                ->count(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function formData(int $companyId): array
    {
        return [
            'employees' => Employee::query()
                ->where('company_id', $companyId)
                ->where('is_active', true)
                ->orderBy('first_name')
                ->get(),
            'leaveTypes' => app(LeaveTypeService::class)->forCompany($companyId),
            'branches' => \App\Models\Branch::query()->where('company_id', $companyId)->orderBy('name')->get(),
            'departments' => \App\Models\Department::query()->where('company_id', $companyId)->orderBy('name')->get(),
        ];
    }
}
