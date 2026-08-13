<?php

namespace App\Services\Production;

use App\Enums\ProductionJobCardStatus;
use App\Models\Production\ProductionJobCard;
use App\Models\User;
use App\Services\Dispatch\JobDispatchPresentationService;
use App\Support\Production\JobCardOutsourceService;
use App\Support\Production\ProductionQcSettings;
use App\Support\Production\ProductionQueueService;
use App\Support\Production\QualityInspectionService;

class ProductionFloorActionService
{
    public function __construct(
        protected JobProductionControlService $controls,
        protected JobCardOutsourceService $outsource,
        protected ProductionQcSettings $qcSettings,
        protected ProductionCompletionService $completion,
        protected QualityInspectionService $quality,
        protected ProductionQueueService $queues,
        protected JobExecutionStateService $execution,
        protected JobDispatchPresentationService $dispatchPresentation,
    ) {}

    /**
     * One-click operator actions for floor, queue, and sticky action bars.
     *
     * @return list<array<string, mixed>>
     */
    public function operatorActions(ProductionJobCard $jobCard, ?User $user = null, bool $forFloor = false): array
    {
        $user ??= auth()->user();
        $actions = [];

        $primary = $this->primaryAction($jobCard, $user, $forFloor);
        if ($primary) {
            $actions[] = $primary;
        }

        foreach ($this->secondaryActions($jobCard, $user) as $action) {
            $actions[] = $action;
        }

        return $actions;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function primaryAction(ProductionJobCard $jobCard, ?User $user = null, bool $forFloor = false): ?array
    {
        $user ??= auth()->user();

        if (! $user) {
            return null;
        }

        $state = $this->execution->state($jobCard);

        if ($user->can('transition', $jobCard) && $jobCard->status === ProductionJobCardStatus::OnHold) {
            return $this->action(__('Resume'), 'post', route('admin.production.job-cards.resume', $jobCard), 'primary');
        }

        // Supervisor path: jobs already released from Sales stay Queued until assigned.
        if (
            $state['needs_operator']
            && $state['queue_id']
            && ($user->can('schedule', $jobCard) || $user->can('update', $jobCard))
        ) {
            if ($forFloor) {
                return $this->floorModal(__('Assign operator'), 'operator', $jobCard, 'primary');
            }

            return $this->action(
                __('Assign operator'),
                'link',
                route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => 'overview']).'#assign-operator',
                'primary',
            );
        }

        if ($state['needs_machine'] && $user->can('machines.assign')) {
            if ($forFloor) {
                return $this->floorModal(__('Assign machine'), 'machine', $jobCard, 'primary');
            }

            return $this->action(
                __('Assign machine'),
                'link',
                route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => 'overview']).'#assign-machine',
                'primary',
            );
        }

        // Only Draft jobs with no queue still need planning entry points.
        if (
            ! $forFloor
            && ! $state['already_in_queue']
            && $user->can('schedule', $jobCard)
            && $jobCard->status === ProductionJobCardStatus::Draft
        ) {
            $materialsReady = app(\App\Support\Production\MaterialReadinessService::class)->assess($jobCard)['ready'];

            if (! $materialsReady) {
                return $this->action(
                    __('Resolve materials'),
                    'link',
                    route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => 'materials']),
                    'primary',
                );
            }

            return $this->action(
                __('Add to queue'),
                'link',
                route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => 'operations']).'#queue-form',
                'primary',
            );
        }

        if (
            $state['is_ready_to_start']
            && $user->can('start', $jobCard)
            && $jobCard->status->canTransitionTo(ProductionJobCardStatus::InProduction)
        ) {
            return $this->action(__('Start work'), 'post', route('admin.production.job-cards.start', $jobCard), 'primary');
        }

        // Operator/machine ready but materials block start — surface the gate in the CTA.
        if (
            $user->can('start', $jobCard)
            && $jobCard->status->canTransitionTo(ProductionJobCardStatus::InProduction)
            && ($state['has_operator'] ?? false)
            && ! ($state['needs_machine'] ?? false)
            && ! ($state['is_ready_to_start'] ?? false)
        ) {
            $assessment = app(\App\Support\Production\MaterialReadinessService::class)->assess($jobCard);
            if (! $assessment['ready']) {
                return $this->action(
                    __('Materials blocked'),
                    'link',
                    $assessment['materials_url'],
                    'primary',
                );
            }
        }

        if ($user->can('complete', $jobCard) && $jobCard->status->canTransitionTo(ProductionJobCardStatus::QualityCheck)) {
            return $this->action(__('Complete stage'), 'post', route('admin.production.job-cards.send-to-qc', $jobCard), 'primary');
        }

        if ($user->can('update', $jobCard) && $jobCard->status->canTransitionTo(ProductionJobCardStatus::Outsourced)) {
            if ($forFloor) {
                return $this->floorModal(__('Send to vendor'), 'outsource-send', $jobCard, 'secondary');
            }

            return $this->action(__('Send to vendor'), 'panel', route('admin.production.floor.panel', $jobCard).'#outsource', 'secondary');
        }

        if ($user->can('update', $jobCard) && $jobCard->status === ProductionJobCardStatus::Outsourced) {
            if ($forFloor) {
                return $this->floorModal(__('Mark returned'), 'outsource-return', $jobCard, 'primary');
            }

            return $this->action(__('Mark returned'), 'panel', route('admin.production.floor.panel', $jobCard).'#outsource', 'primary');
        }

        if ($user->can('create', [\App\Models\Production\QualityCheck::class, $jobCard]) && $jobCard->status === ProductionJobCardStatus::QualityCheck) {
            if ($forFloor) {
                return $this->floorModal(__('QC'), 'qc', $jobCard, 'primary');
            }

            return $this->action(__('QC'), 'panel', route('admin.production.floor.panel', $jobCard).'#quality', 'primary');
        }

        if ($user->can('production.outputs.post') && ! $this->completion->hasPostedFinishedGoods($jobCard)) {
            $outputsUrl = route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => 'outputs']).'#outputs';

            if (in_array($jobCard->status, [ProductionJobCardStatus::Completed, ProductionJobCardStatus::ReadyForDispatch], true)) {
                return $this->action(__('Post finished goods'), 'panel', $outputsUrl, 'primary');
            }

            if ($this->completion->eligibility($jobCard)['eligible'] ?? false) {
                return $this->action(__('Post finished goods'), 'panel', $outputsUrl, 'secondary');
            }
        }

        if ($this->controls->hasIncompleteOperations($jobCard) && $user->can('complete', $jobCard)) {
            return $this->action(
                __('Finish remaining operations'),
                'link',
                route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => 'operations']).'#open-operations',
                'primary',
            );
        }

        if (
            $user->can('complete', $jobCard)
            && $jobCard->status->canTransitionTo(ProductionJobCardStatus::ReadyForDispatch)
            && $this->controls->dispatchEligibility($jobCard)['eligible']
            && ! ($this->dispatchPresentation->build($jobCard)['has_delivery_note'] ?? false)
        ) {
            return $this->action(__('Ready for dispatch'), 'post', route('admin.production.job-cards.ready-for-dispatch', $jobCard), 'primary');
        }

        if ($user->can('complete', $jobCard) && $jobCard->status === ProductionJobCardStatus::ReadyForDispatch) {
            $dispatch = $this->dispatchPresentation->build($jobCard);

            if ($dispatch['has_delivery_note'] ?? false) {
                $primary = $dispatch['actions']['primary'];

                return $this->action(
                    $primary['label'],
                    $primary['type'],
                    $primary['url'],
                    $primary['variant'] ?? 'primary',
                );
            }

            if ($forFloor) {
                return $this->floorModal(__('Hand off'), 'fulfilment', $jobCard, 'primary');
            }

            return $this->action(__('Hand off'), 'panel', route('admin.production.floor.panel', $jobCard).'#fulfilment', 'primary');
        }

        return null;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function secondaryActions(ProductionJobCard $jobCard, ?User $user = null): array
    {
        $user ??= auth()->user();
        $actions = [];
        $state = $this->execution->state($jobCard);

        if ($user?->can('transition', $jobCard) && $jobCard->status === ProductionJobCardStatus::InProduction) {
            $actions[] = $this->action(__('Pause'), 'post', route('admin.production.job-cards.pause', $jobCard), 'ghost');
        }

        // Hold is a planning escape hatch — not shown while waiting for assignment.
        if (
            $user?->can('transition', $jobCard)
            && $jobCard->status->canTransitionTo(ProductionJobCardStatus::OnHold)
            && $jobCard->status !== ProductionJobCardStatus::InProduction
            && ! in_array($state['phase'], ['awaiting_operator', 'awaiting_machine', 'ready'], true)
        ) {
            $actions[] = $this->action(__('Hold'), 'post', route('admin.production.job-cards.hold', $jobCard), 'ghost');
        }

        if ($user?->can('complete', $jobCard) && $jobCard->status === ProductionJobCardStatus::QualityCheck) {
            $actions[] = $this->action(__('Quick pass QC'), 'post', route('admin.production.floor.quick-pass-qc', $jobCard), 'ghost');
        }

        if ($user?->can('complete', $jobCard) && $jobCard->status->canTransitionTo(ProductionJobCardStatus::Completed)) {
            $actions[] = $this->action(__('Complete'), 'post', route('admin.production.job-cards.complete', $jobCard), 'ghost');
        }

        if ($jobCard->status === ProductionJobCardStatus::ReadyForDispatch) {
            $dispatch = $this->dispatchPresentation->build($jobCard);

            if ($dispatch['has_delivery_note'] ?? false) {
                foreach ($dispatch['actions']['secondary'] ?? [] as $action) {
                    $actions[] = $this->action(
                        $action['label'],
                        $action['type'],
                        $action['url'],
                        $action['variant'] ?? 'ghost',
                        $action['target'] ?? null,
                    );
                }
            }
        }

        return $actions;
    }

    /**
     * Quick QC pass from floor panel.
     */
    public function quickPassQc(ProductionJobCard $jobCard, User $user): ProductionJobCard
    {
        abort_unless($user->can('create', [\App\Models\Production\QualityCheck::class, $jobCard]), 403);
        abort_unless($jobCard->status === ProductionJobCardStatus::QualityCheck, 422);

        $this->quality->recordInspection($jobCard, [
            'result' => \App\Enums\QualityCheckResult::Passed->value,
            'comments' => __('Quick pass from production floor.'),
        ], (int) $user->id);

        return $jobCard->fresh();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function adaptForJobWorkspace(?array $action, ProductionJobCard $jobCard): ?array
    {
        if (! $action) {
            return null;
        }

        if ($action['type'] === 'panel') {
            $fragment = parse_url($action['url'], PHP_URL_FRAGMENT) ?: '';
            $tab = match ($fragment) {
                'quality' => 'quality',
                'fulfilment' => 'dispatch',
                'outputs' => 'outputs',
                'queue-form' => 'operations',
                default => 'overview',
            };

            $action['type'] = 'link';
            $action['url'] = route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => $tab])
                .($fragment !== '' ? '#'.$fragment : '');
        }

        return $action;
    }

    /**
     * @param  list<array<string, mixed>>  $actions
     * @return list<array<string, mixed>>
     */
    public function adaptSecondaryForJobWorkspace(array $actions, ProductionJobCard $jobCard): array
    {
        return array_values(array_map(
            fn (array $action) => $this->adaptForJobWorkspace($action, $jobCard) ?? $action,
            $actions,
        ));
    }

    /**
     * @return array<string, mixed>
     */
    protected function floorModal(string $label, string $target, ProductionJobCard $jobCard, string $variant): array
    {
        return $this->action($label, 'modal', route('admin.production.floor.panel', $jobCard), $variant, $target);
    }

    /**
     * @return array<string, mixed>
     */
    protected function action(string $label, string $type, string $url, string $variant, ?string $target = null): array
    {
        $action = [
            'label' => $label,
            'type' => $type,
            'url' => $url,
            'variant' => $variant,
        ];

        if ($target !== null) {
            $action['target'] = $target;
        }

        return $action;
    }
}
