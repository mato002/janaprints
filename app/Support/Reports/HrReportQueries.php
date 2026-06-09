<?php

namespace App\Support\Reports;

use App\Enums\AttendanceStatus;
use App\Enums\EmployeeDocumentCategory;
use App\Enums\LeaveRequestStatus;
use App\Enums\PayrollRunStatus;
use App\Enums\TrainingAssignmentStatus;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Hr\AttendanceRecord;
use App\Models\Hr\CompensationSalaryChange;
use App\Models\Hr\EmployeeDocument;
use App\Models\Hr\EmployeeExit;
use App\Models\Hr\EmployeeTrainingAssignment;
use App\Models\Hr\LeaveRequest;
use App\Models\Hr\PayrollPayslip;
use App\Models\Hr\PayrollRun;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class HrReportQueries
{
    /**
     * @return array{records: int, present: int, absent: int, late: int, attendance_rate: float, avg_hours: float}
     */
    public function attendanceSummary(HrReportScope $scope): array
    {
        if (! $this->hasTable('attendance_records')) {
            return ['records' => 0, 'present' => 0, 'absent' => 0, 'late' => 0, 'attendance_rate' => 0.0, 'avg_hours' => 0.0];
        }

        $query = $this->attendanceQuery($scope);
        $records = (clone $query)->count();

        $present = (clone $query)->whereIn('attendance_records.status', [
            AttendanceStatus::Present->value,
            AttendanceStatus::Late->value,
        ])->count();

        $absent = (clone $query)->where('attendance_records.status', AttendanceStatus::Absent->value)->count();

        $late = (clone $query)->where(function (Builder $q) {
            $q->where('attendance_records.status', AttendanceStatus::Late->value)
                ->orWhere('attendance_records.late_minutes', '>', 0);
        })->count();

        $attendanceRate = $records > 0 ? round(($present / $records) * 100, 1) : 0.0;
        $avgHours = round((float) (clone $query)->avg('attendance_records.actual_hours'), 2);

        return [
            'records' => $records,
            'present' => $present,
            'absent' => $absent,
            'late' => $late,
            'attendance_rate' => $attendanceRate,
            'avg_hours' => $avgHours,
        ];
    }

    /**
     * @return list<array<int, string|int|float>>
     */
    public function attendanceByDay(HrReportScope $scope): array
    {
        if (! $this->hasTable('attendance_records')) {
            return [];
        }

        $period = CarbonPeriod::create($scope->fromDate, $scope->toDate);

        return collect($period)->map(function (Carbon $day) use ($scope) {
            $date = $day->toDateString();
            $dayScope = new HrReportScope(
                $scope->companyId,
                $scope->branchId,
                $date,
                $date,
                $scope->employeeId,
                $scope->departmentId,
                $scope->jobTitleId,
                $scope->status,
            );

            $summary = $this->attendanceSummary($dayScope);

            return [
                $day->format('Y-m-d'),
                $summary['records'],
                $summary['present'],
                $summary['absent'],
                $summary['late'],
            ];
        })->all();
    }

    /**
     * @return list<array<int, string|int|float>>
     */
    public function attendanceByDepartment(HrReportScope $scope): array
    {
        if (! $this->hasTable('attendance_records')) {
            return [];
        }

        $rows = $this->attendanceQuery($scope)
            ->select(
                'attendance_records.department_id',
                DB::raw('COUNT(*) as total'),
                DB::raw("SUM(CASE WHEN attendance_records.status IN ('present','late') THEN 1 ELSE 0 END) as present"),
                DB::raw("SUM(CASE WHEN attendance_records.status = 'absent' THEN 1 ELSE 0 END) as absent"),
                DB::raw('SUM(CASE WHEN attendance_records.late_minutes > 0 OR attendance_records.status = \'late\' THEN 1 ELSE 0 END) as late'),
            )
            ->groupBy('attendance_records.department_id')
            ->orderByDesc('total')
            ->get();

        $departmentNames = Department::query()
            ->where('company_id', $scope->companyId)
            ->whereIn('id', $rows->pluck('department_id')->filter())
            ->pluck('name', 'id');

        return $rows->map(fn ($row) => [
            (string) ($departmentNames[$row->department_id] ?? '—'),
            (int) $row->total,
            (int) $row->present,
            (int) $row->absent,
            (int) $row->late,
        ])->all();
    }

    /**
     * @return array{count: int, avg_late_minutes: float}
     */
    public function lateArrivalsSummary(HrReportScope $scope): array
    {
        if (! $this->hasTable('attendance_records')) {
            return ['count' => 0, 'avg_late_minutes' => 0.0];
        }

        $query = $this->attendanceQuery($scope)->where(function (Builder $q) {
            $q->where('attendance_records.status', AttendanceStatus::Late->value)
                ->orWhere('attendance_records.late_minutes', '>', 0);
        });

        return [
            'count' => (clone $query)->count(),
            'avg_late_minutes' => round((float) (clone $query)->avg('late_minutes'), 1),
        ];
    }

    /**
     * @return list<array<int, string|int>>
     */
    public function lateArrivalsRows(HrReportScope $scope, int $limit = 100): array
    {
        if (! $this->hasTable('attendance_records')) {
            return [];
        }

        return $this->attendanceQuery($scope)
            ->with(['employee', 'department'])
            ->where(function (Builder $q) {
                $q->where('attendance_records.status', AttendanceStatus::Late->value)
                    ->orWhere('attendance_records.late_minutes', '>', 0);
            })
            ->orderByDesc('attendance_date')
            ->limit($limit)
            ->get()
            ->map(fn (AttendanceRecord $record) => [
                $record->employee?->full_name ?? '—',
                $record->attendance_date?->format('Y-m-d') ?? '—',
                $record->clock_in_at?->format('H:i') ?? '—',
                (int) $record->late_minutes,
                $record->department?->name ?? '—',
            ])
            ->all();
    }

    /**
     * @return array{absent_days: int, rate: float, employees: int}
     */
    public function absenteeismSummary(HrReportScope $scope): array
    {
        if (! $this->hasTable('attendance_records')) {
            return ['absent_days' => 0, 'rate' => 0.0, 'employees' => 0];
        }

        $query = $this->attendanceQuery($scope)->where('status', AttendanceStatus::Absent->value);
        $absentDays = (clone $query)->count();
        $totalRecords = $this->attendanceQuery($scope)->count();
        $employees = (clone $query)->distinct('employee_id')->count('employee_id');

        return [
            'absent_days' => $absentDays,
            'rate' => $totalRecords > 0 ? round(($absentDays / $totalRecords) * 100, 1) : 0.0,
            'employees' => $employees,
        ];
    }

    /**
     * @return list<array<int, string|int|float>>
     */
    public function absenteeismByEmployee(HrReportScope $scope, int $limit = 50): array
    {
        if (! $this->hasTable('attendance_records')) {
            return [];
        }

        return $this->attendanceQuery($scope)
            ->where('attendance_records.status', AttendanceStatus::Absent->value)
            ->join('employees', 'employees.id', '=', 'attendance_records.employee_id')
            ->select(
                'employees.first_name',
                'employees.last_name',
                'employees.employee_number',
                DB::raw('COUNT(*) as absent_days'),
            )
            ->groupBy('employees.id', 'employees.first_name', 'employees.last_name', 'employees.employee_number')
            ->orderByDesc('absent_days')
            ->limit($limit)
            ->get()
            ->map(fn ($row) => [
                trim("{$row->first_name} {$row->last_name}"),
                (string) $row->employee_number,
                (int) $row->absent_days,
            ])
            ->all();
    }

    /**
     * @return list<array<int, string|int>>
     */
    public function absenteeismByDepartment(HrReportScope $scope): array
    {
        if (! $this->hasTable('attendance_records')) {
            return [];
        }

        $rows = $this->attendanceQuery($scope)
            ->where('attendance_records.status', AttendanceStatus::Absent->value)
            ->select('attendance_records.department_id', DB::raw('COUNT(*) as absent_days'))
            ->groupBy('attendance_records.department_id')
            ->orderByDesc('absent_days')
            ->get();

        $departmentNames = Department::query()
            ->where('company_id', $scope->companyId)
            ->whereIn('id', $rows->pluck('department_id')->filter())
            ->pluck('name', 'id');

        return $rows->map(fn ($row) => [
            (string) ($departmentNames[$row->department_id] ?? '—'),
            (int) $row->absent_days,
        ])->all();
    }

    /**
     * @return array{total_hours: float, employees: int, records: int}
     */
    public function overtimeSummary(HrReportScope $scope): array
    {
        if (! $this->hasTable('attendance_records')) {
            return ['total_hours' => 0.0, 'employees' => 0, 'records' => 0];
        }

        $query = $this->attendanceQuery($scope)->where('overtime_hours', '>', 0);

        return [
            'total_hours' => round((float) (clone $query)->sum('overtime_hours'), 2),
            'employees' => (clone $query)->distinct('employee_id')->count('employee_id'),
            'records' => (clone $query)->count(),
        ];
    }

    /**
     * @return list<array<int, string|int|float>>
     */
    public function overtimeRows(HrReportScope $scope, int $limit = 100): array
    {
        if (! $this->hasTable('attendance_records')) {
            return [];
        }

        return $this->attendanceQuery($scope)
            ->with('employee')
            ->where('attendance_records.overtime_hours', '>', 0)
            ->orderByDesc('attendance_date')
            ->limit($limit)
            ->get()
            ->map(fn (AttendanceRecord $record) => [
                $record->employee?->full_name ?? '—',
                $record->attendance_date?->format('Y-m-d') ?? '—',
                (float) $record->scheduled_hours,
                (float) $record->actual_hours,
                (float) $record->overtime_hours,
            ])
            ->all();
    }

    /**
     * @return array{days_used: float, requests: int, employees: int}
     */
    public function leaveUtilizationSummary(HrReportScope $scope): array
    {
        if (! $this->hasTable('leave_requests')) {
            return ['days_used' => 0.0, 'requests' => 0, 'employees' => 0];
        }

        $query = $this->leaveQuery($scope)
            ->where('status', LeaveRequestStatus::Approved->value);

        return [
            'days_used' => round((float) (clone $query)->sum('days_requested'), 1),
            'requests' => (clone $query)->count(),
            'employees' => (clone $query)->distinct('employee_id')->count('employee_id'),
        ];
    }

    /**
     * @return list<array<int, string|int|float>>
     */
    public function leaveUtilizationByType(HrReportScope $scope): array
    {
        if (! $this->hasTable('leave_requests') || ! $this->hasTable('leave_types')) {
            return [];
        }

        return $this->leaveQuery($scope)
            ->where('leave_requests.status', LeaveRequestStatus::Approved->value)
            ->join('leave_types', 'leave_types.id', '=', 'leave_requests.leave_type_id')
            ->select('leave_types.name', DB::raw('SUM(leave_requests.days_requested) as days_used'), DB::raw('COUNT(*) as requests'))
            ->groupBy('leave_types.id', 'leave_types.name')
            ->orderByDesc('days_used')
            ->get()
            ->map(fn ($row) => [
                (string) $row->name,
                round((float) $row->days_used, 1),
                (int) $row->requests,
            ])
            ->all();
    }

    /**
     * @return list<array<int, string|int|float>>
     */
    public function leaveUtilizationByEmployee(HrReportScope $scope, int $limit = 50): array
    {
        if (! $this->hasTable('leave_requests')) {
            return [];
        }

        return $this->leaveQuery($scope)
            ->where('leave_requests.status', LeaveRequestStatus::Approved->value)
            ->join('employees', 'employees.id', '=', 'leave_requests.employee_id')
            ->select(
                'employees.first_name',
                'employees.last_name',
                DB::raw('SUM(leave_requests.days_requested) as days_used'),
                DB::raw('COUNT(*) as requests'),
            )
            ->groupBy('employees.id', 'employees.first_name', 'employees.last_name')
            ->orderByDesc('days_used')
            ->limit($limit)
            ->get()
            ->map(fn ($row) => [
                trim("{$row->first_name} {$row->last_name}"),
                round((float) $row->days_used, 1),
                (int) $row->requests,
            ])
            ->all();
    }

    /**
     * @return array{gross: float, net: float, deductions: float, runs: int}
     */
    public function payrollCostSummary(HrReportScope $scope): array
    {
        if (! $this->hasTable('payroll_runs')) {
            return ['gross' => 0.0, 'net' => 0.0, 'deductions' => 0.0, 'runs' => 0];
        }

        $query = $this->payrollRunQuery($scope);

        return [
            'gross' => round((float) (clone $query)->sum('gross_total'), 2),
            'net' => round((float) (clone $query)->sum('net_total'), 2),
            'deductions' => round((float) (clone $query)->sum('deductions_total'), 2),
            'runs' => (clone $query)->count(),
        ];
    }

    /**
     * @return list<array<int, string|int|float>>
     */
    public function payrollCostByRun(HrReportScope $scope): array
    {
        if (! $this->hasTable('payroll_runs')) {
            return [];
        }

        return $this->payrollRunQuery($scope)
            ->orderByDesc('pay_date')
            ->get()
            ->map(fn (PayrollRun $run) => [
                (string) $run->reference,
                $run->period_start?->format('Y-m-d') ?? '—',
                $run->period_end?->format('Y-m-d') ?? '—',
                (int) $run->employee_count,
                (float) $run->gross_total,
                (float) $run->net_total,
            ])
            ->all();
    }

    /**
     * @return list<array<int, string|int|float>>
     */
    public function payrollCostByDepartment(HrReportScope $scope): array
    {
        if (! $this->hasTable('payroll_payslips') || ! $this->hasTable('payroll_runs')) {
            return [];
        }

        $runIds = $this->payrollRunQuery($scope)->pluck('id');

        if ($runIds->isEmpty()) {
            return [];
        }

        $rows = PayrollPayslip::query()
            ->whereIn('payroll_payslips.payroll_run_id', $runIds)
            ->join('employees', 'employees.id', '=', 'payroll_payslips.employee_id')
            ->select(
                'employees.department_id',
                DB::raw('COUNT(DISTINCT payroll_payslips.employee_id) as employees'),
                DB::raw('SUM(payroll_payslips.gross_pay) as gross'),
                DB::raw('SUM(payroll_payslips.net_pay) as net'),
            )
            ->groupBy('employees.department_id')
            ->orderByDesc('gross')
            ->get();

        $departmentNames = Department::query()
            ->where('company_id', $scope->companyId)
            ->whereIn('id', $rows->pluck('department_id')->filter())
            ->pluck('name', 'id');

        return $rows->map(fn ($row) => [
            (string) ($departmentNames[$row->department_id] ?? '—'),
            (int) $row->employees,
            round((float) $row->gross, 2),
            round((float) $row->net, 2),
        ])->all();
    }

    /**
     * @return array{total: int, active: int, inactive: int}
     */
    public function headcountSummary(HrReportScope $scope): array
    {
        $query = $this->employeeQuery($scope, includeInactive: true);

        return [
            'total' => (clone $query)->count(),
            'active' => (clone $query)->where('is_active', true)->count(),
            'inactive' => (clone $query)->where('is_active', false)->count(),
        ];
    }

    /**
     * @return list<array<int, string|int>>
     */
    public function headcountByDepartment(HrReportScope $scope): array
    {
        $rows = $this->employeeQuery($scope)
            ->select(
                'employees.department_id',
                DB::raw('COUNT(*) as headcount'),
                DB::raw('SUM(CASE WHEN employees.is_active = 1 THEN 1 ELSE 0 END) as active'),
            )
            ->groupBy('employees.department_id')
            ->orderByDesc('headcount')
            ->get();

        $departmentNames = Department::query()
            ->where('company_id', $scope->companyId)
            ->whereIn('id', $rows->pluck('department_id')->filter())
            ->pluck('name', 'id');

        return $rows->map(fn ($row) => [
            (string) ($departmentNames[$row->department_id] ?? '—'),
            (int) $row->headcount,
            (int) $row->active,
        ])->all();
    }

    /**
     * @return array{hires: int, exits: int, salary_changes: int}
     */
    public function movementSummary(HrReportScope $scope): array
    {
        $hires = $this->employeeQuery($scope, includeInactive: true)
            ->whereDate('hire_date', '>=', $scope->fromDate)
            ->whereDate('hire_date', '<=', $scope->toDate)
            ->count();

        $exits = 0;
        if ($this->hasTable('employee_exits')) {
            $exits = EmployeeExit::query()
                ->where('company_id', $scope->companyId)
                ->when($scope->branchId, fn ($q) => $q->where('branch_id', $scope->branchId))
                ->whereDate('exit_date', '>=', $scope->fromDate)
                ->whereDate('exit_date', '<=', $scope->toDate)
                ->count();
        }

        $salaryChanges = 0;
        if ($this->hasTable('compensation_salary_changes')) {
            $salaryChanges = CompensationSalaryChange::query()
                ->where('company_id', $scope->companyId)
                ->whereDate('effective_from', '>=', $scope->fromDate)
                ->whereDate('effective_from', '<=', $scope->toDate)
                ->count();
        }

        return ['hires' => $hires, 'exits' => $exits, 'salary_changes' => $salaryChanges];
    }

    /**
     * @return list<array<int, string>>
     */
    public function movementRows(HrReportScope $scope, int $limit = 100): array
    {
        $rows = collect();

        $this->employeeQuery($scope, includeInactive: true)
            ->whereDate('hire_date', '>=', $scope->fromDate)
            ->whereDate('hire_date', '<=', $scope->toDate)
            ->with('department')
            ->get()
            ->each(function (Employee $employee) use ($rows) {
                $rows->push([
                    $employee->full_name,
                    __('Hire'),
                    $employee->hire_date?->format('Y-m-d') ?? '—',
                    $employee->department?->name ?? '—',
                ]);
            });

        if ($this->hasTable('employee_exits')) {
            EmployeeExit::query()
                ->where('company_id', $scope->companyId)
                ->when($scope->branchId, fn ($q) => $q->where('branch_id', $scope->branchId))
                ->whereDate('exit_date', '>=', $scope->fromDate)
                ->whereDate('exit_date', '<=', $scope->toDate)
                ->with(['employee.department'])
                ->get()
                ->each(function (EmployeeExit $exit) use ($rows) {
                    $rows->push([
                        $exit->employee?->full_name ?? '—',
                        __('Exit'),
                        $exit->exit_date?->format('Y-m-d') ?? '—',
                        $exit->employee?->department?->name ?? '—',
                    ]);
                });
        }

        if ($this->hasTable('compensation_salary_changes')) {
            CompensationSalaryChange::query()
                ->where('company_id', $scope->companyId)
                ->whereDate('effective_from', '>=', $scope->fromDate)
                ->whereDate('effective_from', '<=', $scope->toDate)
                ->with(['employee.department'])
                ->get()
                ->each(function (CompensationSalaryChange $change) use ($rows) {
                    $rows->push([
                        $change->employee?->full_name ?? '—',
                        __('Salary Change'),
                        $change->effective_from?->format('Y-m-d') ?? '—',
                        $change->employee?->department?->name ?? '—',
                    ]);
                });
        }

        return $rows->sortByDesc(fn (array $row) => $row[2])->take($limit)->values()->all();
    }

    /**
     * @return array{expiring: int, expired: int}
     */
    public function contractExpirySummary(HrReportScope $scope): array
    {
        if (! $this->hasTable('employee_documents')) {
            return ['expiring' => 0, 'expired' => 0];
        }

        $query = $this->contractDocumentQuery($scope);
        $asOf = Carbon::parse($scope->toDate);
        $windowEnd = $asOf->copy()->addDays(90);

        return [
            'expiring' => (clone $query)
                ->whereNotNull('expires_at')
                ->whereDate('expires_at', '>', $asOf)
                ->whereDate('expires_at', '<=', $windowEnd)
                ->count(),
            'expired' => (clone $query)
                ->whereNotNull('expires_at')
                ->whereDate('expires_at', '<=', $asOf)
                ->count(),
        ];
    }

    /**
     * @return list<array<int, string>>
     */
    public function contractExpiryRows(HrReportScope $scope, int $limit = 100): array
    {
        if (! $this->hasTable('employee_documents')) {
            return [];
        }

        $asOf = Carbon::parse($scope->toDate);

        return $this->contractDocumentQuery($scope)
            ->with('employee')
            ->whereNotNull('expires_at')
            ->whereDate('expires_at', '<=', $asOf->copy()->addDays(90))
            ->orderBy('expires_at')
            ->limit($limit)
            ->get()
            ->map(fn (EmployeeDocument $doc) => [
                $doc->employee?->full_name ?? '—',
                (string) $doc->title,
                $doc->expires_at?->format('Y-m-d') ?? '—',
                $doc->isExpired() ? __('Expired') : __('Expiring Soon'),
            ])
            ->all();
    }

    /**
     * @return array{compliance_rate: float, overdue: int, completed: int, assigned: int}
     */
    public function trainingComplianceSummary(HrReportScope $scope): array
    {
        if (! $this->hasTable('employee_training_assignments')) {
            return ['compliance_rate' => 0.0, 'overdue' => 0, 'completed' => 0, 'assigned' => 0];
        }

        $query = $this->trainingQuery($scope);
        $assigned = (clone $query)->count();
        $completed = (clone $query)->where('status', TrainingAssignmentStatus::Completed->value)->count();
        $overdue = (clone $query)
            ->whereNotIn('status', [TrainingAssignmentStatus::Completed->value, TrainingAssignmentStatus::Cancelled->value])
            ->whereDate('due_date', '<', $scope->toDate)
            ->count();

        return [
            'compliance_rate' => $assigned > 0 ? round(($completed / $assigned) * 100, 1) : 0.0,
            'overdue' => $overdue,
            'completed' => $completed,
            'assigned' => $assigned,
        ];
    }

    /**
     * @return list<array<int, string>>
     */
    public function trainingComplianceRows(HrReportScope $scope, int $limit = 100): array
    {
        if (! $this->hasTable('employee_training_assignments')) {
            return [];
        }

        return $this->trainingQuery($scope)
            ->with(['employee', 'program'])
            ->orderBy('due_date')
            ->limit($limit)
            ->get()
            ->map(fn (EmployeeTrainingAssignment $assignment) => [
                $assignment->employee?->full_name ?? '—',
                $assignment->program?->name ?? '—',
                $assignment->status instanceof TrainingAssignmentStatus
                    ? $assignment->status->label()
                    : ucfirst((string) $assignment->status),
                $assignment->due_date?->format('Y-m-d') ?? '—',
            ])
            ->all();
    }

    /**
     * @return Builder<AttendanceRecord>
     */
    protected function attendanceQuery(HrReportScope $scope): Builder
    {
        $query = AttendanceRecord::query()
            ->where('attendance_records.company_id', $scope->companyId)
            ->whereDate('attendance_records.attendance_date', '>=', $scope->fromDate)
            ->whereDate('attendance_records.attendance_date', '<=', $scope->toDate);

        $this->applyEmployeeFilters($query, $scope, 'attendance_records');

        return $query;
    }

    /**
     * @return Builder<LeaveRequest>
     */
    protected function leaveQuery(HrReportScope $scope): Builder
    {
        $query = LeaveRequest::query()
            ->where('leave_requests.company_id', $scope->companyId)
            ->where(function (Builder $q) use ($scope) {
                $q->whereDate('start_date', '<=', $scope->toDate)
                    ->whereDate('end_date', '>=', $scope->fromDate);
            });

        $this->applyEmployeeFilters($query, $scope, 'leave_requests');

        return $query;
    }

    /**
     * @return Builder<PayrollRun>
     */
    protected function payrollRunQuery(HrReportScope $scope): Builder
    {
        return PayrollRun::query()
            ->where('company_id', $scope->companyId)
            ->when($scope->branchId, fn ($q) => $q->where('branch_id', $scope->branchId))
            ->whereIn('status', [
                PayrollRunStatus::Approved->value,
                PayrollRunStatus::Posted->value,
            ])
            ->where(function (Builder $q) use ($scope) {
                $q->whereDate('period_start', '<=', $scope->toDate)
                    ->whereDate('period_end', '>=', $scope->fromDate);
            });
    }

    /**
     * @return Builder<Employee>
     */
    protected function employeeQuery(HrReportScope $scope, bool $includeInactive = false): Builder
    {
        $query = Employee::query()->where('employees.company_id', $scope->companyId);

        if (! $includeInactive) {
            $query->where('employees.is_active', true);
        }

        if ($scope->branchId) {
            $query->where('employees.branch_id', $scope->branchId);
        }

        if ($scope->departmentId) {
            $query->where('employees.department_id', $scope->departmentId);
        }

        if ($scope->jobTitleId) {
            $query->where('employees.job_title_id', $scope->jobTitleId);
        }

        if ($scope->employeeId) {
            $query->where('employees.id', $scope->employeeId);
        }

        if ($scope->status) {
            $query->where('employees.employment_status', $scope->status);
        }

        return $query;
    }

    /**
     * @return Builder<EmployeeDocument>
     */
    protected function contractDocumentQuery(HrReportScope $scope): Builder
    {
        $query = EmployeeDocument::query()
            ->where('employee_documents.company_id', $scope->companyId)
            ->where('category', EmployeeDocumentCategory::Contract->value)
            ->where('is_active', true);

        if ($scope->employeeId || $scope->departmentId || $scope->branchId || $scope->jobTitleId || $scope->status) {
            $query->whereHas('employee', function (Builder $q) use ($scope) {
                if ($scope->branchId) {
                    $q->where('branch_id', $scope->branchId);
                }
                if ($scope->departmentId) {
                    $q->where('department_id', $scope->departmentId);
                }
                if ($scope->jobTitleId) {
                    $q->where('job_title_id', $scope->jobTitleId);
                }
                if ($scope->employeeId) {
                    $q->where('id', $scope->employeeId);
                }
                if ($scope->status) {
                    $q->where('employment_status', $scope->status);
                }
            });
        }

        return $query;
    }

    /**
     * @return Builder<EmployeeTrainingAssignment>
     */
    protected function trainingQuery(HrReportScope $scope): Builder
    {
        $query = EmployeeTrainingAssignment::query()
            ->where('employee_training_assignments.company_id', $scope->companyId)
            ->where(function (Builder $q) use ($scope) {
                $q->whereDate('assigned_at', '<=', $scope->toDate)
                    ->where(function (Builder $q2) use ($scope) {
                        $q2->whereNull('completed_at')
                            ->orWhereDate('completed_at', '>=', $scope->fromDate);
                    });
            });

        if ($scope->employeeId || $scope->departmentId || $scope->branchId || $scope->jobTitleId || $scope->status) {
            $query->whereHas('employee', function (Builder $q) use ($scope) {
                if ($scope->branchId) {
                    $q->where('branch_id', $scope->branchId);
                }
                if ($scope->departmentId) {
                    $q->where('department_id', $scope->departmentId);
                }
                if ($scope->jobTitleId) {
                    $q->where('job_title_id', $scope->jobTitleId);
                }
                if ($scope->employeeId) {
                    $q->where('id', $scope->employeeId);
                }
                if ($scope->status) {
                    $q->where('employment_status', $scope->status);
                }
            });
        }

        return $query;
    }

    /**
     * @param  Builder<\Illuminate\Database\Eloquent\Model>  $query
     */
    protected function applyEmployeeFilters(Builder $query, HrReportScope $scope, string $table): void
    {
        if ($scope->branchId) {
            $query->where("{$table}.branch_id", $scope->branchId);
        }

        if ($scope->departmentId) {
            $query->where("{$table}.department_id", $scope->departmentId);
        }

        if ($scope->employeeId) {
            $query->where("{$table}.employee_id", $scope->employeeId);
        }

        if ($scope->jobTitleId || $scope->status) {
            $query->whereHas('employee', function (Builder $q) use ($scope) {
                if ($scope->jobTitleId) {
                    $q->where('job_title_id', $scope->jobTitleId);
                }
                if ($scope->status) {
                    $q->where('employment_status', $scope->status);
                }
            });
        }
    }

    protected function hasTable(string $table): bool
    {
        return Schema::hasTable($table);
    }
}
