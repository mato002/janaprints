<?php

namespace App\Support\Production;

use App\Enums\JobCardRouteStepStatus;
use App\Models\Inventory\InventoryItem;
use App\Models\Inventory\ProductProductionRouteStep;
use App\Models\Production\JobCardRouteStep;
use App\Models\Production\ProductionJobCard;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ProductionRouteService
{
    /**
     * @param  list<array{step_name: string, sequence: int, is_active?: bool, work_center_id?: int|null}>  $steps
     */
    public function syncProductRoute(InventoryItem $item, array $steps): void
    {
        DB::transaction(function () use ($item, $steps) {
            $item->productionRouteSteps()->delete();

            foreach ($steps as $index => $step) {
                if (blank($step['step_name'] ?? null)) {
                    continue;
                }

                ProductProductionRouteStep::query()->create([
                    'company_id' => $item->company_id,
                    'branch_id' => $item->branch_id,
                    'inventory_item_id' => $item->id,
                    'work_center_id' => $step['work_center_id'] ?? null,
                    'step_name' => trim((string) $step['step_name']),
                    'sequence' => (int) ($step['sequence'] ?? ($index + 1)),
                    'is_active' => (bool) ($step['is_active'] ?? true),
                ]);
            }
        });
    }

    /**
     * @return Collection<int, JobCardRouteStep>
     */
    public function snapshotRouteToJobCard(ProductionJobCard $jobCard, ?InventoryItem $item): Collection
    {
        if (! $item) {
            return collect();
        }

        $steps = $item->activeProductionRouteSteps()->get(['step_name', 'sequence', 'work_center_id']);

        if ($steps->isEmpty()) {
            return collect();
        }

        return DB::transaction(function () use ($jobCard, $steps) {
            $created = collect();

            foreach ($steps as $step) {
                $created->push(JobCardRouteStep::query()->create([
                    'company_id' => $jobCard->company_id,
                    'branch_id' => $jobCard->branch_id,
                    'production_job_card_id' => $jobCard->id,
                    'work_center_id' => $step->work_center_id,
                    'step_name' => $step->step_name,
                    'sequence' => $step->sequence,
                    'status' => JobCardRouteStepStatus::Pending,
                ]));
            }

            return $created;
        });
    }

    /**
     * @return array{current: ?JobCardRouteStep, completed: Collection<int, JobCardRouteStep>, pending: Collection<int, JobCardRouteStep>, all: Collection<int, JobCardRouteStep>}
     */
    public function routeProgress(ProductionJobCard $jobCard): array
    {
        $steps = $jobCard->relationLoaded('routeSteps')
            ? $jobCard->routeSteps
            : $jobCard->routeSteps()->with(['completedByUser:id,name', 'workCenter:id,name,code'])->orderBy('sequence')->get();

        $current = $steps->first(fn (JobCardRouteStep $s) => $s->status === JobCardRouteStepStatus::InProgress)
            ?? $steps->first(fn (JobCardRouteStep $s) => $s->status === JobCardRouteStepStatus::Pending);

        return [
            'current' => $current,
            'completed' => $steps->filter(fn (JobCardRouteStep $s) => in_array($s->status, [
                JobCardRouteStepStatus::Completed,
                JobCardRouteStepStatus::Skipped,
            ], true)),
            'pending' => $steps->filter(fn (JobCardRouteStep $s) => in_array($s->status, [
                JobCardRouteStepStatus::Pending,
                JobCardRouteStepStatus::InProgress,
            ], true)),
            'all' => $steps,
        ];
    }

    public function updateStepStatus(
        JobCardRouteStep $step,
        JobCardRouteStepStatus $status,
        int $userId,
    ): JobCardRouteStep {
        $updates = ['status' => $status];

        if ($status === JobCardRouteStepStatus::InProgress) {
            $updates['started_at'] = $updates['started_at'] ?? now();
        }

        if (in_array($status, [JobCardRouteStepStatus::Completed, JobCardRouteStepStatus::Skipped], true)) {
            $updates['completed_at'] = now();
            $updates['completed_by'] = $userId;
            if (! $step->started_at) {
                $updates['started_at'] = now();
            }
        }

        $step->update($updates);

        return $step->fresh(['completedByUser:id,name', 'workCenter:id,name,code']);
    }
}
