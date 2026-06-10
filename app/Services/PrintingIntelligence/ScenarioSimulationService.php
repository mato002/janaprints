<?php

namespace App\Services\PrintingIntelligence;

class ScenarioSimulationService
{
    /**
     * @param  array{company_id?: int, scenario?: string}  $filters
     * @return array<string, mixed>
     */
    public function simulate(array $filters = []): array
    {
        $companyId = (int) ($filters['company_id'] ?? tenant()->companyId() ?? auth()->user()?->company_id);
        $scenario = $filters['scenario'] ?? 'sales_plus_10';

        $overview = app(PrintingIntelligenceGateway::class)->profitabilityOverview($companyId, null, ['days' => 90]);
        $baselineRevenue = (float) ($overview['summary']['total_revenue'] ?? 0);
        $baselineCost = (float) ($overview['summary']['total_cost'] ?? 0);
        $baselineProfit = (float) ($overview['summary']['total_profit'] ?? 0);
        $baselineMargin = $overview['summary']['average_margin'] ?? null;

        [$revenueFactor, $costFactor, $label] = $this->scenarioFactors($scenario);

        $simRevenue = round($baselineRevenue * $revenueFactor, 2);
        $simCost = round($baselineCost * $costFactor, 2);
        $simProfit = round($simRevenue - $simCost, 2);
        $simMargin = $simRevenue > 0 ? round(($simProfit / $simRevenue) * 100, 3) : null;

        return [
            'scenario' => $scenario,
            'scenario_label' => $label,
            'baseline' => [
                'revenue' => $baselineRevenue,
                'cost' => $baselineCost,
                'profit' => $baselineProfit,
                'margin_percent' => $baselineMargin,
            ],
            'simulated' => [
                'revenue' => $simRevenue,
                'cost' => $simCost,
                'profit' => $simProfit,
                'margin_percent' => $simMargin,
            ],
            'impact' => [
                'revenue_delta' => round($simRevenue - $baselineRevenue, 2),
                'profit_delta' => round($simProfit - $baselineProfit, 2),
                'margin_delta' => ($baselineMargin !== null && $simMargin !== null)
                    ? round($simMargin - $baselineMargin, 3)
                    : null,
            ],
            'read_only' => true,
            'available_scenarios' => $this->availableScenarios(),
        ];
    }

    /**
     * @return array{0: float, 1: float, 2: string}
     */
    protected function scenarioFactors(string $scenario): array
    {
        return match ($scenario) {
            'sales_plus_20' => [1.20, 1.0, __('Sales +20%')],
            'sales_minus_10' => [0.90, 1.0, __('Sales -10%')],
            'sales_minus_20' => [0.80, 1.0, __('Sales -20%')],
            'material_cost_plus_10' => [1.0, 1.10, __('Material costs +10%')],
            'material_cost_plus_20' => [1.0, 1.20, __('Material costs +20%')],
            'machine_utilization_plus_10' => [1.05, 1.10, __('Machine utilization +10%')],
            'machine_utilization_plus_20' => [1.10, 1.20, __('Machine utilization +20%')],
            default => [1.10, 1.0, __('Sales +10%')],
        };
    }

    /**
     * @return list<array{key: string, label: string}>
     */
    public function availableScenarios(): array
    {
        return [
            ['key' => 'sales_plus_10', 'label' => __('Sales +10%')],
            ['key' => 'sales_plus_20', 'label' => __('Sales +20%')],
            ['key' => 'sales_minus_10', 'label' => __('Sales -10%')],
            ['key' => 'sales_minus_20', 'label' => __('Sales -20%')],
            ['key' => 'material_cost_plus_10', 'label' => __('Material costs +10%')],
            ['key' => 'material_cost_plus_20', 'label' => __('Material costs +20%')],
            ['key' => 'machine_utilization_plus_10', 'label' => __('Machine utilization +10%')],
            ['key' => 'machine_utilization_plus_20', 'label' => __('Machine utilization +20%')],
        ];
    }
}
