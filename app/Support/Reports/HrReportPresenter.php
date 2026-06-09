<?php

namespace App\Support\Reports;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class HrReportPresenter
{
    public function __construct(
        protected HrReportScopeResolver $scopeResolver,
        protected HrReportQueries $queries,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function present(Request $request): array
    {
        $resolved = $this->scopeResolver->resolve($request);
        $scope = $resolved['scope'];
        $tab = $resolved['tab'];

        return [
            'title' => __(config('hr_reports.title', 'HR Reports')),
            'description' => __(config('hr_reports.description', 'Workforce analytics.')),
            'filters' => $resolved['filters'],
            'branches' => $resolved['branches'],
            'departments' => $resolved['departments'],
            'jobTitles' => $resolved['jobTitles'],
            'employees' => $resolved['employees'],
            'employmentStatuses' => $resolved['employmentStatuses'],
            'can_export' => $resolved['can_export'],
            'catalog' => $this->catalog(),
            'tabs' => $this->tabs(),
            'active_tab' => $tab,
            'tab_data' => $this->presentTab($scope, $tab),
            'export_url' => Route::has('admin.reports.hr.export')
                ? route('admin.reports.hr.export')
                : null,
            'print_url' => Route::has('admin.reports.hr.print')
                ? route('admin.reports.hr.print', $request->query())
                : null,
        ];
    }

    /**
     * @return list<array{group: string, reports: list<array{key: string, label: string}>}>
     */
    public function catalog(): array
    {
        return collect(config('hr_reports.tabs', []))
            ->map(fn (array $group, string $key) => [
                'group' => $group['label'] ?? $key,
                'reports' => collect($group['reports'] ?? [])
                    ->map(fn (string $label, string $reportKey) => [
                        'key' => $reportKey,
                        'label' => $label,
                    ])
                    ->values()
                    ->all(),
            ])
            ->values()
            ->all();
    }

    /**
     * @return list<array{key: string, label: string}>
     */
    public function tabs(): array
    {
        return collect(config('hr_reports.tabs', []))
            ->map(fn (array $group, string $key) => [
                'key' => $key,
                'label' => __($group['label'] ?? $key),
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    public function presentTab(HrReportScope $scope, string $tab): array
    {
        return match ($tab) {
            'attendance' => $this->attendanceTab($scope),
            'leave' => $this->leaveTab($scope),
            'payroll' => $this->payrollTab($scope),
            'workforce' => $this->workforceTab($scope),
            default => ['type' => 'placeholder', 'message' => __('Select a report tab.')],
        };
    }

    /**
     * @return array<string, mixed>
     */
    protected function attendanceTab(HrReportScope $scope): array
    {
        $summary = $this->queries->attendanceSummary($scope);
        $late = $this->queries->lateArrivalsSummary($scope);
        $absent = $this->queries->absenteeismSummary($scope);
        $overtime = $this->queries->overtimeSummary($scope);

        return [
            'type' => 'attendance',
            'summary' => [
                ['label' => __('Attendance Rate'), 'value' => $summary['attendance_rate'].'%'],
                ['label' => __('Present'), 'value' => (string) $summary['present']],
                ['label' => __('Absent'), 'value' => (string) $summary['absent']],
                ['label' => __('Late Arrivals'), 'value' => (string) $late['count']],
                ['label' => __('Overtime Hours'), 'value' => (string) $overtime['total_hours']],
                ['label' => __('Absenteeism Rate'), 'value' => $absent['rate'].'%'],
            ],
            'daily' => [
                'title' => __('Attendance Report'),
                'columns' => [__('Date'), __('Records'), __('Present'), __('Absent'), __('Late')],
                'rows' => $this->queries->attendanceByDay($scope),
            ],
            'departments' => [
                'title' => __('Attendance by Department'),
                'columns' => [__('Department'), __('Records'), __('Present'), __('Absent'), __('Late')],
                'rows' => $this->queries->attendanceByDepartment($scope),
            ],
            'late' => [
                'title' => __('Late Arrivals Report'),
                'columns' => [__('Employee'), __('Date'), __('Clock In'), __('Late (min)'), __('Department')],
                'rows' => $this->queries->lateArrivalsRows($scope),
            ],
            'absent' => [
                'title' => __('Absenteeism by Employee'),
                'columns' => [__('Employee'), __('Employee No.'), __('Absent Days')],
                'rows' => $this->queries->absenteeismByEmployee($scope),
            ],
            'absent_departments' => [
                'title' => __('Absenteeism by Department'),
                'columns' => [__('Department'), __('Absent Days')],
                'rows' => $this->queries->absenteeismByDepartment($scope),
            ],
            'overtime' => [
                'title' => __('Overtime Report'),
                'columns' => [__('Employee'), __('Date'), __('Scheduled'), __('Actual'), __('Overtime')],
                'rows' => $this->queries->overtimeRows($scope),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function leaveTab(HrReportScope $scope): array
    {
        $summary = $this->queries->leaveUtilizationSummary($scope);

        return [
            'type' => 'leave',
            'summary' => [
                ['label' => __('Days Used'), 'value' => (string) $summary['days_used']],
                ['label' => __('Approved Requests'), 'value' => (string) $summary['requests']],
                ['label' => __('Employees'), 'value' => (string) $summary['employees']],
            ],
            'by_type' => [
                'title' => __('Leave Utilization by Type'),
                'columns' => [__('Leave Type'), __('Days Used'), __('Requests')],
                'rows' => $this->queries->leaveUtilizationByType($scope),
            ],
            'by_employee' => [
                'title' => __('Leave Utilization by Employee'),
                'columns' => [__('Employee'), __('Days Used'), __('Requests')],
                'rows' => $this->queries->leaveUtilizationByEmployee($scope),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function payrollTab(HrReportScope $scope): array
    {
        $summary = $this->queries->payrollCostSummary($scope);

        return [
            'type' => 'payroll',
            'summary' => [
                ['label' => __('Gross Total'), 'value' => number_format($summary['gross'], 2)],
                ['label' => __('Net Total'), 'value' => number_format($summary['net'], 2)],
                ['label' => __('Deductions'), 'value' => number_format($summary['deductions'], 2)],
                ['label' => __('Payroll Runs'), 'value' => (string) $summary['runs']],
            ],
            'runs' => [
                'title' => __('Payroll Cost by Run'),
                'columns' => [__('Reference'), __('Period Start'), __('Period End'), __('Employees'), __('Gross'), __('Net')],
                'rows' => $this->queries->payrollCostByRun($scope),
            ],
            'departments' => [
                'title' => __('Payroll Cost by Department'),
                'columns' => [__('Department'), __('Employees'), __('Gross'), __('Net')],
                'rows' => $this->queries->payrollCostByDepartment($scope),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function workforceTab(HrReportScope $scope): array
    {
        $headcount = $this->queries->headcountSummary($scope);
        $movement = $this->queries->movementSummary($scope);
        $contracts = $this->queries->contractExpirySummary($scope);
        $training = $this->queries->trainingComplianceSummary($scope);

        return [
            'type' => 'workforce',
            'summary' => [
                ['label' => __('Total Headcount'), 'value' => (string) $headcount['total']],
                ['label' => __('Active'), 'value' => (string) $headcount['active']],
                ['label' => __('Hires'), 'value' => (string) $movement['hires']],
                ['label' => __('Exits'), 'value' => (string) $movement['exits']],
                ['label' => __('Contracts Expiring'), 'value' => (string) $contracts['expiring']],
                ['label' => __('Training Compliance'), 'value' => $training['compliance_rate'].'%'],
            ],
            'headcount' => [
                'title' => __('Department Headcount Report'),
                'columns' => [__('Department'), __('Headcount'), __('Active')],
                'rows' => $this->queries->headcountByDepartment($scope),
            ],
            'movement' => [
                'title' => __('Employee Movement Report'),
                'columns' => [__('Employee'), __('Event'), __('Date'), __('Department')],
                'rows' => $this->queries->movementRows($scope),
            ],
            'contracts' => [
                'title' => __('Contract Expiry Report'),
                'columns' => [__('Employee'), __('Document'), __('Expiry'), __('Status')],
                'rows' => $this->queries->contractExpiryRows($scope),
            ],
            'training' => [
                'title' => __('Training Compliance Report'),
                'columns' => [__('Employee'), __('Program'), __('Status'), __('Due Date')],
                'rows' => $this->queries->trainingComplianceRows($scope),
            ],
        ];
    }
}
