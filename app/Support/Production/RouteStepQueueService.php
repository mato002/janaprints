<?php

namespace App\Support\Production;

use App\Enums\ProductionQueueStatus;
use App\Models\Production\JobCardRouteStep;
use App\Models\Production\ProductionJobCard;
use App\Models\Production\ProductionQueue;
use App\Models\Production\WorkCenter;
use Illuminate\Support\Facades\DB;

class RouteStepQueueService
{
    public function __construct(
        protected ProductionQueueOrderingService $ordering,
        protected ProductionQueueService $queues,
    ) {}

    public function bootstrapQueuesForJobCard(ProductionJobCard $jobCard): void
    {
        $steps = $jobCard->routeSteps()
            ->whereNotNull('work_center_id')
            ->orderBy('sequence')
            ->get();

        if ($steps->isEmpty()) {
            return;
        }

        DB::transaction(function () use ($jobCard, $steps) {
            foreach ($steps as $step) {
                $this->createQueueForStep($jobCard, $step);
            }
        });
    }

    public function createQueueForStep(ProductionJobCard $jobCard, JobCardRouteStep $step): ?ProductionQueue
    {
        if (! $step->work_center_id) {
            return null;
        }

        $existing = ProductionQueue::query()
            ->where('job_card_route_step_id', $step->id)
            ->first();

        if ($existing) {
            return $existing;
        }

        $workCenter = WorkCenter::query()->findOrFail($step->work_center_id);

        $entry = ProductionQueue::query()->create([
            'company_id' => $jobCard->company_id,
            'branch_id' => $jobCard->branch_id,
            'production_job_card_id' => $jobCard->id,
            'job_card_route_step_id' => $step->id,
            'work_center_id' => $workCenter->id,
            'queue_position' => $this->queues->nextQueuePosition($workCenter),
            'status' => ProductionQueueStatus::Waiting,
        ]);

        $this->ordering->reorderWorkCenter($workCenter);

        return $entry->fresh(['workCenter']);
    }

    /**
     * @return array{current: ?ProductionQueue, position: ?int, work_center: ?WorkCenter}
     */
    public function currentQueueContext(ProductionJobCard $jobCard): array
    {
        $queues = $jobCard->relationLoaded('queues')
            ? $jobCard->queues
            : $jobCard->queues()->with(['workCenter:id,name,code', 'routeStep:id,step_name,sequence'])->get();

        $current = $queues->first(fn (ProductionQueue $q) => in_array($q->status, [
            ProductionQueueStatus::InProgress,
            ProductionQueueStatus::Assigned,
            ProductionQueueStatus::Queued,
        ], true)) ?? $queues->first(fn (ProductionQueue $q) => $q->status === ProductionQueueStatus::Waiting);

        if (! $current) {
            return ['current' => null, 'position' => null, 'work_center' => null];
        }

        $position = ProductionQueue::query()
            ->where('work_center_id', $current->work_center_id)
            ->whereIn('production_queues.status', array_map(fn (ProductionQueueStatus $s) => $s->value, ProductionQueueStatus::activeStatuses()))
            ->join('production_job_cards', 'production_job_cards.id', '=', 'production_queues.production_job_card_id')
            ->orderByRaw(app(ProductionQueueOrderingService::class)->priorityOrderSql('production_job_cards.priority'))
            ->orderBy('production_job_cards.required_date')
            ->orderBy('production_job_cards.planned_end_date')
            ->orderBy('production_job_cards.created_at')
            ->orderBy('production_queues.queue_position')
            ->pluck('production_queues.id')
            ->search($current->id);

        return [
            'current' => $current,
            'position' => $position === false ? null : $position + 1,
            'work_center' => $current->workCenter,
        ];
    }
}
