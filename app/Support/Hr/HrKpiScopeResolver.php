<?php

namespace App\Support\Hr;

use App\Enums\EmploymentStatus;
use App\Models\Department;
use App\Support\Reports\Concerns\ResolvesGovernedReportBranchScope;
use App\Models\Employee;
use App\Models\JobTitle;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class HrKpiScopeResolver
{
    use ResolvesGovernedReportBranchScope;

    /**
     * @return array{
     *     scope: HrKpiScope,
     *     branches: Collection<int, Branch>,
     *     departments: Collection<int, Department>,
     *     jobTitles: Collection<int, JobTitle>,
     *     employees: Collection<int, Employee>,
     *     employmentStatuses: list<array{value: string, label: string}>,
     *     dimensions: list<array{key: string, label: string}>,
     *     can_export: bool,
     *     filters: array<string, mixed>
     * }
     */
    public function resolve(Request $request): array
    {
        $companyId = tenant()->companyId() ?? $request->user()?->company_id;

        if (! $companyId) {
            abort(403, __('Company context is required.'));
        }

        $dimension = (string) $request->query('dimension', 'company');
        $validDimensions = ['company', 'branch', 'department', 'supervisor'];

        if (! in_array($dimension, $validDimensions, true)) {
            $dimension = 'company';
        }

        if ($dimension === 'branch' && ! $this->canViewConsolidatedReports($request->user())) {
            $dimension = 'company';
        }

        $branchId = $this->resolveGovernedBranchId($request, defaultFromTenant: false);

        $scope = new HrKpiScope(
            companyId: (int) $companyId,
            branchId: $branchId,
            fromDate: $request->input('from_date', now()->startOfMonth()->toDateString()),
            toDate: $request->input('to_date', now()->toDateString()),
            dimension: $dimension,
            employeeId: $request->filled('employee_id') ? (int) $request->input('employee_id') : null,
            departmentId: $request->filled('department_id') ? (int) $request->input('department_id') : null,
            jobTitleId: $request->filled('job_title_id') ? (int) $request->input('job_title_id') : null,
            supervisorJobTitleId: $request->filled('supervisor_job_title_id') ? (int) $request->input('supervisor_job_title_id') : null,
            status: $request->filled('status') ? (string) $request->input('status') : null,
        );

        $canExport = $request->user()?->can('hr.kpi.export')
            || $request->user()?->can('kpi.view')
            || $request->user()?->can('reports.export')
            ?? false;

        $branches = $this->governedReportBranches($request, $scope->companyId);

        return [
            'scope' => $scope,
            'branches' => $branches,
            'can_view_consolidated' => $this->canViewConsolidatedReports($request->user()),
            'departments' => Department::query()->where('company_id', $scope->companyId)->orderBy('name')->get(['id', 'name']),
            'jobTitles' => JobTitle::query()->where('company_id', $scope->companyId)->where('is_active', true)->orderBy('title')->get(['id', 'title']),
            'employees' => Employee::query()->where('company_id', $scope->companyId)->where('is_active', true)->orderBy('first_name')->get(['id', 'first_name', 'middle_name', 'last_name', 'employee_number']),
            'employmentStatuses' => collect(EmploymentStatus::cases())->map(fn (EmploymentStatus $status) => [
                'value' => $status->value,
                'label' => ucwords(str_replace('_', ' ', $status->value)),
            ])->all(),
            'dimensions' => [
                ['key' => 'company', 'label' => __('Company')],
                ['key' => 'branch', 'label' => __('Branch')],
                ['key' => 'department', 'label' => __('Department')],
                ['key' => 'supervisor', 'label' => __('Supervisor')],
            ],
            'can_export' => $canExport,
            'filters' => [
                'from_date' => $scope->fromDate,
                'to_date' => $scope->toDate,
                'branch_id' => $scope->branchId,
                'employee_id' => $scope->employeeId,
                'department_id' => $scope->departmentId,
                'job_title_id' => $scope->jobTitleId,
                'supervisor_job_title_id' => $scope->supervisorJobTitleId,
                'status' => $scope->status,
                'dimension' => $dimension,
            ],
        ];
    }
}
