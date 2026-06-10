<?php

namespace App\Services\PrintingIntelligence;

use App\Enums\CalibrationRuleStatus;
use App\Enums\CalibrationRuleType;
use App\Models\PrintingIntelligence\PrintCalibrationRule;

class ActiveCostingProfileService
{
    public function __construct(
        protected CostFormulaVersionService $formulaVersions,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function profile(?int $companyId = null): array
    {
        $companyId ??= (int) (tenant()->companyId() ?? auth()->user()?->company_id);

        return [
            'formula_versions' => $this->formulaVersions->currentVersions($companyId),
            'ink_yield_factor' => $this->value(CalibrationRuleType::InkYield, 'default_cmyk_coverage_factor', $companyId, 1.0),
            'ink_cost_per_ml' => $this->value(CalibrationRuleType::InkCost, null, $companyId, null),
            'machine_rate_per_hour' => $this->value(CalibrationRuleType::MachineRate, null, $companyId, null),
            'labour_rate_per_hour' => $this->value(CalibrationRuleType::LabourRate, 'labour_hourly_rate', $companyId, 500.0),
            'electricity_rate_per_kwh' => $this->value(CalibrationRuleType::ElectricityRate, 'electricity_rate_per_kwh', $companyId, 25.0),
            'overhead_percent' => $this->value(CalibrationRuleType::OverheadRate, 'default_overhead_percent', $companyId, 10.0),
            'wastage_percent' => $this->value(CalibrationRuleType::WastageFactor, 'default_wastage_percent', $companyId, 5.0),
            'minimum_margin_percent' => $this->value(CalibrationRuleType::MarginRule, 'default_minimum_margin_percent', $companyId, 20.0),
            'target_margin_percent' => $this->value(CalibrationRuleType::MarginRule, 'default_target_margin_percent', $companyId, 35.0),
            'source' => 'active_costing_profile',
        ];
    }

    public function value(
        CalibrationRuleType $type,
        ?string $configKey,
        ?int $companyId,
        mixed $default,
    ): mixed {
        $rule = $this->activeRule($type, $companyId);

        if ($rule !== null && $rule->proposed_value !== null) {
            return (float) $rule->proposed_value;
        }

        if ($configKey !== null) {
            return config("printing_intelligence.{$configKey}", $default);
        }

        return $default;
    }

    public function activeRule(CalibrationRuleType $type, ?int $companyId = null): ?PrintCalibrationRule
    {
        $companyId ??= (int) (tenant()->companyId() ?? auth()->user()?->company_id);

        return PrintCalibrationRule::query()
            ->where('company_id', $companyId)
            ->where('rule_type', $type)
            ->where('status', CalibrationRuleStatus::Approved)
            ->where(function ($query) {
                $query->whereNull('effective_from')->orWhere('effective_from', '<=', now());
            })
            ->latest('approved_at')
            ->first();
    }
}
