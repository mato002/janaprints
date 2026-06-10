<?php

namespace App\Services\PrintingIntelligence;

use App\Enums\ProfitabilitySnapshotType;
use App\Models\Assets\MachineProfile;
use App\Models\PrintingIntelligence\PrintProfitabilitySnapshot;

class MachineProfitabilityService
{
    /**
     * @param  array{company_id?: int, branch_id?: int|null, days?: int}  $filters
     * @return array<string, mixed>
     */
    public function analyze(array $filters = []): array
    {
        $companyId = (int) ($filters['company_id'] ?? tenant()->companyId() ?? auth()->user()?->company_id);
        $days = (int) ($filters['days'] ?? 90);

        $jobQuery = PrintProfitabilitySnapshot::query()
            ->where('company_id', $companyId)
            ->where('snapshot_type', ProfitabilitySnapshotType::Job)
            ->where('snapshot_date', '>=', now()->subDays($days)->toDateString())
            ->whereNotNull('machine_profile_id');

        PrintingIntelligenceScope::applyBranchScope($jobQuery, $filters);

        $jobRows = $jobQuery->get();
        $rows = $jobRows->groupBy('machine_profile_id');

        $machines = MachineProfile::query()->where('company_id', $companyId)->get()->keyBy('id');
        $totalJobs = max(1, $jobRows->count());

        $ranked = $rows->map(function ($group, $machineId) use ($machines, $totalJobs) {
            $revenue = $group->sum(fn ($r) => (float) $r->revenue);
            $cost = $group->sum(fn ($r) => (float) $r->total_cost);
            $profit = $group->sum(fn ($r) => (float) $r->gross_profit);
            $jobCount = $group->count();

            return [
                'machine_profile_id' => (int) $machineId,
                'machine_name' => $machines->get($machineId)?->machine_code ?? __('Machine #:id', ['id' => $machineId]),
                'jobs_processed' => $jobCount,
                'revenue' => round($revenue, 2),
                'cost' => round($cost, 2),
                'profit' => round($profit, 2),
                'margin_percent' => $revenue > 0 ? round(($profit / $revenue) * 100, 3) : null,
                'utilization_percent' => round(($jobCount / $totalJobs) * 100, 2),
            ];
        })->sortByDesc('profit')->values();

        return [
            'best_performing' => $ranked->first(),
            'worst_performing' => $ranked->last(),
            'rankings' => $ranked->take(10)->all(),
        ];
    }
}
