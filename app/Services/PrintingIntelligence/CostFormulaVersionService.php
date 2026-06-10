<?php

namespace App\Services\PrintingIntelligence;

use App\Enums\CalibrationRuleType;
use App\Models\PrintingIntelligence\PrintCalibrationRule;
use Illuminate\Support\Collection;

class CostFormulaVersionService
{
    /**
     * @return array<string, string>
     */
    public function currentVersions(?int $companyId = null): array
    {
        $base = [
            'PI3' => (string) config('printing_intelligence.default_formula_version', 'PI3-V1'),
            'PI4' => (string) config('printing_intelligence.production_formula_version', 'PI4-V1'),
            'PI5' => (string) config('printing_intelligence.quotation_formula_version', 'PI5-V1'),
            'PI6' => (string) config('printing_intelligence.estimate_actual_formula_version', 'PI6-V1'),
            'PI7' => (string) config('printing_intelligence.calibration_formula_version', 'PI7-V1'),
        ];

        if ($companyId === null) {
            return $base;
        }

        $approved = PrintCalibrationRule::query()
            ->where('company_id', $companyId)
            ->where('status', 'approved')
            ->whereNotNull('rule_version')
            ->latest('approved_at')
            ->get()
            ->groupBy(fn (PrintCalibrationRule $rule) => $rule->rule_type->formulaPrefix());

        foreach ($approved as $prefix => $rules) {
            $latest = $rules->sortByDesc('rule_version')->first();
            if ($latest?->rule_version) {
                $base[$prefix] = $latest->rule_version;
            }
        }

        return $base;
    }

    public function nextVersion(CalibrationRuleType $ruleType, ?int $companyId = null): string
    {
        $prefix = $ruleType->formulaPrefix();
        $current = $this->currentVersions($companyId)[$prefix] ?? "{$prefix}-V1";

        if (preg_match('/^(.+)-V(\d+)$/', $current, $matches)) {
            $next = ((int) $matches[2]) + 1;

            return "{$matches[1]}-V{$next}";
        }

        return "{$prefix}-V2";
    }

    /**
     * Historical estimates retain their stored formula_version — never rewritten.
     */
    public function assertHistoricalImmutability(string $storedVersion, string $activeVersion): bool
    {
        return $storedVersion !== '' && $storedVersion !== $activeVersion;
    }
}
