<?php

namespace App\Support\Hr;

use App\Enums\EmployeeDocumentCategory;
use App\Enums\RecruitmentPipelineStage;
use App\Enums\TrainingAssignmentStatus;
use App\Models\Hr\EmployeeDocument;
use App\Models\Hr\EmployeeTrainingAssignment;
use App\Models\Hr\JobApplication;
use App\Models\Hr\PerformanceReview;
use App\Support\Reports\HrReportQueries;
use App\Support\Reports\HrReportScope;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

class HrDashboardIntelligenceService
{
    public function __construct(
        protected AttendanceService $attendance,
        protected LeaveRequestService $leave,
        protected HrReportQueries $reportQueries,
        protected HrWorkforceIntelligenceService $workforce,
        protected RecruitmentDashboardService $recruitment,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function snapshot(int $companyId): array
    {
        $scope = $this->defaultScope($companyId);
        $kpiScope = $this->defaultKpiScope($companyId);
        $attendanceToday = $this->attendance->dashboardMetrics($companyId);
        $leaveToday = $this->leave->dashboardStats($companyId);

        $payroll = $this->reportQueries->payrollCostSummary($scope);
        $movement = $this->reportQueries->movementSummary($scope);
        $headcount = $this->reportQueries->headcountSummary($scope);
        $training = $this->reportQueries->trainingComplianceSummary($scope);
        $overtimeHours = $this->reportQueries->overtimeSummary($scope);

        return [
            'kpis' => $this->kpis(
                $companyId,
                $attendanceToday,
                $leaveToday,
                $payroll,
                $headcount,
                $movement,
                $training,
                $overtimeHours,
                $kpiScope,
            ),
            'trends' => $this->trends($companyId),
            'widgets' => $this->widgets($companyId, $kpiScope, $training),
        ];
    }

    /**
     * @param  array<string, int|float>  $attendanceToday
     * @param  array<string, mixed>  $leaveToday
     * @param  array<string, float|int>  $payroll
     * @param  array<string, int>  $headcount
     * @param  array<string, int>  $movement
     * @param  array<string, mixed>  $training
     * @param  array<string, float|int>  $overtimeHours
     * @return list<array{key: string, label: string, value: string, icon: string}>
     */
    protected function kpis(
        int $companyId,
        array $attendanceToday,
        array $leaveToday,
        array $payroll,
        array $headcount,
        array $movement,
        array $training,
        array $overtimeHours,
        HrKpiScope $kpiScope,
    ): array {
        $overtimeCost = $this->estimateOvertimeCost($companyId, $kpiScope);
        $attrition = $headcount['active'] > 0
            ? round(($movement['exits'] / $headcount['active']) * 100, 1)
            : 0.0;

        $pipeline = $this->recruitmentPipelineSummary($companyId);
        $contractExpiry = $this->contractExpiryCount($companyId);
        $trainingDue = $this->trainingDueCount($companyId);

        return [
            ['key' => 'total_employees', 'label' => __('Total Employees'), 'value' => (string) $headcount['active'], 'icon' => 'users'],
            ['key' => 'present_today', 'label' => __('Present Today'), 'value' => (string) $attendanceToday['present_today'], 'icon' => 'check-circle'],
            ['key' => 'on_leave', 'label' => __('Employees On Leave'), 'value' => (string) $leaveToday['on_leave_today'], 'icon' => 'calendar'],
            ['key' => 'attendance_percent', 'label' => __('Attendance %'), 'value' => $attendanceToday['attendance_percent'].'%', 'icon' => 'chart-pie'],
            ['key' => 'payroll_cost', 'label' => __('Payroll Cost'), 'value' => number_format($payroll['net'], 0), 'icon' => 'cash'],
            ['key' => 'overtime_cost', 'label' => __('Overtime Cost'), 'value' => number_format($overtimeCost, 0), 'icon' => 'clock'],
            ['key' => 'training_due', 'label' => __('Training Due'), 'value' => (string) $trainingDue, 'icon' => 'book-open'],
            ['key' => 'contract_expiry', 'label' => __('Contract Expiry'), 'value' => (string) $contractExpiry, 'icon' => 'document-text'],
            ['key' => 'attrition_rate', 'label' => __('Attrition Rate'), 'value' => $attrition.'%', 'icon' => 'switch-horizontal'],
            ['key' => 'recruitment_pipeline', 'label' => __('Recruitment Pipeline'), 'value' => (string) $pipeline['active'], 'icon' => 'user-add'],
            ['key' => 'overtime_hours', 'label' => __('Overtime Hours'), 'value' => number_format($overtimeHours['total_hours'], 1), 'icon' => 'clock', 'hidden' => true],
            ['key' => 'training_compliance_rate', 'label' => __('Training Compliance'), 'value' => $training['compliance_rate'].'%', 'icon' => 'book-open', 'hidden' => true],
        ];
    }

    /**
     * @return array<string, list<array{label: string, value: float, percent: int}>>
     */
    public function trends(int $companyId): array
    {
        $attendance = [];
        $payroll = [];
        $leave = [];
        $headcount = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $from = $month->copy()->startOfMonth()->toDateString();
            $to = $month->copy()->endOfMonth()->toDateString();
            $label = $month->format('M');
            $scope = new HrReportScope($companyId, null, $from, $to);

            $attendanceSummary = $this->reportQueries->attendanceSummary($scope);
            $payrollSummary = $this->reportQueries->payrollCostSummary($scope);
            $leaveSummary = $this->reportQueries->leaveUtilizationSummary($scope);
            $headcountSummary = $this->reportQueries->headcountSummary($scope);

            $attendance[] = ['label' => $label, 'value' => (float) $attendanceSummary['attendance_rate']];
            $payroll[] = ['label' => $label, 'value' => (float) $payrollSummary['net']];
            $leave[] = ['label' => $label, 'value' => (float) $leaveSummary['days_used']];
            $headcount[] = ['label' => $label, 'value' => (float) $headcountSummary['active']];
        }

        return [
            'attendance' => $this->chartPoints($attendance),
            'payroll' => $this->chartPoints($payroll),
            'leave' => $this->chartPoints($leave),
            'headcount' => $this->chartPoints($headcount),
        ];
    }

    /**
     * @param  array<string, mixed>  $training
     * @return array<string, mixed>
     */
    public function widgets(int $companyId, ?HrKpiScope $kpiScope = null, ?array $training = null): array
    {
        $kpiScope ??= $this->defaultKpiScope($companyId);
        $scope = $kpiScope->toReportScope();
        $training ??= $this->reportQueries->trainingComplianceSummary($scope);

        return [
            'performance_distribution' => $this->workforce->performanceRatingDistribution($kpiScope),
            'department_headcount' => $this->reportQueries->headcountByDepartment($scope),
            'performance_heatmap' => $this->performanceHeatmap($companyId, $kpiScope),
            'training_compliance' => [
                'rate' => $training['compliance_rate'],
                'assigned' => $training['assigned'],
                'completed' => $training['completed'],
                'overdue' => $training['overdue'],
            ],
            'recruitment_pipeline' => $this->recruitmentPipelineSummary($companyId),
        ];
    }

    /**
     * @return list<array{label: string, value: float, percent: int}>
     */
    protected function chartPoints(array $points): array
    {
        $max = max(1, ...array_column($points, 'value'));

        return array_map(fn (array $point) => [
            'label' => $point['label'],
            'value' => $point['value'],
            'percent' => (int) round(($point['value'] / $max) * 100),
        ], $points);
    }

    /**
     * @return list<array{department: string, attendance: float, performance: float, headcount: int, intensity: int}>
     */
    protected function performanceHeatmap(int $companyId, HrKpiScope $scope): array
    {
        if (! Schema::hasTable('performance_reviews') || ! Schema::hasTable('attendance_records')) {
            return [];
        }

        $departments = $this->reportQueries->headcountByDepartment($scope->toReportScope());
        $heatmap = [];

        foreach ($departments as $row) {
            $departmentName = (string) $row[0];
            $deptHeadcount = (int) $row[1];

            $departmentId = \App\Models\Department::query()
                ->where('company_id', $companyId)
                ->where('name', $departmentName)
                ->value('id');

            if (! $departmentId) {
                continue;
            }

            $deptScope = new HrKpiScope(
                $companyId,
                $scope->branchId,
                $scope->fromDate,
                $scope->toDate,
                departmentId: $departmentId,
            );

            $attendanceRate = $this->departmentAttendanceRate($deptScope);
            $performance = $this->departmentPerformanceScore($companyId, $departmentId, $scope);

            $intensity = (int) round(($attendanceRate + $performance) / 2);

            $heatmap[] = [
                'department' => $departmentName,
                'attendance' => $attendanceRate,
                'performance' => $performance,
                'headcount' => $deptHeadcount,
                'intensity' => min(100, max(0, $intensity)),
            ];
        }

        return $heatmap;
    }

    protected function departmentAttendanceRate(HrKpiScope $scope): float
    {
        $summary = $this->reportQueries->attendanceSummary($scope->toReportScope());

        return (float) $summary['attendance_rate'];
    }

    protected function departmentPerformanceScore(int $companyId, int $departmentId, HrKpiScope $scope): float
    {
        $avg = PerformanceReview::query()
            ->where('company_id', $companyId)
            ->whereHas('employee', fn ($q) => $q->where('department_id', $departmentId))
            ->whereDate('period_end', '>=', $scope->fromDate)
            ->whereDate('period_end', '<=', $scope->toDate)
            ->avg('composite_score');

        return round((float) $avg, 1);
    }

    /**
     * @return array{active: int, stages: array<string, int>}
     */
    protected function recruitmentPipelineSummary(int $companyId): array
    {
        if (! Schema::hasTable('job_applications')) {
            return ['active' => 0, 'stages' => []];
        }

        $stages = $this->recruitment->pipelineCounts($companyId);
        $active = JobApplication::query()
            ->where('company_id', $companyId)
            ->whereNotIn('stage', [
                RecruitmentPipelineStage::Rejected->value,
                RecruitmentPipelineStage::Hired->value,
            ])
            ->count();

        return ['active' => $active, 'stages' => $stages];
    }

    protected function contractExpiryCount(int $companyId): int
    {
        if (! Schema::hasTable('employee_documents')) {
            return 0;
        }

        return EmployeeDocument::query()
            ->forTenant()
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->where('category', EmployeeDocumentCategory::Contract->value)
            ->expiringSoon(60)
            ->count();
    }

    protected function trainingDueCount(int $companyId): int
    {
        if (! Schema::hasTable('employee_training_assignments')) {
            return 0;
        }

        return EmployeeTrainingAssignment::query()
            ->forTenant()
            ->where('company_id', $companyId)
            ->whereIn('status', [
                TrainingAssignmentStatus::Assigned->value,
                TrainingAssignmentStatus::InProgress->value,
            ])
            ->where(function ($q) {
                $q->whereDate('due_date', '<=', now())
                    ->orWhere(function ($inner) {
                        $inner->expiringCertificates(30);
                    });
            })
            ->count();
    }

    protected function estimateOvertimeCost(int $companyId, HrKpiScope $scope): float
    {
        $kpis = $this->workforce->kpis($scope);

        foreach ($kpis as $kpi) {
            if ($kpi['label'] === __('Overtime Cost')) {
                return (float) str_replace(',', '', $kpi['value']);
            }
        }

        return 0.0;
    }

    protected function defaultScope(int $companyId): HrReportScope
    {
        return new HrReportScope(
            $companyId,
            null,
            now()->startOfYear()->toDateString(),
            now()->toDateString(),
        );
    }

    protected function defaultKpiScope(int $companyId): HrKpiScope
    {
        return new HrKpiScope(
            $companyId,
            null,
            now()->startOfYear()->toDateString(),
            now()->toDateString(),
        );
    }
}
