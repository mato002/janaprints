<?php

namespace App\Support\Hr;

use App\Enums\AttendanceStatus;
use App\Enums\LeaveRequestStatus;
use App\Enums\PerformanceRating;
use App\Enums\TrainingAssignmentStatus;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Hr\AttendanceRecord;
use App\Models\Hr\EmployeeCompensation;
use App\Models\Hr\PerformanceReview;
use App\Models\JobTitle;
use App\Support\Reports\HrReportQueries;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class HrWorkforceIntelligenceService
{
    public function __construct(
        protected HrReportQueries $reportQueries,
        protected PerformanceKpiCalculationService $performanceKpi,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function dashboard(HrKpiScope $scope): array
    {
        $kpis = $this->kpis($scope);

        return [
            'kpis' => $kpis,
            'dimension' => $scope->dimension,
            'dimension_rows' => $this->dimensionBreakdown($scope),
            'rankings' => $this->rankings($scope),
            'rating_distribution' => $this->performanceRatingDistribution($scope),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function summaryCards(HrKpiScope $scope): array
    {
        $kpis = $this->kpis($scope);

        return collect($kpis)->map(fn (array $kpi) => [
            'name' => $kpi['label'],
            'value' => $kpi['value'],
            'hint' => $kpi['hint'],
            'status' => $kpi['status'],
            'status_label' => $this->statusLabel($kpi['status']),
            'source' => 'HR',
        ])->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function kpis(HrKpiScope $scope): array
    {
        $reportScope = $scope->toReportScope();
        $attendance = $this->reportQueries->attendanceSummary($reportScope);
        $absenteeism = $this->reportQueries->absenteeismSummary($reportScope);
        $leave = $this->reportQueries->leaveUtilizationSummary($reportScope);
        $payroll = $this->reportQueries->payrollCostSummary($reportScope);
        $overtime = $this->reportQueries->overtimeSummary($reportScope);
        $headcount = $this->reportQueries->headcountSummary($reportScope);
        $movement = $this->reportQueries->movementSummary($reportScope);
        $training = $this->reportQueries->trainingComplianceSummary($reportScope);
        $overtimeCost = $this->overtimeCost($scope);
        $turnover = $this->turnoverRate($scope, $headcount['total'], $movement['exits']);
        $leavePercent = $this->leaveUtilizationPercent($scope, $headcount['active'], $leave['days_used']);
        $deptPerformance = $this->averageDepartmentPerformance($scope);

        return [
            $this->kpi(__('Attendance %'), $attendance['attendance_rate'].'%', __('Present / records in period'), $this->rateStatus($attendance['attendance_rate'], 90, 75)),
            $this->kpi(__('Leave %'), $leavePercent.'%', __('Leave days used vs capacity'), $this->rateStatus(100 - $leavePercent, 80, 60)),
            $this->kpi(__('Absenteeism %'), $absenteeism['rate'].'%', __('Absent days / records'), $this->rateStatus(100 - $absenteeism['rate'], 95, 85)),
            $this->kpi(__('Payroll Cost'), number_format($payroll['net'], 2), __('Net payroll in period'), 'good'),
            $this->kpi(__('Overtime Cost'), number_format($overtimeCost, 2), __('Estimated OT cost'), $overtimeCost > 0 ? 'watch' : 'good'),
            $this->kpi(__('Headcount'), (string) $headcount['active'], __('Active employees'), 'good'),
            $this->kpi(__('Employee Turnover'), $turnover.'%', __('Exits / headcount'), $this->rateStatus(100 - $turnover, 90, 75)),
            $this->kpi(__('Training Compliance'), $training['compliance_rate'].'%', __('Completed assignments'), $this->rateStatus($training['compliance_rate'], 90, 70)),
            $this->kpi(__('Department Performance'), $deptPerformance.'%', __('Avg composite score'), $this->rateStatus($deptPerformance, 75, 60)),
            $this->kpi(__('Performance Reviews'), (string) $this->reviewCount($scope), __('Reviews in period'), 'good'),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function dimensionBreakdown(HrKpiScope $scope): array
    {
        return match ($scope->dimension) {
            'branch' => $this->breakdownByBranch($scope),
            'department' => $this->breakdownByDepartment($scope),
            'supervisor' => $this->breakdownBySupervisor($scope),
            default => [$this->companyRow($scope)],
        };
    }

    /**
     * @return array<string, mixed>
     */
    public function rankings(HrKpiScope $scope): array
    {
        return [
            'top_attendance' => [
                'title' => __('Top Attendance'),
                'columns' => [__('Employee'), __('Attendance %')],
                'rows' => $this->topAttendance($scope, 10),
            ],
            'most_overtime' => [
                'title' => __('Most Overtime'),
                'columns' => [__('Employee'), __('Hours')],
                'rows' => $this->mostOvertime($scope, 10),
            ],
            'top_performers' => [
                'title' => __('Top Performers'),
                'columns' => [__('Employee'), __('Score')],
                'rows' => $this->topPerformers($scope, 10),
            ],
            'top_departments' => [
                'title' => __('Departments'),
                'columns' => [__('Department'), __('Attendance %'), __('Headcount')],
                'rows' => $this->topDepartments($scope, 10),
            ],
        ];
    }

    /**
     * @return list<array{label: string, count: int}>
     */
    public function performanceRatingDistribution(HrKpiScope $scope): array
    {
        if (! Schema::hasTable('performance_reviews')) {
            return [];
        }

        $counts = PerformanceReview::query()
            ->where('company_id', $scope->companyId)
            ->whereNotNull('rating')
            ->where(function (Builder $q) use ($scope) {
                $q->whereDate('reviewed_at', '>=', $scope->fromDate)
                    ->whereDate('reviewed_at', '<=', $scope->toDate);
            })
            ->when($scope->branchId, fn ($q) => $q->whereHas('employee', fn ($e) => $e->where('branch_id', $scope->branchId)))
            ->when($scope->departmentId, fn ($q) => $q->whereHas('employee', fn ($e) => $e->where('department_id', $scope->departmentId)))
            ->select('rating', DB::raw('COUNT(*) as total'))
            ->groupBy('rating')
            ->pluck('total', 'rating');

        return collect(PerformanceRating::cases())->map(fn (PerformanceRating $rating) => [
            'label' => $rating->label(),
            'count' => (int) ($counts[$rating->value] ?? 0),
        ])->all();
    }

    /**
     * @return list<array<int, string|int|float>>
     */
    public function exportRows(HrKpiScope $scope): array
    {
        $rows = [];
        $rows[] = [__('Metric'), __('Value')];
        foreach ($this->kpis($scope) as $kpi) {
            $rows[] = [$kpi['label'], $kpi['value']];
        }
        $rows[] = ['', ''];

        $rows[] = [__('Dimension Breakdown'), ''];
        $rows[] = [__('Name'), __('Attendance %'), __('Headcount'), __('Payroll')];
        foreach ($this->dimensionBreakdown($scope) as $row) {
            $rows[] = [
                $row['name'],
                $row['attendance_percent'].'%',
                (string) $row['headcount'],
                number_format($row['payroll_cost'], 2),
            ];
        }

        $rows[] = ['', ''];
        foreach ($this->rankings($scope) as $block) {
            $rows[] = [$block['title'], ''];
            $rows[] = $block['columns'];
            foreach ($block['rows'] as $row) {
                $rows[] = array_map(fn ($cell) => (string) $cell, (array) $row);
            }
            $rows[] = ['', ''];
        }

        return $rows;
    }

    protected function companyRow(HrKpiScope $scope): array
    {
        $reportScope = $scope->toReportScope();
        $attendance = $this->reportQueries->attendanceSummary($reportScope);
        $headcount = $this->reportQueries->headcountSummary($reportScope);
        $payroll = $this->reportQueries->payrollCostSummary($reportScope);

        return [
            'name' => __('Company'),
            'attendance_percent' => $attendance['attendance_rate'],
            'headcount' => $headcount['active'],
            'payroll_cost' => $payroll['net'],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function breakdownByBranch(HrKpiScope $scope): array
    {
        $branches = Branch::query()
            ->where('company_id', $scope->companyId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return $branches->map(function (Branch $branch) use ($scope) {
            $branchScope = new HrKpiScope(
                $scope->companyId,
                $branch->id,
                $scope->fromDate,
                $scope->toDate,
                $scope->dimension,
                $scope->employeeId,
                $scope->departmentId,
                $scope->jobTitleId,
                $scope->supervisorJobTitleId,
                $scope->status,
            );

            $attendance = $this->reportQueries->attendanceSummary($branchScope->toReportScope());
            $headcount = $this->reportQueries->headcountSummary($branchScope->toReportScope());
            $payroll = $this->reportQueries->payrollCostSummary($branchScope->toReportScope());

            return [
                'name' => $branch->name,
                'attendance_percent' => $attendance['attendance_rate'],
                'headcount' => $headcount['active'],
                'payroll_cost' => $payroll['net'],
            ];
        })->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function breakdownByDepartment(HrKpiScope $scope): array
    {
        $departments = Department::query()
            ->where('company_id', $scope->companyId)
            ->orderBy('name')
            ->get();

        return $departments->map(function (Department $department) use ($scope) {
            $deptScope = new HrKpiScope(
                $scope->companyId,
                $scope->branchId,
                $scope->fromDate,
                $scope->toDate,
                $scope->dimension,
                $scope->employeeId,
                $department->id,
                $scope->jobTitleId,
                $scope->supervisorJobTitleId,
                $scope->status,
            );

            $attendance = $this->reportQueries->attendanceSummary($deptScope->toReportScope());
            $headcount = $this->reportQueries->headcountSummary($deptScope->toReportScope());
            $payroll = $this->reportQueries->payrollCostSummary($deptScope->toReportScope());

            return [
                'name' => $department->name,
                'attendance_percent' => $attendance['attendance_rate'],
                'headcount' => $headcount['active'],
                'payroll_cost' => $payroll['net'],
            ];
        })->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function breakdownBySupervisor(HrKpiScope $scope): array
    {
        $managerTitles = JobTitle::query()
            ->where('company_id', $scope->companyId)
            ->where('is_active', true)
            ->whereIn('id', JobTitle::query()
                ->where('company_id', $scope->companyId)
                ->whereNotNull('reports_to_job_title_id')
                ->distinct()
                ->pluck('reports_to_job_title_id'))
            ->orderBy('title')
            ->get();

        if ($managerTitles->isEmpty()) {
            return [[
                'name' => __('No supervisor hierarchy'),
                'attendance_percent' => 0,
                'headcount' => 0,
                'payroll_cost' => 0,
            ]];
        }

        return $managerTitles->map(function (JobTitle $managerTitle) use ($scope) {
            $teamJobTitleIds = JobTitle::query()
                ->where('company_id', $scope->companyId)
                ->where('reports_to_job_title_id', $managerTitle->id)
                ->pluck('id');

            $headcount = Employee::query()
                ->where('company_id', $scope->companyId)
                ->where('is_active', true)
                ->whereIn('job_title_id', $teamJobTitleIds)
                ->when($scope->branchId, fn ($q) => $q->where('branch_id', $scope->branchId))
                ->count();

            $attendancePercent = $this->teamAttendancePercent($scope, $teamJobTitleIds);
            $payrollCost = $this->teamPayrollCost($scope, $teamJobTitleIds);

            return [
                'name' => $managerTitle->title,
                'attendance_percent' => $attendancePercent,
                'headcount' => $headcount,
                'payroll_cost' => $payrollCost,
            ];
        })->all();
    }

    /**
     * @return list<array<int, string|float>>
     */
    protected function topAttendance(HrKpiScope $scope, int $limit): array
    {
        $start = Carbon::parse($scope->fromDate);
        $end = Carbon::parse($scope->toDate);

        return $this->employeeQuery($scope)
            ->get()
            ->map(fn (Employee $employee) => [
                'employee' => $employee,
                'percent' => $this->performanceKpi->attendancePercent($employee, $start, $end),
            ])
            ->sortByDesc('percent')
            ->take($limit)
            ->map(fn (array $row) => [$row['employee']->full_name, $row['percent'].'%'])
            ->values()
            ->all();
    }

    /**
     * @return list<array<int, string|float>>
     */
    protected function mostOvertime(HrKpiScope $scope, int $limit): array
    {
        if (! Schema::hasTable('attendance_records')) {
            return [];
        }

        return AttendanceRecord::query()
            ->where('company_id', $scope->companyId)
            ->whereDate('attendance_date', '>=', $scope->fromDate)
            ->whereDate('attendance_date', '<=', $scope->toDate)
            ->when($scope->branchId, fn ($q) => $q->where('branch_id', $scope->branchId))
            ->when($scope->departmentId, fn ($q) => $q->where('department_id', $scope->departmentId))
            ->select('employee_id', DB::raw('SUM(overtime_hours) as total_hours'))
            ->groupBy('employee_id')
            ->orderByDesc('total_hours')
            ->limit($limit)
            ->with('employee')
            ->get()
            ->map(fn ($row) => [
                $row->employee?->full_name ?? '—',
                round((float) $row->total_hours, 1),
            ])
            ->all();
    }

    /**
     * @return list<array<int, string|float>>
     */
    protected function topPerformers(HrKpiScope $scope, int $limit): array
    {
        $start = Carbon::parse($scope->fromDate);
        $end = Carbon::parse($scope->toDate);

        return $this->employeeQuery($scope)
            ->get()
            ->map(fn (Employee $employee) => [
                'employee' => $employee,
                'score' => $this->performanceKpi->calculate($employee, $start, $end)['composite_score'],
            ])
            ->sortByDesc('score')
            ->take($limit)
            ->map(fn (array $row) => [$row['employee']->full_name, $row['score']])
            ->values()
            ->all();
    }

    /**
     * @return list<array<int, string|int|float>>
     */
    protected function topDepartments(HrKpiScope $scope, int $limit): array
    {
        return collect($this->breakdownByDepartment($scope))
            ->sortByDesc('attendance_percent')
            ->take($limit)
            ->map(fn (array $row) => [
                $row['name'],
                $row['attendance_percent'].'%',
                $row['headcount'],
            ])
            ->values()
            ->all();
    }

    protected function overtimeCost(HrKpiScope $scope): float
    {
        if (! Schema::hasTable('attendance_records')) {
            return 0.0;
        }

        $records = AttendanceRecord::query()
            ->where('company_id', $scope->companyId)
            ->whereDate('attendance_date', '>=', $scope->fromDate)
            ->whereDate('attendance_date', '<=', $scope->toDate)
            ->when($scope->branchId, fn ($q) => $q->where('branch_id', $scope->branchId))
            ->when($scope->departmentId, fn ($q) => $q->where('department_id', $scope->departmentId))
            ->where('overtime_hours', '>', 0)
            ->get(['employee_id', 'overtime_hours']);

        if ($records->isEmpty()) {
            return 0.0;
        }

        $rates = EmployeeCompensation::query()
            ->where('company_id', $scope->companyId)
            ->where('is_active', true)
            ->whereIn('employee_id', $records->pluck('employee_id')->unique())
            ->pluck('basic_salary', 'employee_id');

        return round($records->sum(function ($record) use ($rates) {
            $hourly = ((float) ($rates[$record->employee_id] ?? 0)) / 176;

            return (float) $record->overtime_hours * $hourly * 1.5;
        }), 2);
    }

    protected function turnoverRate(HrKpiScope $scope, int $headcount, int $exits): float
    {
        if ($headcount <= 0) {
            return 0.0;
        }

        return round(($exits / $headcount) * 100, 1);
    }

    protected function leaveUtilizationPercent(HrKpiScope $scope, int $activeEmployees, float $daysUsed): float
    {
        if ($activeEmployees <= 0) {
            return 0.0;
        }

        $start = Carbon::parse($scope->fromDate);
        $end = Carbon::parse($scope->toDate);
        $workingDays = max(1, $this->workingDaysInPeriod($start, $end));
        $capacity = $activeEmployees * $workingDays;

        return round(min(100, ($daysUsed / $capacity) * 100), 1);
    }

    protected function averageDepartmentPerformance(HrKpiScope $scope): float
    {
        $start = Carbon::parse($scope->fromDate);
        $end = Carbon::parse($scope->toDate);
        $employees = $this->employeeQuery($scope)->get();

        if ($employees->isEmpty()) {
            return 0.0;
        }

        $avg = $employees->avg(fn (Employee $employee) => $this->performanceKpi->calculate($employee, $start, $end)['composite_score']);

        return round((float) $avg, 1);
    }

    protected function reviewCount(HrKpiScope $scope): int
    {
        if (! Schema::hasTable('performance_reviews')) {
            return 0;
        }

        return PerformanceReview::query()
            ->where('company_id', $scope->companyId)
            ->whereDate('reviewed_at', '>=', $scope->fromDate)
            ->whereDate('reviewed_at', '<=', $scope->toDate)
            ->count();
    }

    /**
     * @param  Collection<int, int>  $teamJobTitleIds
     */
    protected function teamAttendancePercent(HrKpiScope $scope, Collection $teamJobTitleIds): float
    {
        if (! Schema::hasTable('attendance_records') || $teamJobTitleIds->isEmpty()) {
            return 0.0;
        }

        $employeeIds = Employee::query()
            ->where('company_id', $scope->companyId)
            ->whereIn('job_title_id', $teamJobTitleIds)
            ->pluck('id');

        if ($employeeIds->isEmpty()) {
            return 0.0;
        }

        $query = AttendanceRecord::query()
            ->where('company_id', $scope->companyId)
            ->whereIn('employee_id', $employeeIds)
            ->whereDate('attendance_date', '>=', $scope->fromDate)
            ->whereDate('attendance_date', '<=', $scope->toDate);

        $total = (clone $query)->count();
        if ($total === 0) {
            return 0.0;
        }

        $present = (clone $query)->whereIn('status', [
            AttendanceStatus::Present->value,
            AttendanceStatus::Late->value,
        ])->count();

        return round(($present / $total) * 100, 1);
    }

    /**
     * @param  Collection<int, int>  $teamJobTitleIds
     */
    protected function teamPayrollCost(HrKpiScope $scope, Collection $teamJobTitleIds): float
    {
        if (! Schema::hasTable('payroll_payslips') || $teamJobTitleIds->isEmpty()) {
            return 0.0;
        }

        $employeeIds = Employee::query()
            ->where('company_id', $scope->companyId)
            ->whereIn('job_title_id', $teamJobTitleIds)
            ->pluck('id');

        if ($employeeIds->isEmpty()) {
            return 0.0;
        }

        $runIds = DB::table('payroll_runs')
            ->where('company_id', $scope->companyId)
            ->whereIn('status', ['approved', 'posted'])
            ->whereDate('period_start', '<=', $scope->toDate)
            ->whereDate('period_end', '>=', $scope->fromDate)
            ->pluck('id');

        if ($runIds->isEmpty()) {
            return 0.0;
        }

        return round((float) DB::table('payroll_payslips')
            ->whereIn('payroll_run_id', $runIds)
            ->whereIn('employee_id', $employeeIds)
            ->sum('net_pay'), 2);
    }

    /**
     * @return Builder<Employee>
     */
    protected function employeeQuery(HrKpiScope $scope): Builder
    {
        $query = Employee::query()
            ->where('company_id', $scope->companyId)
            ->where('is_active', true);

        if ($scope->branchId) {
            $query->where('branch_id', $scope->branchId);
        }

        if ($scope->departmentId) {
            $query->where('department_id', $scope->departmentId);
        }

        if ($scope->jobTitleId) {
            $query->where('job_title_id', $scope->jobTitleId);
        }

        if ($scope->employeeId) {
            $query->where('id', $scope->employeeId);
        }

        if ($scope->status) {
            $query->where('employment_status', $scope->status);
        }

        if ($scope->supervisorJobTitleId) {
            $teamIds = JobTitle::query()
                ->where('company_id', $scope->companyId)
                ->where('reports_to_job_title_id', $scope->supervisorJobTitleId)
                ->pluck('id');
            $query->whereIn('job_title_id', $teamIds);
        }

        return $query;
    }

    protected function workingDaysInPeriod(Carbon $start, Carbon $end): int
    {
        $days = 0;
        $cursor = $start->copy();

        while ($cursor->lte($end)) {
            if (! $cursor->isWeekend()) {
                $days++;
            }
            $cursor->addDay();
        }

        return $days;
    }

    /**
     * @return array{label: string, value: string, hint: string, status: string}
     */
    protected function kpi(string $label, string $value, string $hint, string $status): array
    {
        return compact('label', 'value', 'hint', 'status');
    }

    protected function rateStatus(float $value, float $good, float $watch): string
    {
        if ($value >= $good) {
            return 'good';
        }

        if ($value >= $watch) {
            return 'watch';
        }

        return 'critical';
    }

    protected function statusLabel(string $status): string
    {
        return match ($status) {
            'good' => __('Good'),
            'watch' => __('Watch'),
            'critical' => __('Critical'),
            default => __('Pending source'),
        };
    }
}
