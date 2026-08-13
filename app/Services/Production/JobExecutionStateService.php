<?php

namespace App\Services\Production;

use App\Enums\ProductionJobCardStatus;
use App\Enums\ProductionQueueStatus;
use App\Models\Production\ProductionJobCard;
use App\Models\Production\ProductionOperation;
use App\Models\Production\ProductionQueue;
use App\Models\Production\WorkCenter;
use App\Models\User;
use App\Services\Dispatch\JobDispatchPresentationService;
use App\Support\Production\RouteStepQueueService;
use Illuminate\Support\Collection;

/**
 * Derives supervisor/operator execution readiness for job cards already released from Sales.
 *
 * Operator and machine assignment are optional helpers — they never block start.
 * `needs_operator` / `needs_machine` mean "not yet assigned" for the assign UI.
 */
class JobExecutionStateService
{
    public function __construct(
        protected RouteStepQueueService $routeQueues,
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
     *     requires_machine: bool,
     *     needs_operator: bool,
     *     needs_machine: bool,
     *     is_ready_to_start: bool,
     *     already_in_queue: bool,
     *     readiness_facts: list<array{label: string, value: string, tone: string}>,
     *     operators: Collection<int, User>,
     * }
     */
    public function state(ProductionJobCard $jobCard): array
    {
        $jobCard->loadMissing([
            'assignedMachine:id,asset_name,asset_number',
            'queues.workCenter:id,name,code,requires_machine',
            'queues.assignedOperator:id,name',
            'queues.routeStep:id,step_name,sequence',
        ]);

        $context = $this->routeQueues->currentQueueContext($jobCard);
        /** @var ProductionQueue|null $queue */
        $queue = $context['current'];
        $workCenter = $context['work_center'] ?? $queue?->workCenter;
        $stageName = $queue?->routeStep?->step_name
            ?? $workCenter?->name
            ?? $queue?->workCenter?->name;
        $requiresMachine = $this->stageRequiresMachine($workCenter);
        $operationOperator = $this->assignedOperationOperator($jobCard, $queue);
        $hasOperator = $queue?->assigned_operator_id !== null || $operationOperator !== null;
        $operatorName = $queue?->assignedOperator?->name ?? $operationOperator;
        $hasMachine = $jobCard->assigned_machine_asset_id !== null;
        $needsMachine = $requiresMachine && ! $hasMachine;
        $alreadyInQueue = $this->alreadyInQueue($jobCard);
        $isReady = $this->isReadyToStart($jobCard, $queue);

        $phase = $this->resolvePhase($jobCard, $isReady, $alreadyInQueue);
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
            'next_action' => $dispatchSummary['next_action'] ?? $this->nextActionCopy(
                $phase,
                $stageName,
                $operatorName,
                $requiresMachine,
                $jobCard->assignedMachine?->asset_name,
            ),
            'dispatch_summary' => ($dispatchSummary['has_delivery_note'] ?? false) ? $dispatchSummary : null,
            'queue' => $queue,
            'queue_id' => $queue?->id,
            'queue_position' => $context['position'],
            'queue_status' => $queue?->status?->value,
            'stage_name' => $stageName,
            'work_center' => $workCenter?->name ?? $queue?->workCenter?->name,
            'machine_name' => $jobCard->assignedMachine?->asset_name,
            'operator_name' => $operatorName,
            'has_operator' => $hasOperator,
            'has_machine' => $hasMachine,
            'requires_machine' => $requiresMachine,
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
            'readiness_facts' => $this->readinessFacts(
                $jobCard,
                $phase,
                $stageName,
                $operatorName,
                $requiresMachine,
                $hasMachine,
                $jobCard->assignedMachine?->asset_name,
            ),
            'operators' => $this->assignableOperators($jobCard),
        ];
    }

    public function isReadyToStart(
        ProductionJobCard $jobCard,
        ?ProductionQueue $queue = null,
        ?bool $needsMachine = null,
    ): bool {
        unset($needsMachine);
        if (! in_array($jobCard->status, [
            ProductionJobCardStatus::Queued,
            ProductionJobCardStatus::Rework,
        ], true)) {
            return false;
        }

        if ($queue === null) {
            $queue = $this->routeQueues->currentQueueContext($jobCard)['current'] ?? null;
        }

        return $queue !== null;
    }

    public function stageRequiresMachine(?WorkCenter $workCenter): bool
    {
        if ($workCenter === null) {
            return false;
        }

        if (! array_key_exists('requires_machine', $workCenter->getAttributes())) {
            return (bool) WorkCenter::query()->whereKey($workCenter->id)->value('requires_machine');
        }

        return (bool) $workCenter->requires_machine;
    }

    protected function assignedOperationOperator(ProductionJobCard $jobCard, ?ProductionQueue $queue): ?string
    {
        $query = ProductionOperation::query()
            ->where('production_job_card_id', $jobCard->id)
            ->whereNull('ended_at')
            ->whereNotNull('assigned_employee_id')
            ->with('assignedEmployee:id,first_name,last_name,middle_name');

        if ($queue?->work_center_id) {
            $matched = (clone $query)->where('work_center_id', $queue->work_center_id)->latest('started_at')->first();

            if ($matched?->assignedEmployee) {
                return $matched->assignedEmployee->full_name
                    ?: trim($matched->assignedEmployee->first_name.' '.$matched->assignedEmployee->last_name);
            }
        }

        $operation = $query->latest('started_at')->first();

        if (! $operation?->assignedEmployee) {
            return null;
        }

        return $operation->assignedEmployee->full_name
            ?: trim($operation->assignedEmployee->first_name.' '.$operation->assignedEmployee->last_name);
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
            ->where(function ($query) {
                // Active logins, or HR-onboarded staff awaiting activation.
                $query->where('is_active', true)
                    ->orWhereNotNull('employee_id');
            })
            ->orderBy('name')
            ->limit(100)
            ->get(['id', 'name']);
    }

    protected function resolvePhase(
        ProductionJobCard $jobCard,
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
                ? ($isReady ? 'awaiting_accept' : 'queued')
                : 'draft',
            ProductionJobCardStatus::Queued, ProductionJobCardStatus::Rework => $isReady ? 'ready' : 'queued',
            default => 'other',
        };
    }

    protected function phaseLabel(string $phase): string
    {
        return match ($phase) {
            'draft' => __('Draft'),
            'awaiting_accept' => __('Queued'),
            'awaiting_operator' => __('Queued'),
            'awaiting_machine' => __('Machine required'),
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

    protected function nextActionCopy(
        string $phase,
        ?string $stageName,
        ?string $operatorName,
        bool $requiresMachine,
        ?string $machineName,
    ): string {
        $stage = $stageName ?: __('this stage');

        return match ($phase) {
            'draft' => __('Release this job into the production queue when planning is complete.'),
            'awaiting_accept' => __('Supervisor: confirm queue placement. Operator and machine can be assigned if needed.'),
            'awaiting_operator' => __('Operator is optional. Assign one if you want them on :stage.', ['stage' => $stage]),
            'awaiting_machine' => __('Machine is optional. Assign one if you want it on :stage.', ['stage' => $stage]),
            'ready' => $this->readyToStartCopy($operatorName, $requiresMachine, $machineName, $stage),
            'queued' => __('Waiting in queue.'),
            'in_progress' => __('Operator is running this stage. Pause, report issues, or finish when done.'),
            'on_hold' => __('Job is paused. Resume when ready to continue.'),
            'qc' => __('Record quality inspection results.'),
            'awaiting_fg' => __('Post finished goods to unlock dispatch.'),
            'dispatch' => __('Create or continue dispatch / delivery.'),
            default => __('Review job details and continue the next production step.'),
        };
    }

    /**
     * @return list<array{label: string, value: string, tone: string}>
     */
    protected function readinessFacts(
        ProductionJobCard $jobCard,
        string $phase,
        ?string $stageName,
        ?string $operatorName,
        bool $requiresMachine,
        bool $hasMachine,
        ?string $machineName,
    ): array {
        if (! in_array($phase, ['awaiting_operator', 'awaiting_machine', 'ready', 'awaiting_accept', 'queued'], true)) {
            return [];
        }

        $facts = [
            [
                'label' => __('Assigned operator'),
                'value' => $operatorName ?: __('Not assigned'),
                'tone' => $operatorName ? 'ok' : 'warn',
            ],
        ];

        if ($requiresMachine) {
            $facts[] = [
                'label' => __('Machine'),
                'value' => $hasMachine
                    ? ($machineName ?: __('Assigned'))
                    : __('Not assigned'),
                'tone' => $hasMachine ? 'ok' : 'warn',
            ];
        } else {
            $facts[] = [
                'label' => __('Machine'),
                'value' => __('Not required for :stage', [
                    'stage' => $stageName ?: __('this stage'),
                ]),
                'tone' => 'ok',
            ];
        }

        $materials = app(\App\Support\Production\MaterialReadinessService::class)->assess($jobCard);
        $facts[] = [
            'label' => __('Materials'),
            'value' => $materials['ready']
                ? __('Ready (:percent%)', ['percent' => $materials['percent']])
                : ($materials['has_requirements']
                    ? __('Not ready (:percent%)', ['percent' => $materials['percent']])
                    : __('Requirements missing')),
            'tone' => $materials['ready'] ? 'ok' : 'warn',
        ];

        return $facts;
    }

    protected function readyToStartCopy(
        ?string $operatorName,
        bool $requiresMachine,
        ?string $machineName,
        string $stage,
    ): string {
        if ($operatorName && $requiresMachine && $machineName) {
            return __('Ready to start — :name on :machine for :stage.', [
                'name' => $operatorName,
                'machine' => $machineName,
                'stage' => $stage,
            ]);
        }

        if ($operatorName) {
            return __('Ready to start — operator :name assigned. Machine is optional.', [
                'name' => $operatorName,
            ]);
        }

        return __('Ready to start. Operator and machine are optional.');
    }
}
