<?php

namespace App\Support\Hr;

use App\Models\Company;
use App\Models\Employee;

class EmployeeNumberService
{
    public function prefixForCompany(int $companyId): string
    {
        $configured = strtoupper(trim((string) config('hr.employee_number.prefix', 'JP')));

        if ($configured !== '') {
            return $configured;
        }

        if (config('hr.employee_number.use_company_code', false)) {
            $code = Company::query()->whereKey($companyId)->value('code');

            if (filled($code)) {
                return strtoupper((string) $code);
            }
        }

        return 'JPEMP';
    }

    public function nextForCompany(int $companyId): string
    {
        $prefix = $this->prefixForCompany($companyId);
        $padding = max(2, (int) config('hr.employee_number.sequence_padding', 4));

        $numbers = Employee::query()
            ->where('company_id', $companyId)
            ->pluck('employee_number');

        $max = 0;

        foreach ($numbers as $employeeNumber) {
            if (preg_match('/-(\d+)$/', (string) $employeeNumber, $matches)) {
                $max = max($max, (int) $matches[1]);
            }
        }

        for ($candidate = $max + 1; $candidate <= $max + 1000; $candidate++) {
            $formatted = sprintf('%s-%0'.$padding.'d', $prefix, $candidate);

            if (! $numbers->contains($formatted)) {
                return $formatted;
            }
        }

        return sprintf('%s-%0'.$padding.'d', $prefix, $max + 1);
    }

    public function isAvailable(int $companyId, string $employeeNumber, ?int $ignoreEmployeeId = null): bool
    {
        return ! Employee::query()
            ->where('company_id', $companyId)
            ->where('employee_number', $employeeNumber)
            ->when($ignoreEmployeeId, fn ($query) => $query->where('id', '!=', $ignoreEmployeeId))
            ->exists();
    }
}
