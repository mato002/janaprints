<?php

namespace App\Services\Production;

use App\Enums\ProductionJobCardStatus;
use App\Enums\QualityCheckResult;
use App\Models\Production\ProductionJobCard;
use App\Models\User;
use App\Support\Production\JobCardOutsourceService;
use App\Support\Production\ProductionQcSettings;
use App\Support\Production\QualityInspectionService;

class ProductionFloorActionService
{
    public function __construct(
        protected JobProductionControlService $controls,
        protected JobCardOutsourceService $outsource,
        protected ProductionQcSettings $qcSettings,
        protected ProductionCompletionService $completion,
        protected QualityInspectionService $quality,
    ) {}

    /**
     * @return array<string, mixed>|null
     */
    public function primaryAction(ProductionJobCard $jobCard, ?User $user = null): ?array
    {
        $user ??= auth()->user();

        if (! $user) {
            return null;
        }

        if ($user->can('schedule', $jobCard) && $jobCard->status === ProductionJobCardStatus::Draft) {
            return $this->action(__('Queue job'), 'post', route('admin.production.job-cards.queue', $jobCard), 'secondary');
        }

        if ($user->can('start', $jobCard) && $jobCard->status->canTransitionTo(ProductionJobCardStatus::InProduction)) {
            return $this->action(__('Start job'), 'post', route('admin.production.job-cards.start', $jobCard), 'primary');
        }

        if ($user->can('complete', $jobCard) && $jobCard->status->canTransitionTo(ProductionJobCardStatus::QualityCheck)) {
            return $this->action(__('Send to QC'), 'post', route('admin.production.job-cards.send-to-qc', $jobCard), 'primary');
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
            return $this->action(__('Ready for dispatch'), 'post', route('admin.production.job-cards.ready-for-dispatch', $jobCard), 'primary');
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

        if ($user?->can('view', $jobCard)) {
            $actions[] = $this->action(__('Open job'), 'link', route('admin.production.job-cards.show', $jobCard), 'ghost');
        }

        if ($user?->can('transition', $jobCard) && $jobCard->status->canTransitionTo(ProductionJobCardStatus::OnHold)) {
            $actions[] = $this->action(__('Hold'), 'post', route('admin.production.job-cards.hold', $jobCard), 'ghost');
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
            'result' => QualityCheckResult::Passed->value,
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
                'outputs' => 'finish',
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
