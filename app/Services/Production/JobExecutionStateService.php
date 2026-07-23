<?php

namespace App\Services\Production;

use App\Enums\ProductionJobCardStatus;
use App\Enums\ProductionQueueStatus;
use App\Models\Production\ProductionJobCard;
use App\Models\Production\ProductionQueue;
use App\Models\User;
use App\Services\Dispatch\JobDispatchPresentationService;
use App\Support\Production\ProductionFloorSettings;
use App\Support\Production\RouteStepQueueService;
use Illuminate\Support\Collection;

/**
 * Derives supervisor/operator execution readiness for job cards already released from Sales.
 */
class JobExecutionStateService
{
    public function __construct(
        protected RouteStepQueueService $routeQueues,
        protected ProductionFloorSettings $floorSettings,
        protected JobDispatchPresentationService $dispatchPresentation,
    ) {}

    /**
     * @return array{
     *     phase: string,
     *     phase_label: string,
     *     next_action: string,
     *     queue: ?ProductionQueue,
     *     queue_id: ?int,
     *     queue_position: ?int,
     *     queue_status: ?string,
     *     stage_name: ?string,
     *     work_center: ?string,
     *     machine_name: ?string,
     *     operator_name: ?string,
     *     has_operator: bool,
     *     has_machine: bool,
     *     needs_operator: bool,
     *     needs_machine: bool,
     *     is_ready_to_start: bool,
     *     already_in_queue: bool,
     *     operators: Collection<int, User>,
     * }
     */
    public function state(ProductionJobCard $jobCard): array
    {
        $jobCard->loadMissing([
            'assignedMachine:id,asset_name,asset_number',
            'queues.workCenter:id,name,code',
            'queues.assignedOperator:id,name',
            'queues.routeStep:id,step_name,sequence',
        ]);

        $context = $this->routeQueues->currentQueueContext($jobCard);
        /** @var ProductionQueue|null $queue */
        $queue = $context['current'];
        $hasOperator = $queue?->assigned_operator_id !== null;
        $hasMachine = $jobCard->assigned_machine_asset_id !== null;
        $needsMachine = $this->floorSettings->plannerAssignsMachines($jobCard->company_id, $jobCard->branch_id)
            && ! $hasMachine;
        $alreadyInQueue = $this->alreadyInQueue($jobCard);
        $isReady = $this->isReadyToStart($jobCard, $queue, $needsMachine);

        $phase = $this->resolvePhase($jobCard, $hasOperator, $needsMachine, $isReady, $alreadyInQueue);
        $dispatchSummary = null;

        if ($jobCard->status === ProductionJobCardStatus::ReadyForDispatch) {
            $dispatchSummary = $this->dispatchPresentation->build($jobCard);

            if ($dispatchSummary['has_delivery_note'] ?? false) {
                $phase = $dispatchSummary['workflow_phase'];
            }
        }

        return [
            'phase' => $phase,
            'phase_label' => $dispatchSummary['workflow_label'] ?? $this->phaseLabel($phase),
            'next_action' => $dispatchSummary['next_action'] ?? $this->nextActionCopy($phase),
            'dispatch_summary' => ($dispatchSummary['has_delivery_note'] ?? false) ? $dispatchSummary : null,
            'queue' => $queue,
            'queue_id' => $queue?->id,
            'queue_position' => $context['position'],
            'queue_status' => $queue?->status?->value,
            'stage_name' => $queue?->routeStep?->step_name
                ?? $context['work_center']?->name
                ?? $queue?->workCenter?->name,
            'work_center' => $context['work_center']?->name ?? $queue?->workCenter?->name,
            'machine_name' => $jobCard->assignedMachine?->asset_name,
            'operator_name' => $queue?->assignedOperator?->name,
            'has_operator' => $hasOperator,
            'has_machine' => $hasMachine,
            'needs_operator' => $alreadyInQueue && ! $hasOperator && in_array($jobCard->status, [
                ProductionJobCardStatus::Draft,
                ProductionJobCardStatus::Queued,
                ProductionJobCardStatus::Rework,
            ], true),
            'needs_machine' => $alreadyInQueue && $needsMachine && in_array($jobCard->status, [
                ProductionJobCardStatus::Draft,
                ProductionJobCardStatus::Queued,
                ProductionJobCardStatus::Rework,
            ], true),
            'is_ready_to_start' => $isReady,
            'already_in_queue' => $alreadyInQueue,
            'operators' => $this->assignableOperators($jobCard),
        ];
    }

    public function isReadyToStart(
        ProductionJobCard $jobCard,
        ?ProductionQueue $queue = null,
        ?bool $needsMachine = null,
    ): bool {
        if (! in_array($jobCard->status, [
            ProductionJobCardStatus::Queued,
            ProductionJobCardStatus::Rework,
        ], true)) {
            return false;
        }

        $queue ??= $this->routeQueues->currentQueueContext($jobCard)['current'] ?? null;

        if ($queue === null || $queue->assigned_operator_id === null) {
            return false;
        }

        $needsMachine ??= $this->floorSettings->plannerAssignsMachines($jobCard->company_id, $jobCard->branch_id)
            && $jobCard->assigned_machine_asset_id === null;

        return ! $needsMachine;
    }

    public function alreadyInQueue(ProductionJobCard $jobCard): bool
    {
        if ($jobCard->relationLoaded('queues')) {
            return $jobCard->queues->contains(
                fn (ProductionQueue $queue) => in_array($queue->status, ProductionQueueStatus::activeStatuses(), true),
            );
        }

        return $jobCard->queues()
            ->whereIn('status', array_map(fn (ProductionQueueStatus $s) => $s->value, ProductionQueueStatus::activeStatuses()))
            ->exists();
    }

    /**
     * @return Collection<int, User>
     */
    public function assignableOperators(ProductionJobCard $jobCard): Collection
    {
        return User::query()
            ->where('company_id', $jobCard->company_id)
            ->where(function ($query) use ($jobCard) {
                $query->where('default_branch_id', $jobCard->branch_id)
                    ->orWhereNull('default_branch_id');
            })
            ->where('is_active', true)
            ->orderBy('name')
            ->limit(100)
            ->get(['id', 'name']);
    }

    protected function resolvePhase(
        ProductionJobCard $jobCard,
        bool $hasOperator,
        bool $needsMachine,
        bool $isReady,
        bool $alreadyInQueue,
    ): string {
        return match ($jobCard->status) {
            ProductionJobCardStatus::OnHold => 'on_hold',
            ProductionJobCardStatus::InProduction => 'in_progress',
            ProductionJobCardStatus::QualityCheck => 'qc',
            ProductionJobCardStatus::AwaitingCustomerApproval => 'awaiting_approval',
            ProductionJobCardStatus::Completed => 'awaiting_fg',
            ProductionJobCardStatus::ReadyForDispatch => 'dispatch',
            ProductionJobCardStatus::Outsourced => 'outsourced',
            ProductionJobCardStatus::Cancelled => 'cancelled',
            ProductionJobCardStatus::Draft => $alreadyInQueue
                ? (! $hasOperator ? 'awaiting_operator' : ($needsMachine ? 'awaiting_machine' : 'awaiting_accept'))
                : 'draft',
            ProductionJobCardStatus::Queued, ProductionJobCardStatus::Rework => ! $hasOperator
                ? 'awaiting_operator'
                : ($needsMachine ? 'awaiting_machine' : ($isReady ? 'ready' : 'queued')),
            default => 'other',
        };
    }

    protected function phaseLabel(string $phase): string
    {
        return match ($phase) {
            'draft' => __('Draft'),
            'awaiting_accept' => __('Queued'),
            'awaiting_operator' => __('Queued'),
            'awaiting_machine' => __('Ready for machine'),
            'ready' => __('Ready'),
            'queued' => __('Queued'),
            'in_progress' => __('In progress'),
            'on_hold' => __('On hold'),
            'qc' => __('Quality check'),
            'awaiting_approval' => __('Awaiting approval'),
            'awaiting_fg' => __('Awaiting finished goods'),
            'dispatch' => __('Ready for dispatch'),
            'dispatch_created' => __('Dispatch created'),
            'dispatched' => __('Dispatched'),
            'delivered' => __('Delivered'),
            'closed' => __('Closed'),
            'outsourced' => __('Outsourced'),
            'cancelled' => __('Cancelled'),
            default => __('In progress'),
        };
    }

    protected function nextActionCopy(string $phase): string
    {
        return match ($phase) {
            'draft' => __('Release this job into the production queue when planning is complete.'),
            'awaiting_accept' => __('Supervisor: confirm queue placement, then assign an operator.'),
            'awaiting_operator' => __('Waiting for operator assignment.'),
            'awaiting_machine' => __('Operator assigned. Assign a machine before work can start.'),
            'ready' => __('Ready for the assigned operator to start work.'),
            'queued' => __('Waiting in queue.'),
            'in_progress' => __('Operator is running this stage. Pause, report issues, or finish when done.'),
            'on_hold' => __('Job is paused. Resume when ready to continue.'),
            'qc' => __('Record quality inspection results.'),
            'awaiting_fg' => __('Post finished goods to unlock dispatch.'),
            'dispatch' => __('Create or continue dispatch / delivery.'),
            default => __('Review job details and continue the next production step.'),
        };
    }
}
