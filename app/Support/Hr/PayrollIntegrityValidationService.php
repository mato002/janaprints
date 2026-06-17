<?php

namespace App\Support\Hr;

use App\Models\Hr\PayrollRun;

class PayrollIntegrityValidationService
{
    public function __construct(
        protected PayrollEmployeeScopeService $scope,
        protected PayrollCompensationValidationService $compensationValidation,
    ) {}

    /**
     * @return array{valid: bool, warnings: list<array<string, mixed>>, summary: array<string, int>}
     */
    public function validateBeforeGeneration(PayrollRun $run): array
    {
        if (blank($run->payroll_group)) {
            return [
                'valid' => false,
                'warnings' => [[
                    'code' => 'missing_payroll_group',
                    'message' => __('Payroll group must be selected before generation.'),
                ]],
                'summary' => ['blocking_issues' => 1],
            ];
        }

        $certification = $this->scope->certify($run);
        $warnings = [];

        if ($certification['included_count'] === 0) {
            $warnings[] = [
                'code' => 'no_included_employees',
                'message' => __('No employees are eligible for this payroll group and scope.'),
            ];
        }

        foreach ($certification['excluded'] as $row) {
            if (in_array($row['exclusion_reason'], [
                PayrollEmployeeScopeService::REASON_MISSING_SALARY,
                PayrollEmployeeScopeService::REASON_MISSING_SETUP,
            ], true)) {
                $warnings[] = [
                    'code' => 'setup_gap',
                    'employee_id' => $row['employee_id'],
                    'employee_number' => $row['employee_number'],
                    'employee_name' => $row['employee_name'],
                    'message' => __(':name — :reason', [
                        'name' => $row['employee_name'],
                        'reason' => $row['exclusion_label'],
                    ]),
                ];
            }
        }

        $includedEmployees = $this->scope->includedEmployees($run);
        $compensationCheck = $this->compensationValidation->validateEmployees($includedEmployees);

        foreach ($compensationCheck['issues'] as $issue) {
            $warnings[] = [
                'code' => 'compensation_issue',
                'employee_id' => $issue['employee_id'],
                'employee_number' => $issue['employee_number'],
                'employee_name' => $issue['employee_name'],
                'message' => $issue['employee_name'].': '.implode('; ', $issue['problems']),
            ];
        }

        $blocking = collect($warnings)->whereIn('code', ['missing_payroll_group', 'no_included_employees'])->count();

        return [
            'valid' => $blocking === 0,
            'warnings' => $warnings,
            'summary' => [
                'included_count' => $certification['included_count'],
                'excluded_count' => $certification['excluded_count'],
                'setup_warnings' => count($warnings),
                'blocking_issues' => $blocking,
            ],
            'scope' => $certification,
        ];
    }
}
