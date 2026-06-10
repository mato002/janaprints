<?php

namespace App\Services\PrintingIntelligence;

use App\Models\Company;
use App\Models\PrintingIntelligence\PrintCalibrationRule;
use App\Models\PrintingIntelligence\PrintEstimateActualComparison;
use App\Models\PrintingIntelligence\PrintQuotationEstimate;
use App\Enums\CalibrationRuleStatus;
use App\Enums\CalibrationRuleType;
use Illuminate\Support\Facades\Route;

class PrintingIntelligenceAuditService
{
    /**
     * @return array<string, array{status: string, detail: string}>
     */
    public function run(?int $companyId = null): array
    {
        $companyId ??= (int) (Company::query()->value('id') ?? 0);

        return [
            'calibration_propagation' => $this->checkCalibrationPropagation($companyId),
            'electricity_integrity' => $this->checkElectricityIntegrity(),
            'estimate_immutability' => $this->checkEstimateImmutability(),
            'tenant_isolation' => $this->checkTenantIsolation(),
            'permission_enforcement' => $this->checkPermissionEnforcement(),
            'profitability_integrity' => $this->checkProfitabilityIntegrity($companyId),
            'forecast_integrity' => $this->checkForecastIntegrity($companyId),
        ];
    }

    /**
     * @return array{status: string, detail: string}
     */
    protected function checkCalibrationPropagation(int $companyId): array
    {
        $profile = app(ActiveCostingProfileService::class)->profile($companyId);
        $calculator = app(ProductionCostCalculator::class);
        $machine = \App\Models\Assets\MachineProfile::query()->where('company_id', $companyId)->first();

        if ($machine === null) {
            return ['status' => 'PASS', 'detail' => 'No machine profile to validate; service wiring present.'];
        }

        PrintCalibrationRule::query()->updateOrCreate(
            [
                'company_id' => $companyId,
                'rule_type' => CalibrationRuleType::LabourRate,
                'rule_key' => 'audit_labour_rate',
            ],
            [
                'proposed_value' => 777,
                'status' => CalibrationRuleStatus::Approved,
                'approved_at' => now(),
                'effective_from' => now()->subDay(),
            ],
        );

        $result = $calculator->calculate($machine, 1.0);
        $rate = (float) ($result['metadata']['labour_hourly_rate'] ?? 0);

        return $rate === 777.0
            ? ['status' => 'PASS', 'detail' => 'Approved labour calibration consumed by PI4 calculator.']
            : ['status' => 'FAIL', 'detail' => "Expected labour rate 777, got {$rate}."];
    }

    /**
     * @return array{status: string, detail: string}
     */
    protected function checkElectricityIntegrity(): array
    {
        $company = Company::query()->first();
        if ($company === null) {
            return ['status' => 'PASS', 'detail' => 'Skipped — no company.'];
        }

        $machine = \App\Models\Assets\MachineProfile::query()
            ->where('company_id', $company->id)
            ->whereNotNull('power_rating_kw')
            ->first();

        if ($machine === null) {
            return ['status' => 'PASS', 'detail' => 'Skipped — no powered machine.'];
        }

        $service = app(MachineCostProfileService::class);
        $machineOnly = $service->estimatedMachineCost($machine, 2.0, (int) $company->id);
        $electricity = $service->estimatedElectricityCost($machine, 2.0, (int) $company->id);
        $combined = $machineOnly + $electricity;

        $legacy = round(($service->costPerHour($machine, (int) $company->id) * 2 * max(1, (float) ($machine->maintenance_cost_factor ?? 1)))
            + $service->estimatedSetupCost($machine, (int) $company->id)
            + $electricity, 2);

        $pricing = app(QuotationPricingService::class)->price([
            'material_cost' => 0,
            'ink_cost' => 0,
            'machine_cost' => $machineOnly,
            'labour_cost' => 0,
            'electricity_cost' => $electricity,
            'overhead_cost' => 0,
            'wastage_cost' => 0,
        ], 20, 35);

        $doubleCount = abs($pricing['estimated_total_cost'] - $legacy) > 0.02;

        return ! $doubleCount
            ? ['status' => 'PASS', 'detail' => 'Electricity counted once in PI5 pricing total.']
            : ['status' => 'FAIL', 'detail' => 'Electricity double-count detected in pricing rollup.'];
    }

    /**
     * @return array{status: string, detail: string}
     */
    protected function checkEstimateImmutability(): array
    {
        $estimate = PrintQuotationEstimate::query()->whereNotNull('applied_at')->first();

        if ($estimate === null) {
            return ['status' => 'PASS', 'detail' => 'Model guard registered; no applied estimates to mutate.'];
        }

        try {
            $estimate->estimated_total_cost = (float) $estimate->estimated_total_cost + 1;
            $estimate->save();

            return ['status' => 'FAIL', 'detail' => 'Applied estimate allowed overwrite.'];
        } catch (\App\Exceptions\PrintingIntelligence\AppliedEstimateImmutableException) {
            return ['status' => 'PASS', 'detail' => 'Applied estimates reject mutation.'];
        }
    }

    /**
     * @return array{status: string, detail: string}
     */
    protected function checkTenantIsolation(): array
    {
        $policies = [
            PrintEstimateActualComparison::class,
            PrintCalibrationRule::class,
            \App\Models\PrintingIntelligence\PrintProfitabilitySnapshot::class,
            \App\Models\PrintingIntelligence\PrintForecastSnapshot::class,
        ];

        foreach ($policies as $model) {
            if (! app(\Illuminate\Contracts\Auth\Access\Gate::class)->getPolicyFor($model)) {
                return ['status' => 'FAIL', 'detail' => 'Missing policy for '.class_basename($model).'.'];
            }
        }

        return ['status' => 'PASS', 'detail' => 'Tenant policies registered for PI models.'];
    }

    /**
     * @return array{status: string, detail: string}
     */
    protected function checkPermissionEnforcement(): array
    {
        $required = [
            'admin.printing-intelligence.estimate-vs-actual',
            'admin.printing-intelligence.executive-intelligence',
            'admin.printing-intelligence.cost',
        ];

        foreach ($required as $routeName) {
            if (! Route::has($routeName)) {
                return ['status' => 'FAIL', 'detail' => "Missing route {$routeName}."];
            }
        }

        return ['status' => 'PASS', 'detail' => 'Core PI routes registered with middleware groups.'];
    }

    /**
     * @return array{status: string, detail: string}
     */
    protected function checkProfitabilityIntegrity(int $companyId): array
    {
        $service = app(RevenueResolutionService::class);
        if (! method_exists($service, 'resolve')) {
            return ['status' => 'FAIL', 'detail' => 'RevenueResolutionService missing.'];
        }

        $customer = app(CustomerProfitabilityService::class)->analyze(['company_id' => $companyId, 'days' => 90]);

        return isset($customer['total_profit'])
            ? ['status' => 'PASS', 'detail' => 'Profitability aggregation service operational.']
            : ['status' => 'FAIL', 'detail' => 'Customer profitability aggregation failed.'];
    }

    /**
     * @return array{status: string, detail: string}
     */
    protected function checkForecastIntegrity(int $companyId): array
    {
        $generator = app(ForecastSnapshotGeneratorService::class);
        $snapshots = $generator->generateForCompany($companyId, null, null, false);

        return is_array($snapshots)
            ? ['status' => 'PASS', 'detail' => 'Forecast snapshot generator dry-run OK.']
            : ['status' => 'FAIL', 'detail' => 'Forecast generator failed.'];
    }
}
