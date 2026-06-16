<?php

namespace App\Support\Hr;

use App\Support\Platform\SystemSettingsService;

class PayrollStatutorySettingsService
{
    public function __construct(
        protected SystemSettingsService $settings,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function resolve(?int $companyId = null, ?int $branchId = null): array
    {
        $defaults = config('payroll_statutory', []);

        if ($companyId === null) {
            return $defaults;
        }

        $stored = $this->settings->get('payroll_statutory_rates', [], $companyId, $branchId);

        if (! is_array($stored) || $stored === []) {
            return $defaults;
        }

        return array_replace_recursive($defaults, $stored);
    }

    public function nssfEmployeeRate(?int $companyId = null): float
    {
        return (float) ($this->resolve($companyId)['nssf']['employee_rate'] ?? 0.06);
    }

    public function nssfEmployerRate(?int $companyId = null): float
    {
        return (float) ($this->resolve($companyId)['nssf']['employer_rate'] ?? 0.06);
    }

    public function nssfPensionableCeiling(?int $companyId = null): float
    {
        return (float) ($this->resolve($companyId)['nssf']['pensionable_ceiling'] ?? 36000);
    }

    public function shifEmployeeRate(?int $companyId = null): float
    {
        return (float) ($this->resolve($companyId)['shif']['employee_rate'] ?? 0.0275);
    }

    public function shifEmployerRate(?int $companyId = null): float
    {
        return (float) ($this->resolve($companyId)['shif']['employer_rate'] ?? 0);
    }

    public function shifMinimum(?int $companyId = null): float
    {
        return (float) ($this->resolve($companyId)['shif']['minimum_contribution'] ?? 300);
    }

    public function housingEmployeeRate(?int $companyId = null): float
    {
        return (float) ($this->resolve($companyId)['housing_levy']['employee_rate'] ?? 0.015);
    }

    public function housingEmployerRate(?int $companyId = null): float
    {
        return (float) ($this->resolve($companyId)['housing_levy']['employer_rate'] ?? 0.015);
    }

    public function payePersonalRelief(?int $companyId = null): float
    {
        return (float) ($this->resolve($companyId)['paye']['personal_relief'] ?? 2400);
    }

    /**
     * @return list<array{limit: float, rate: float}>
     */
    public function payeBands(?int $companyId = null): array
    {
        return $this->resolve($companyId)['paye']['bands'] ?? config('payroll_statutory.paye.bands', []);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function additionalStatutories(?int $companyId = null): array
    {
        return $this->resolve($companyId)['additional'] ?? [];
    }
}
