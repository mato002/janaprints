<?php

namespace App\Support\Hr;

use App\Enums\CompensationStatus;
use App\Models\Employee;
use App\Models\Hr\PayrollRun;

class PayrollCompensationValidationService
{
    /**
     * @return array{valid: bool, issues: list<array{employee_id: int, employee_number: string, employee_name: string, problems: list<string>}>, summary: array<string, int>}
     */
    public function validateForRun(PayrollRun $run): array
    {
        return $this->validateEmployees(
            Employee::query()
                ->where('company_id', $run->company_id)
                ->where('is_active', true)
                ->when($run->branch_id, fn ($q) => $q->where('branch_id', $run->branch_id))
                ->with('compensation')
                ->orderBy('employee_number')
                ->get()
        );
    }

    /**
     * @return array{valid: bool, issues: list<array{employee_id: int, employee_number: string, employee_name: string, problems: list<string>}>, summary: array<string, int>}
     */
    public function validateCompany(int $companyId, ?int $branchId = null): array
    {
        return $this->validateEmployees(
            Employee::query()
                ->where('company_id', $companyId)
                ->where('is_active', true)
                ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                ->with('compensation')
                ->orderBy('employee_number')
                ->get()
        );
    }

    /**
     * @param  \Illuminate\Support\Collection<int, Employee>  $employees
     * @return array{valid: bool, issues: list<array{employee_id: int, employee_number: string, employee_name: string, problems: list<string>}>, summary: array<string, int>}
     */
    protected function validateEmployees($employees): array
    {
        $issues = [];

        foreach ($employees as $employee) {
            $problems = $this->problemsForEmployee($employee);

            if ($problems !== []) {
                $issues[] = [
                    'employee_id' => $employee->id,
                    'employee_number' => $employee->employee_number,
                    'employee_name' => $employee->full_name,
                    'problems' => $problems,
                ];
            }
        }

        return [
            'valid' => $issues === [],
            'issues' => $issues,
            'summary' => [
                'employees_checked' => $employees->count(),
                'employees_with_issues' => count($issues),
                'missing_compensation' => collect($issues)->filter(
                    fn ($i) => in_array(__('No active compensation record'), $i['problems'], true)
                )->count(),
            ],
        ];
    }

    /**
     * @return list<string>
     */
    public function problemsForEmployee(Employee $employee): array
    {
        $problems = [];
        $compensation = $employee->compensation;

        if (! $compensation) {
            $problems[] = __('No active compensation record');

            return $problems;
        }

        if ($compensation->status !== null) {
            $status = $compensation->status instanceof CompensationStatus
                ? $compensation->status
                : CompensationStatus::tryFrom((string) $compensation->status);

            if ($status !== null && $status !== CompensationStatus::Active) {
                $problems[] = __('Compensation is not active (status: :status)', [
                    'status' => $status->label(),
                ]);
            }
        } elseif (! $compensation->is_active) {
            $problems[] = __('Compensation is not active');
        }

        if ((float) $compensation->basic_salary <= 0) {
            $problems[] = __('Basic salary is missing or zero');
        }

        if (blank($compensation->payroll_group)) {
            $problems[] = __('Payroll group is not assigned');
        }

        return $problems;
    }
}
