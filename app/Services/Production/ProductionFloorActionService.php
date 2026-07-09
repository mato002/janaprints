<?php

namespace App\Services\Production;

use App\Enums\ProductionJobCardStatus;
use App\Models\Production\ProductionJobCard;
use App\Models\User;
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
    ) {}

    /**
     * One-click operator actions for floor, queue, and sticky action bars.
     *
     * @return list<array<string, mixed>>
     */
    public function operatorActions(ProductionJobCard $jobCard, ?User $user = null): array
    {
        $user ??= auth()->user();
        $actions = [];

        $primary = $this->primaryAction($jobCard, $user);
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
    public function primaryAction(ProductionJobCard $jobCard, ?User $user = null): ?array
    {
        $user ??= auth()->user();

        if (! $user) {
            return null;
        }

        if ($user->can('transition', $jobCard) && $jobCard->status === ProductionJobCardStatus::OnHold) {
            return $this->action(__('Resume'), 'post', route('admin.production.job-cards.resume', $jobCard), 'primary');
        }

        if ($user->can('schedule', $jobCard) && $jobCard->status === ProductionJobCardStatus::Draft) {
            if ($this->queues->hasActiveQueue($jobCard)) {
                return $this->action(__('Queue job'), 'post', route('admin.production.job-cards.queue', $jobCard), 'secondary');
            }

            return $this->action(
                __('Add to queue'),
                'link',
                route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => 'operations']).'#queue-form',
                'primary',
            );
        }

        if ($user->can('start', $jobCard) && $jobCard->status->canTransitionTo(ProductionJobCardStatus::InProduction)) {
            return $this->action(__('Start job'), 'post', route('admin.production.job-cards.start', $jobCard), 'primary');
        }

        if ($user->can('complete', $jobCard) && $jobCard->status->canTransitionTo(ProductionJobCardStatus::QualityCheck)) {
            return $this->action(__('QC ready'), 'post', route('admin.production.job-cards.send-to-qc', $jobCard), 'primary');
        }

        if ($user->can('update', $jobCard) && $jobCard->status->canTransitionTo(ProductionJobCardStatus::Outsourced)) {
            return $this->action(__('Send to vendor'), 'panel', route('admin.production.floor.panel', $jobCard).'#outsource', 'secondary');
        }

        if ($user->can('update', $jobCard) && $jobCard->status === ProductionJobCardStatus::Outsourced) {
            return $this->action(__('Mark returned'), 'panel', route('admin.production.floor.panel', $jobCard).'#outsource', 'primary');
        }

        if ($user->can('create', [\App\Models\Production\QualityCheck::class, $jobCard]) && $jobCard->status === ProductionJobCardStatus::QualityCheck) {
            return $this->action(__('Record QC'), 'panel', route('admin.production.floor.panel', $jobCard).'#quality', 'primary');
        }

        if (
            $user->can('complete', $jobCard)
            && $jobCard->status->canTransitionTo(ProductionJobCardStatus::ReadyForDispatch)
            && $this->controls->dispatchEligibility($jobCard)['eligible']
        ) {
            return $this->action(__('Dispatch ready'), 'post', route('admin.production.job-cards.ready-for-dispatch', $jobCard), 'primary');
        }

        if ($user->can('complete', $jobCard) && $jobCard->status === ProductionJobCardStatus::ReadyForDispatch) {
            return $this->action(__('Hand off'), 'panel', route('admin.production.floor.panel', $jobCard).'#fulfilment', 'primary');
        }

        if ($user->can('production.outputs.post') && ($this->completion->eligibility($jobCard)['eligible'] ?? false)) {
            return $this->action(__('Post finished goods'), 'panel', route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => 'finish']).'#outputs', 'secondary');
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

        if ($user?->can('transition', $jobCard) && $jobCard->status === ProductionJobCardStatus::InProduction) {
            $actions[] = $this->action(__('Pause'), 'post', route('admin.production.job-cards.pause', $jobCard), 'ghost');
        }

        if ($user?->can('transition', $jobCard) && $jobCard->status->canTransitionTo(ProductionJobCardStatus::OnHold)
            && $jobCard->status !== ProductionJobCardStatus::InProduction) {
            $actions[] = $this->action(__('Hold'), 'post', route('admin.production.job-cards.hold', $jobCard), 'ghost');
        }

        if ($user?->can('complete', $jobCard) && $jobCard->status === ProductionJobCardStatus::QualityCheck) {
            $actions[] = $this->action(__('Quick pass QC'), 'post', route('admin.production.floor.quick-pass-qc', $jobCard), 'ghost');
        }

        if ($user?->can('complete', $jobCard) && $jobCard->status->canTransitionTo(ProductionJobCardStatus::Completed)) {
            $actions[] = $this->action(__('Complete'), 'post', route('admin.production.job-cards.complete', $jobCard), 'ghost');
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
                'outputs', 'finish' => 'finish',
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
    protected function action(string $label, string $type, string $url, string $variant): array
    {
        return [
            'label' => $label,
            'type' => $type,
            'url' => $url,
            'variant' => $variant,
        ];
    }
}
