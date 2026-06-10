<?php

namespace App\Services\PrintingIntelligence;

use App\Enums\ProfitabilitySnapshotType;
use App\Models\Assets\MachineProfile;
use App\Models\PrintingIntelligence\PrintProfitabilitySnapshot;

class CapacityForecastService
{
    public function __construct(
        protected ExecutiveForecastingService $engine,
    ) {}

    /**
     * @param  array{company_id?: int, days?: int}  $filters
     * @return array<string, mixed>
     */
    public function forecast(array $filters = []): array
    {
        $companyId = (int) ($filters['company_id'] ?? tenant()->companyId() ?? auth()->user()?->company_id);
        $days = (int) ($filters['days'] ?? 90);

        $jobs = PrintProfitabilitySnapshot::query()
            ->where('company_id', $companyId)
            ->where('snapshot_type', ProfitabilitySnapshotType::Job)
            ->where('snapshot_date', '>=', now()->subDays($days)->toDateString())
            ->whereNotNull('machine_profile_id')
            ->get();

        $totalJobs = PrintProfitabilitySnapshot::query()
            ->where('company_id', $companyId)
            ->where('snapshot_type', ProfitabilitySnapshotType::Job)
            ->where('snapshot_date', '>=', now()->subDays($days)->toDateString())
            ->count();

        $machines = MachineProfile::query()->where('company_id', $companyId)->get()->keyBy('id');
        $utilizationHistory = [];

        $machineForecasts = $jobs->groupBy('machine_profile_id')->map(function ($group, $machineId) use ($machines, $totalJobs, &$utilizationHistory) {
            $currentUtil = $totalJobs > 0 ? ($group->count() / $totalJobs) * 100 : 0;
            $utilizationHistory[] = $currentUtil;
            $projection = $this->engine->project([$currentUtil]);

            return [
                'machine_profile_id' => (int) $machineId,
                'machine_name' => $machines->get($machineId)?->machine_code ?? __('Machine #:id', ['id' => $machineId]),
                'current_utilization_percent' => round($currentUtil, 2),
                'forecast_utilization_percent' => $projection['forecast_value'],
                'confidence_score' => $projection['confidence_score'],
                'is_bottleneck' => $currentUtil >= (float) config('printing_intelligence.capacity_bottleneck_threshold', 35),
                'is_underutilized' => $currentUtil <= (float) config('printing_intelligence.capacity_underutilized_threshold', 5),
            ];
        })->sortByDesc('current_utilization_percent')->values();

        $overall = $this->engine->project($utilizationHistory ?: [0]);

        return [
            'overall_utilization_forecast' => $overall,
            'machines' => $machineForecasts->all(),
            'bottlenecks' => $machineForecasts->where('is_bottleneck', true)->values()->all(),
            'underutilized' => $machineForecasts->where('is_underutilized', true)->values()->all(),
        ];
    }
}
