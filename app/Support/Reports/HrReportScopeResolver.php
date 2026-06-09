<?php

namespace App\Support\Reports;

use App\Enums\EmploymentStatus;
use App\Models\Department;
use App\Support\Reports\Concerns\ResolvesGovernedReportBranchScope;
use App\Models\Employee;
use App\Models\JobTitle;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class HrReportScopeResolver
{
    use ResolvesGovernedReportBranchScope;

    /**
     * @return array{
     *     scope: HrReportScope,
     *     branches: Collection<int, Branch>,
     *     departments: Collection<int, Department>,
     *     jobTitles: Collection<int, JobTitle>,
     *     employees: Collection<int, Employee>,
     *     employmentStatuses: list<array{value: string, label: string}>,
     *     can_export: bool,
     *     filters: array<string, mixed>,
     *     tab: string
     * }
     */
    public function resolve(Request $request): array
    {
        $companyId = tenant()->companyId() ?? $request->user()?->company_id;

        if (! $companyId) {
            abort(403, __('Company context is required.'));
        }

        $branchId = $this->resolveGovernedBranchId($request, defaultFromTenant: false);

        $scope = new HrReportScope(
            companyId: (int) $companyId,
            branchId: $branchId,
            fromDate: $request->input('from_date', now()->startOfMonth()->toDateString()),
            toDate: $request->input('to_date', now()->toDateString()),
            employeeId: $request->filled('employee_id') ? (int) $request->input('employee_id') : null,
            departmentId: $request->filled('department_id') ? (int) $request->input('department_id') : null,
            jobTitleId: $request->filled('job_title_id') ? (int) $request->input('job_title_id') : null,
            status: $request->filled('status') ? (string) $request->input('status') : null,
        );

        $tab = (string) $request->query('tab', 'attendance');
        $validTabs = array_keys(config('hr_reports.tabs', []));

        if (! in_array($tab, $validTabs, true)) {
            $tab = 'attendance';
        }

        $branches = $this->governedReportBranches($request, $scope->companyId);

        $departments = Department::query()
            ->where('company_id', $scope->companyId)
            ->orderBy('name')
            ->get(['id', 'name']);

        $jobTitles = JobTitle::query()
            ->where('company_id', $scope->companyId)
            ->orderBy('name')
            ->get(['id', 'name']);

        $employees = Employee::query()
            ->where('company_id', $scope->companyId)
            ->where('is_active', true)
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get(['id', 'first_name', 'middle_name', 'last_name', 'employee_number']);

        $employmentStatuses = collect(EmploymentStatus::cases())
            ->map(fn (EmploymentStatus $status) => [
                'value' => $status->value,
                'label' => ucwords(str_replace('_', ' ', $status->value)),
            ])
            ->all();

        $canExport = $request->user()?->can('hr.reports.export')
            || $request->user()?->can('reports.export')
            ?? false;

        return [
            'scope' => $scope,
            'branches' => $branches,
            'can_view_consolidated' => $this->canViewConsolidatedReports($request->user()),
            'departments' => $departments,
            'jobTitles' => $jobTitles,
            'employees' => $employees,
            'employmentStatuses' => $employmentStatuses,
            'can_export' => $canExport,
            'filters' => [
                'from_date' => $scope->fromDate,
                'to_date' => $scope->toDate,
                'branch_id' => $scope->branchId,
                'employee_id' => $scope->employeeId,
                'department_id' => $scope->departmentId,
                'job_title_id' => $scope->jobTitleId,
                'status' => $scope->status,
                'tab' => $tab,
            ],
            'tab' => $tab,
        ];
    }
}
