<?php

namespace App\Services\PrintingIntelligence;

use App\Models\Assets\MachineProfile;
use App\Models\PrintingIntelligence\PrintArtworkAnalysis;
use Illuminate\Support\Collection;

class MachineSelectionService
{
    public function __construct(
        protected ProductionRunTimeEstimationService $runTimeEstimator,
        protected MachineCostProfileService $machineCostProfile,
        protected ProductionCostCalculator $costCalculator,
    ) {}

    /**
     * @return array{
     *     selected: array<string, mixed>|null,
     *     alternatives: list<array<string, mixed>>,
     *     warnings: list<string>
     * }
     */
    public function select(PrintArtworkAnalysis $analysis, int $quantity = 1, ?MachineProfile $preferred = null): array
    {
        $candidates = $this->candidateMachines($analysis, $preferred);

        if ($candidates->isEmpty()) {
            return [
                'selected' => null,
                'alternatives' => [],
                'warnings' => [__('No eligible production machines found for selection.')],
            ];
        }

        $warnings = [];
        $ranked = [];

        foreach ($candidates as $machine) {
            $runTime = $this->runTimeEstimator->estimate($analysis, $machine, $quantity);
            $runHours = (float) ($runTime['estimated_run_hours'] ?? 0);

            if ($runHours <= 0) {
                continue;
            }

            $costs = $this->costCalculator->calculate($machine, $runHours, 0.0, 0.0);
            $score = $this->scoreMachine($machine, $analysis, (float) $costs['estimated_total_production_cost'], $runHours);

            $ranked[] = [
                'machine_profile_id' => $machine->id,
                'machine_code' => $machine->machine_code,
                'machine_type' => $machine->machine_type,
                'is_primary' => (bool) $machine->is_primary_production_machine,
                'estimated_run_hours' => $runHours,
                'estimated_machine_cost' => $costs['estimated_machine_cost'],
                'estimated_labour_cost' => $costs['estimated_labour_cost'],
                'estimated_total_production_cost' => $costs['estimated_total_production_cost'],
                'selection_score' => $score,
                'branch_id' => $machine->branch_id,
            ];
        }

        if ($ranked === []) {
            return [
                'selected' => null,
                'alternatives' => [],
                'warnings' => array_merge($warnings, [__('Machines found but none could produce a run-time estimate.')]),
            ];
        }

        usort($ranked, fn ($a, $b) => $b['selection_score'] <=> $a['selection_score']);

        $selected = $ranked[0];
        $alternatives = array_slice($ranked, 1, 5);

        return [
            'selected' => $selected,
            'alternatives' => $alternatives,
            'warnings' => $warnings,
        ];
    }

    /**
     * @return Collection<int, MachineProfile>
     */
    protected function candidateMachines(PrintArtworkAnalysis $analysis, ?MachineProfile $preferred): Collection
    {
        if ($preferred !== null) {
            return collect([$preferred]);
        }

        return MachineProfile::query()
            ->productionMachines()
            ->where('company_id', $analysis->company_id)
            ->when($analysis->branch_id, fn ($q) => $q->where('branch_id', $analysis->branch_id))
            ->orderByDesc('is_primary_production_machine')
            ->orderBy('machine_code')
            ->get();
    }

    protected function scoreMachine(
        MachineProfile $machine,
        PrintArtworkAnalysis $analysis,
        float $totalProductionCost,
        float $runHours,
    ): float {
        $score = 100.0;

        if ($totalProductionCost > 0) {
            $score -= min(50, $totalProductionCost / 100);
        }

        if ((int) $machine->branch_id === (int) $analysis->branch_id) {
            $score += 10;
        }

        if ($machine->is_primary_production_machine && config('printing_intelligence.machine_selection_prefer_primary', true)) {
            $score += 15;
        }

        if ($this->machineCostProfile->costPerHour($machine) <= 0) {
            $score -= 25;
        }

        if ($runHours > 8) {
            $score -= 10;
        }

        return round(max(0, min(100, $score)), 3);
    }
}
