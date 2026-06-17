<?php

namespace App\Support\Hr;

use App\Enums\AttendanceStatus;
use App\Enums\EmployeeDocumentCategory;
use App\Enums\EmploymentStatus;
use App\Enums\LeaveRequestStatus;
use App\Enums\TrainingAssignmentStatus;
use App\Models\Assets\AssetHandover;
use App\Models\Assets\FixedAsset;
use App\Models\Employee;
use App\Models\Hr\AttendanceRecord;
use App\Models\JobTitle;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class Employee360WorkspaceService
{
    public function __construct(
        protected AttendanceService $attendance,
        protected LeaveRequestService $leaveRequests,
        protected LeaveCalendarService $leaveCalendar,
        protected LeaveBalanceService $leaveBalances,
        protected CompensationService $compensation,
        protected CompensationAuditService $compensationAudit,
        protected EmployeeDocumentService $documents,
        protected PerformanceReviewService $performance,
        protected PerformanceKpiCalculationService $kpis,
        protected TrainingAssignmentService $training,
        protected EmployeeExitService $exits,
        protected ExitFinalDuesService $exitDues,
        protected EmployeeTimelineService $timeline,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function build(Employee $employee): array
    {
        $employee->load([
            'branch', 'department', 'jobTitle.reportsTo', 'shift', 'compensation',
            'payrollAllowances' => fn ($q) => $q->where('is_active', true),
            'payrollDeductions' => fn ($q) => $q->where('is_active', true),
        ]);

        $companyId = $employee->company_id;
        $employeeId = $employee->id;
        $filters = ['employee_id' => $employeeId];

        return [
            'employee' => $employee,
            'supervisor' => $this->resolveSupervisor($employee),
            'overview' => $this->overview($employee),
            'attendance' => $this->attendanceTab($companyId, $employeeId),
            'leave' => $this->leaveTab($companyId, $employee, $filters),
            'compensation' => $this->compensationTab($employee, $companyId),
            'payroll' => $this->payrollTab($employee),
            'documents' => $this->documentsTab($companyId, $filters),
            'performance' => $this->performanceTab($companyId, $employee, $filters),
            'training' => $this->trainingTab($companyId, $employeeId, $filters),
            'assets' => $this->assetsTab($employeeId),
            'exit' => $this->exitTab($companyId, $employee, $filters),
            'timeline' => $this->timeline->eventsFor($employee),
            'tabs' => $this->tabs(),
        ];
    }

    /**
     * @return list<array{id: string, label: string}>
     */
    public function tabs(): array
    {
        return [
            ['id' => 'overview', 'label' => __('Overview')],
            ['id' => 'attendance', 'label' => __('Attendance')],
            ['id' => 'leave', 'label' => __('Leave')],
            ['id' => 'compensation', 'label' => __('Compensation')],
            ['id' => 'payroll', 'label' => __('Payroll')],
            ['id' => 'documents', 'label' => __('Documents')],
            ['id' => 'performance', 'label' => __('Performance')],
            ['id' => 'training', 'label' => __('Training')],
            ['id' => 'assets', 'label' => __('Assets')],
            ['id' => 'exit', 'label' => __('Exit')],
            ['id' => 'timeline', 'label' => __('Timeline')],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function overview(Employee $employee): array
    {
        $completeness = app(EmployeeProfileCompletenessService::class);

        return [
            'employee_number' => $employee->employee_number,
            'name' => $employee->full_name,
            'department' => $employee->department?->name,
            'branch' => $employee->branch?->name,
            'job_title' => $employee->jobTitle?->title ?? $employee->designation,
            'employment_status' => $employee->employment_status?->value,
            'is_suspended' => $employee->employment_status === EmploymentStatus::Suspended,
            'access_restricted' => in_array($employee->employment_status, [
                EmploymentStatus::Suspended,
                EmploymentStatus::Terminated,
            ], true) || ! $employee->is_active,
            'hire_date' => $employee->hire_date,
            'date_of_birth' => $employee->date_of_birth,
            'gender' => $employee->gender?->value,
            'email' => $employee->email,
            'phone' => $employee->phone,
            'address' => $employee->address,
            'national_id' => $employee->national_id,
            'kra_pin' => $employee->kra_pin,
            'nssf_number' => $employee->nssf_number,
            'nhif_number' => $employee->nhif_number,
            'bank_name' => $employee->bank_name,
            'bank_account_number' => $employee->bank_account_number,
            'bank_branch_code' => $employee->bank_branch_code,
            'emergency_contact_name' => $employee->emergency_contact_name,
            'emergency_contact_phone' => $employee->emergency_contact_phone,
            'next_of_kin_name' => $employee->next_of_kin_name,
            'next_of_kin_phone' => $employee->next_of_kin_phone,
            'next_of_kin_relationship' => $employee->next_of_kin_relationship,
            'gross_salary' => $employee->compensation?->grossComponents(),
            'payroll_profile_complete' => $completeness->isPayrollReady($employee),
            'missing_payroll_fields' => $completeness->missingForPayroll($employee),
            'missing_recommended_fields' => $completeness->missingRecommended($employee),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function attendanceTab(int $companyId, int $employeeId): array
    {
        $start = now()->startOfMonth();
        $end = now()->endOfMonth();

        $records = AttendanceRecord::query()
            ->where('employee_id', $employeeId)
            ->whereBetween('attendance_date', [$start->toDateString(), $end->toDateString()])
            ->orderByDesc('attendance_date')
            ->get();

        $lateCount = $records->filter(fn ($r) => $r->status === AttendanceStatus::Late || $r->late_minutes > 0)->count();
        $absentCount = $records->where('status', AttendanceStatus::Absent)->count();
        $overtimeHours = round((float) $records->sum('overtime_hours'), 1);
        $presentCount = $records->whereIn('status', [
            AttendanceStatus::Present,
            AttendanceStatus::Late,
            AttendanceStatus::HalfDay,
        ])->count();

        return [
            'summary' => [
                'present' => $presentCount,
                'late' => $lateCount,
                'absent' => $absentCount,
                'overtime_hours' => $overtimeHours,
            ],
            'records' => $this->attendance->paginateRegister($companyId, [
                'employee_id' => $employeeId,
                'date_from' => $start->toDateString(),
                'date_to' => $end->toDateString(),
            ], 15),
            'calendar_month' => $this->leaveCalendar->monthGrid(
                $companyId,
                (int) now()->year,
                (int) now()->month,
                ['employee_id' => $employeeId],
            ),
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    protected function leaveTab(int $companyId, Employee $employee, array $filters): array
    {
        $balances = $employee->leaveBalances()->with('leaveType')->get()->map(function ($balance) {
            return [
                'leave_type' => $balance->leaveType?->name,
                'available' => $balance->available(),
                'taken' => (float) $balance->taken,
                'pending' => (float) $balance->pending,
            ];
        });

        return [
            'balances' => $balances,
            'history' => $this->leaveRequests->paginate($companyId, $filters, 10),
            'pending' => $employee->leaveRequests()
                ->whereIn('status', [
                    LeaveRequestStatus::Submitted->value,
                    LeaveRequestStatus::SupervisorApproved->value,
                ])
                ->with('leaveType')
                ->latest()
                ->get(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function compensationTab(Employee $employee, int $companyId): array
    {
        return [
            'active' => $employee->compensation,
            'history' => $this->compensation->historyForEmployee($employee),
            'salary_changes' => $this->compensationAudit->salaryHistoryForEmployee($employee->id),
            'allowances' => $employee->payrollAllowances,
            'deductions' => $employee->payrollDeductions,
            'allowance_definitions' => \App\Models\Hr\CompensationAllowanceDefinition::query()
                ->where('company_id', $companyId)->where('is_active', true)->orderBy('name')->get(),
            'deduction_definitions' => \App\Models\Hr\CompensationDeductionDefinition::query()
                ->where('company_id', $companyId)->where('is_active', true)->orderBy('name')->get(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function payrollTab(Employee $employee): array
    {
        $payslips = $employee->payslips()->with('payrollRun')->orderByDesc('created_at')->limit(24)->get();

        return [
            'payslips' => $payslips,
            'runs' => $payslips->pluck('payrollRun')->filter()->unique('id')->values(),
            'net_trend' => $payslips->sortBy('created_at')->map(fn ($p) => [
                'period' => $p->payrollRun?->period_end?->format('M Y') ?? $p->created_at?->format('M Y'),
                'net' => (float) $p->net_pay,
            ])->values(),
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    protected function documentsTab(int $companyId, array $filters): array
    {
        $all = $this->documents->paginate($companyId, $filters, 50);

        return [
            'all' => $all,
            'contracts' => $all->getCollection()->filter(fn ($d) => $d->category === EmployeeDocumentCategory::Contract),
            'certificates' => $all->getCollection()->filter(fn ($d) => $d->category === EmployeeDocumentCategory::Certificate),
            'id_documents' => $all->getCollection()->filter(fn ($d) => in_array($d->category, [
                EmployeeDocumentCategory::IdCopy,
                EmployeeDocumentCategory::KraPin,
                EmployeeDocumentCategory::NssfRecord,
                EmployeeDocumentCategory::ShifRecord,
            ], true)),
            'hr_files' => $all->getCollection()->filter(fn ($d) => in_array($d->category, [
                EmployeeDocumentCategory::Cv,
                EmployeeDocumentCategory::WarningLetter,
                EmployeeDocumentCategory::PerformanceReview,
                EmployeeDocumentCategory::ExitDocument,
            ], true)),
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    protected function performanceTab(int $companyId, Employee $employee, array $filters): array
    {
        $periodStart = now()->startOfMonth()->subMonths(2);
        $periodEnd = now()->endOfMonth();

        return [
            'reviews' => $this->performance->paginate($companyId, $filters, 10),
            'targets' => $employee->salesTargets()->orderByDesc('period_end')->limit(6)->get(),
            'kpis' => $this->kpis->calculate($employee, $periodStart, $periodEnd),
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    protected function trainingTab(int $companyId, int $employeeId, array $filters): array
    {
        $assignments = $this->training->paginate($companyId, $filters, 15);
        $expiring = $assignments->getCollection()->filter(function ($assignment) {
            return $assignment->certificate_expires_at
                && $assignment->certificate_expires_at->lte(now()->addDays(60));
        });

        return [
            'assignments' => $assignments,
            'skills' => $this->training->skillsMatrix($companyId, $employeeId),
            'expiring_certificates' => $expiring,
            'completed' => $assignments->getCollection()->where('status', TrainingAssignmentStatus::Completed),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function assetsTab(int $employeeId): array
    {
        $issued = FixedAsset::query()
            ->where('assigned_to_employee_id', $employeeId)
            ->with(['category', 'branch'])
            ->get();

        $handovers = AssetHandover::query()
            ->where(function ($query) use ($employeeId) {
                $query->where('from_employee_id', $employeeId)
                    ->orWhere('to_employee_id', $employeeId);
            })
            ->with(['asset', 'fromEmployee', 'toEmployee'])
            ->orderByDesc('handover_date')
            ->limit(20)
            ->get();

        $returned = $handovers->filter(fn ($h) => $h->from_employee_id === $employeeId && $h->received_date);
        $pendingReturns = $issued->filter(fn ($a) => $a->custody_status?->value === 'assigned');

        return [
            'issued' => $issued,
            'returned' => $returned,
            'pending_returns' => $pendingReturns,
            'handovers' => $handovers,
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    protected function exitTab(int $companyId, Employee $employee, array $filters): array
    {
        $records = $this->exits->paginate($companyId, $filters, 10);
        $active = $employee->exits()->with('clearances')->latest()->first();

        return [
            'records' => $records,
            'active' => $active,
            'final_dues' => $active
                ? $this->exitDues->calculate($employee, Carbon::parse($active->last_working_date))
                : null,
        ];
    }

    protected function resolveSupervisor(Employee $employee): ?Employee
    {
        $reportsToTitleId = $employee->jobTitle?->reports_to_job_title_id;

        if (! $reportsToTitleId) {
            return null;
        }

        return Employee::query()
            ->where('company_id', $employee->company_id)
            ->where('job_title_id', $reportsToTitleId)
            ->where('is_active', true)
            ->when($employee->branch_id, fn ($q) => $q->where('branch_id', $employee->branch_id))
            ->first();
    }
}
