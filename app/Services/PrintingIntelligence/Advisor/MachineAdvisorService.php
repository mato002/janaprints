<?php

namespace App\Services\PrintingIntelligence\Advisor;

use App\Enums\AdvisorRecommendationType;
use App\Enums\AdvisorSeverity;
use App\Models\Assets\MachineProfile;
use App\Services\PrintingIntelligence\CapacityForecastService;
use App\Services\PrintingIntelligence\MachineProfitabilityService;

class MachineAdvisorService
{
    public function __construct(
        protected AdvisorConfidenceService $confidence,
    ) {}

    /**
     * @param  array{company_id?: int, branch_id?: int|null, days?: int}  $filters
     * @return list<array<string, mixed>>
     */
    public function recommend(array $filters = []): array
    {
        $companyId = (int) ($filters['company_id'] ?? tenant()->companyId() ?? auth()->user()?->company_id);
        $filters['company_id'] = $companyId;
        $filters['days'] = (int) ($filters['days'] ?? 90);

        $profitability = app(MachineProfitabilityService::class)->analyze($filters);
        $capacity = app(CapacityForecastService::class)->forecast($filters);
        $machines = MachineProfile::query()->where('company_id', $companyId)->get();

        $recommendations = [];
        $rankings = collect($profitability['rankings'] ?? []);

        if ($best = $profitability['best_performing'] ?? null) {
            $recommendations[] = AdvisorRecommendationWriter::payload(
                AdvisorRecommendationType::Machine,
                AdvisorSeverity::Info,
                'machine:most_profitable:'.($best['machine_profile_id'] ?? 'top'),
                __('More profitable machine available'),
                __(':name delivers highest profit in portfolio.', ['name' => $best['machine_name'] ?? '—']),
                __('Consider routing similar jobs to :name when capacity allows.', ['name' => $best['machine_name'] ?? '—']),
                'PI8',
                $this->confidence->score(['data_points' => 3, 'historical_periods' => 3, 'signal_strength' => 70]),
                __('Review upcoming jobs for machine reassignment.'),
                MachineProfile::class,
                $best['machine_profile_id'] ?? null,
                $best,
            );
        }

        if ($worst = $profitability['worst_performing'] ?? null) {
            $recommendations[] = AdvisorRecommendationWriter::payload(
                AdvisorRecommendationType::Machine,
                AdvisorSeverity::Medium,
                'machine:underperforming:'.($worst['machine_profile_id'] ?? 'worst'),
                __('Underperforming machine'),
                __(':name shows weakest profitability.', ['name' => $worst['machine_name'] ?? '—']),
                __('Investigate setup time, maintenance, or rate calibration for this machine.'),
                'PI8',
                $this->confidence->score(['data_points' => 3, 'historical_periods' => 2]),
                MachineProfile::class,
                $worst['machine_profile_id'] ?? null,
                $worst,
            );
        }

        $lowestCost = $machines->sortBy(fn ($m) => (float) ($m->cost_per_hour ?? 999999))->first();
        $highestCost = $machines->sortByDesc(fn ($m) => (float) ($m->cost_per_hour ?? 0))->first();
        if ($lowestCost && $highestCost && $lowestCost->id !== $highestCost->id) {
            $savings = round(((float) $highestCost->cost_per_hour - (float) $lowestCost->cost_per_hour) / max(1, (float) $highestCost->cost_per_hour) * 100, 1);
            if ($savings > 5) {
                $recommendations[] = AdvisorRecommendationWriter::payload(
                    AdvisorRecommendationType::Machine,
                    AdvisorSeverity::Info,
                    "machine:lower_cost:{$lowestCost->id}",
                    __('Lower cost machine option'),
                    __(':machineB is projected to reduce hourly cost vs :machineA.', [
                        'machineB' => $lowestCost->machine_code,
                        'machineA' => $highestCost->machine_code,
                    ]),
                    __('Machine :code may reduce cost by approximately :pct% for compatible jobs.', ['code' => $lowestCost->machine_code, 'pct' => $savings]),
                    'PI4',
                    $this->confidence->score(['data_points' => 2, 'signal_strength' => $savings]),
                    __('Evaluate PI4 machine selection for new quotes.'),
                    MachineProfile::class,
                    $lowestCost->id,
                    ['savings_percent' => $savings],
                );
            }
        }

        foreach ($capacity['bottlenecks'] ?? [] as $bottleneck) {
            $recommendations[] = AdvisorRecommendationWriter::payload(
                AdvisorRecommendationType::Machine,
                AdvisorSeverity::High,
                'machine:bottleneck:'.($bottleneck['machine_profile_id'] ?? uniqid()),
                __('Capacity bottleneck'),
                __(':name utilization forecast :pct%.', [
                    'name' => $bottleneck['machine_name'] ?? __('Machine'),
                    'pct' => $bottleneck['forecast_utilization_percent'] ?? 0,
                ]),
                __('Capacity relief may require shifting jobs to alternate machines or overtime planning.'),
                'PI9',
                $this->confidence->score(['forecast_confidence' => 65, 'historical_periods' => 2]),
                __('Review production schedule for load balancing.'),
                MachineProfile::class,
                $bottleneck['machine_profile_id'] ?? null,
                $bottleneck,
            );
        }

        $leastUtilized = $rankings->sortBy('utilization_percent')->first();
        if ($leastUtilized && ($leastUtilized['utilization_percent'] ?? 100) < 40) {
            $recommendations[] = AdvisorRecommendationWriter::payload(
                AdvisorRecommendationType::Machine,
                AdvisorSeverity::Low,
                'machine:underutilized:'.($leastUtilized['machine_profile_id'] ?? 'idle'),
                __('Underutilized machine'),
                __(':name running at :pct% utilization.', ['name' => $leastUtilized['machine_name'], 'pct' => $leastUtilized['utilization_percent']]),
                __('Consider assigning more jobs to this machine to improve ROI.'),
                'PI8',
                $this->confidence->score(['data_points' => 2]),
                MachineProfile::class,
                $leastUtilized['machine_profile_id'] ?? null,
                $leastUtilized,
            );
        }

        return $recommendations;
    }
}
