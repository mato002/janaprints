<?php

namespace App\Support\Hr;

use App\Enums\CompensationStatus;
use App\Enums\EmploymentStatus;
use App\Models\Employee;
use App\Models\Hr\EmployeeCompensation;
use App\Models\Hr\PayrollRun;
use Illuminate\Support\Collection;

class PayrollEmployeeScopeService
{
    public const REASON_SUSPENDED = 'suspended';
    public const REASON_EXITED = 'exited';
    public const REASON_INACTIVE = 'inactive';
    public const REASON_WRONG_GROUP = 'wrong_payroll_group';
    public const REASON_MISSING_SALARY = 'missing_salary';
    public const REASON_MISSING_SETUP = 'missing_payroll_setup';

    public function __construct(
        protected PayrollGroupService $payrollGroups,
    ) {}

    /**
     * @return Collection<int, Employee>
     */
    public function includedEmployees(PayrollRun $run): Collection
    {
        return $this->candidateEmployees($run)
            ->filter(fn (Employee $employee) => $this->exclusionReason($employee, $run) === null)
            ->values();
    }

    /**
     * @return array<string, mixed>
     */
    public function certify(PayrollRun $run): array
    {
        $included = [];
        $excluded = [];

        foreach ($this->candidateEmployees($run) as $employee) {
            $reason = $this->exclusionReason($employee, $run);

            if ($reason === null) {
                $included[] = $this->employeeRow($employee, $run);
            } else {
                $excluded[] = array_merge($this->employeeRow($employee, $run), [
                    'exclusion_reason' => $reason,
                    'exclusion_label' => $this->reasonLabel($reason),
                ]);
            }
        }

        return [
            'payroll_group' => $run->payroll_group,
            'payroll_group_label' => $this->payrollGroups->label((int) $run->company_id, (string) $run->payroll_group),
            'included_count' => count($included),
            'excluded_count' => count($excluded),
            'included' => $included,
            'excluded' => $excluded,
        ];
    }

    public function exclusionReason(Employee $employee, PayrollRun $run): ?string
    {
        if ($employee->employment_status === EmploymentStatus::Suspended) {
            return self::REASON_SUSPENDED;
        }

        if ($employee->employment_status === EmploymentStatus::Terminated) {
            return self::REASON_EXITED;
        }

        if (! $employee->is_active) {
            return self::REASON_INACTIVE;
        }

        $compensation = $this->periodCompensation($employee, $run);

        if ($compensation === null) {
            return self::REASON_MISSING_SETUP;
        }

        if ($compensation->status !== null && $compensation->status !== CompensationStatus::Active) {
            return self::REASON_MISSING_SETUP;
        }

        if (blank($compensation->payroll_group)) {
            return self::REASON_MISSING_SETUP;
        }

        if ((string) $compensation->payroll_group !== (string) $run->payroll_group) {
            return self::REASON_WRONG_GROUP;
        }

        if ((float) $compensation->basic_salary <= 0) {
            return self::REASON_MISSING_SALARY;
        }

        return null;
    }

    /**
     * @return Collection<int, Employee>
     */
    protected function candidateEmployees(PayrollRun $run): Collection
    {
        return Employee::query()
            ->where('company_id', $run->company_id)
            ->when($run->branch_id, fn ($q) => $q->where('branch_id', $run->branch_id))
            ->with(['compensation'])
            ->orderBy('employee_number')
            ->get();
    }

    protected function periodCompensation(Employee $employee, PayrollRun $run): ?EmployeeCompensation
    {
        return EmployeeCompensation::query()
            ->where('employee_id', $employee->id)
            ->where('is_active', true)
            ->where('effective_from', '<=', $run->period_end)
            ->orderByDesc('effective_from')
            ->first();
    }

    /**
     * @return array<string, mixed>
     */
    protected function employeeRow(Employee $employee, PayrollRun $run): array
    {
        $compensation = $this->periodCompensation($employee, $run);

        return [
            'employee_id' => $employee->id,
            'employee_number' => $employee->employee_number,
            'employee_name' => $employee->full_name,
            'branch' => $employee->branch?->name,
            'payroll_group' => $compensation?->payroll_group,
            'payroll_group_label' => $compensation
                ? $this->payrollGroups->label((int) $employee->company_id, (string) $compensation->payroll_group)
                : null,
            'basic_salary' => $compensation ? (float) $compensation->basic_salary : null,
        ];
    }

    protected function reasonLabel(string $reason): string
    {
        return match ($reason) {
            self::REASON_SUSPENDED => __('Suspended'),
            self::REASON_EXITED => __('Exited'),
            self::REASON_INACTIVE => __('Inactive'),
            self::REASON_WRONG_GROUP => __('Wrong Payroll Group'),
            self::REASON_MISSING_SALARY => __('Missing Salary'),
            self::REASON_MISSING_SETUP => __('Missing Payroll Setup'),
            default => __('Excluded'),
        };
    }
}
