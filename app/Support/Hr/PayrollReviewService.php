<?php

namespace App\Support\Hr;

use App\Models\Employee;
use App\Models\Hr\PayrollRun;
use App\Enums\EmploymentStatus;

class PayrollReviewService
{
    public function __construct(
        protected PayrollCompensationValidationService $compensationValidation,
    ) {}

    /**
     * @return array{
     *     critical: list<array<string, mixed>>,
     *     warnings: list<array<string, mixed>>,
     *     informational: list<array<string, mixed>>,
     *     summary: array<string, int>,
     *     can_submit_for_approval: bool
     * }
     */
    public function review(PayrollRun $run): array
    {
        $run->loadMissing(['payslips.employee.compensation']);

        $critical = [];
        $warnings = [];
        $informational = [];

        $employeeIds = [];
        foreach ($run->payslips as $payslip) {
            $employee = $payslip->employee;
            if (! $employee) {
                continue;
            }

            if (isset($employeeIds[$employee->id])) {
                $critical[] = $this->issue(
                    'duplicate_line',
                    $employee,
                    __('Duplicate payroll line for employee'),
                    true,
                );

                continue;
            }
            $employeeIds[$employee->id] = true;

            foreach ($this->compensationValidation->problemsForEmployee($employee) as $problem) {
                $critical[] = $this->issue('missing_salary', $employee, $problem, true);
            }

            if ((float) $payslip->basic_salary <= 0 && (float) $payslip->gross_pay <= 0) {
                $critical[] = $this->issue('zero_salary', $employee, __('Zero salary on payroll line'), true);
            }

            if ((float) $payslip->net_pay < 0) {
                $critical[] = $this->issue('negative_net', $employee, __('Negative net pay'), true);
            }

            if (blank($employee->kra_pin)) {
                $warnings[] = $this->issue('missing_kra_pin', $employee, __('Missing KRA PIN'), false);
            }

            if (blank($employee->bank_account_number) || blank($employee->bank_name)) {
                $warnings[] = $this->issue('missing_bank', $employee, __('Missing bank details'), false);
            }

            if (blank($employee->nssf_number)) {
                $warnings[] = $this->issue('missing_nssf', $employee, __('Missing NSSF number'), false);
            }

            if (blank($employee->nhif_number)) {
                $warnings[] = $this->issue('missing_shif_nhif', $employee, __('Missing SHIF/NHIF number'), false);
            }

            if ($run->generation_warnings) {
                foreach ($run->generation_warnings as $generationWarning) {
                    if (($generationWarning['employee_id'] ?? null) === $employee->id) {
                        foreach ($generationWarning['problems'] ?? [] as $problem) {
                            $warnings[] = $this->issue('generation_warning', $employee, $problem, false);
                        }
                    }
                }
            }
        }

        $scopedEmployees = Employee::query()
            ->where('company_id', $run->company_id)
            ->where('is_active', true)
            ->where('employment_status', '!=', EmploymentStatus::Terminated->value)
            ->when($run->branch_id, fn ($q) => $q->where('branch_id', $run->branch_id))
            ->orderBy('employee_number')
            ->get();
        $includedIds = $run->payslips->pluck('employee_id')->all();

        foreach ($scopedEmployees as $employee) {
            if (! in_array($employee->id, $includedIds, true)) {
                $informational[] = $this->issue(
                    'excluded_employee',
                    $employee,
                    __('Employee excluded from payroll run'),
                    false,
                );
            }
        }

        $critical = $this->uniqueIssues($critical);
        $warnings = $this->uniqueIssues($warnings);

        return [
            'critical' => $critical,
            'warnings' => $warnings,
            'informational' => $informational,
            'summary' => [
                'critical_count' => count($critical),
                'warning_count' => count($warnings),
                'informational_count' => count($informational),
                'employees_on_run' => $run->payslips->count(),
            ],
            'can_submit_for_approval' => $critical === [],
        ];
    }

    public function assertCanSubmitForApproval(PayrollRun $run): void
    {
        $review = $this->review($run);

        if (! $review['can_submit_for_approval']) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'review' => __('Resolve critical payroll review issues before submitting for approval.'),
            ]);
        }
    }

    /**
     * @param  list<array<string, mixed>>  $issues
     * @return list<array<string, mixed>>
     */
    protected function uniqueIssues(array $issues): array
    {
        $seen = [];

        return collect($issues)
            ->filter(function (array $issue) use (&$seen) {
                $key = ($issue['code'] ?? '').':'.($issue['employee_id'] ?? '').':'.($issue['message'] ?? '');

                if (isset($seen[$key])) {
                    return false;
                }
                $seen[$key] = true;

                return true;
            })
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    protected function issue(string $code, Employee $employee, string $message, bool $critical): array
    {
        return [
            'code' => $code,
            'employee_id' => $employee->id,
            'employee_number' => $employee->employee_number,
            'employee_name' => $employee->full_name,
            'message' => $message,
            'critical' => $critical,
        ];
    }
}
