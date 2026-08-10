<?php

namespace App\Services\Production;

use App\Enums\ProductionJobCardStatus;
use App\Models\Production\ProductionJobCard;
use App\Services\Dispatch\JobDispatchPresentationService;

/**
 * Single source of truth for job-card workflow labels, progress, and readiness UI.
 */
class JobWorkflowPresentationService
{
    public function __construct(
        protected ProductionCompletionService $completion,
        protected JobProductionControlService $controls,
        protected JobDispatchPresentationService $dispatchPresentation,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function present(ProductionJobCard $jobCard): array
    {
        $hasPostedOutput = $this->completion->hasPostedFinishedGoods($jobCard);
        $completion = $this->completion->eligibility($jobCard);
        $dispatchSummary = $this->dispatchPresentation->build($jobCard);
        $hasDeliveryNote = (bool) ($dispatchSummary['has_delivery_note'] ?? false);
        $dispatchWorkflow = $this->controls->deliveryNoteWorkflow($jobCard);
        $checklist = collect($this->controls->readinessChecklist($jobCard));

        $phase = $this->resolvePhase($jobCard, $hasPostedOutput, $hasDeliveryNote, $completion, $dispatchWorkflow, $dispatchSummary);
        $readinessItems = $this->readinessItems($jobCard, $checklist, $completion, $hasPostedOutput);

        return [
            'phase' => $phase['key'],
            'phase_label' => $phase['label'],
            'next_action' => $phase['next_action'],
            'next_step' => $phase['next_step'],
            'badges' => $this->resolveBadges($jobCard, $hasPostedOutput, $hasDeliveryNote, $dispatchSummary, $dispatchWorkflow),
            'current_stage_label' => $phase['label'],
            'workflow_progress_percent' => $this->workflowProgressPercent(
                $jobCard,
                $checklist,
                $hasPostedOutput,
                $hasDeliveryNote,
                $dispatchSummary,
                $dispatchWorkflow,
            ),
            'readiness_items' => $readinessItems,
            'readiness_remaining_count' => collect($readinessItems)->where('passed', false)->count(),
            'dispatch_workflow' => $dispatchWorkflow,
            'can_create_delivery_note' => $dispatchWorkflow['eligible'],
        ];
    }

    /**
     * @param  array<string, mixed>  $completion
     * @param  array<string, mixed>  $dispatchWorkflow
     * @param  array<string, mixed>  $dispatchSummary
     * @return array{key: string, label: string, next_action: string, next_step: ?array{label: string, url: string}}
     */
    protected function resolvePhase(
        ProductionJobCard $jobCard,
        bool $hasPostedOutput,
        bool $hasDeliveryNote,
        array $completion,
        array $dispatchWorkflow,
        array $dispatchSummary,
    ): array {
        if ($hasDeliveryNote) {
            return [
                'key' => 'dispatch_created',
                'label' => $dispatchSummary['workflow_label'] ?? __('Dispatch in progress'),
                'next_action' => $dispatchSummary['next_action'] ?? __('Manage delivery on the delivery note.'),
                'next_step' => null,
            ];
        }

        if (in_array($jobCard->status, [ProductionJobCardStatus::Completed, ProductionJobCardStatus::ReadyForDispatch], true)) {
            if (! $hasPostedOutput) {
                return [
                    'key' => 'awaiting_fg_post',
                    'label' => __('Post finished goods'),
                    'next_action' => $this->firstBlockerMessage($completion['blockers'] ?? [])
                        ?? __('Post finished goods to inventory before dispatch can begin.'),
                    'next_step' => [
                        'label' => __('Post finished goods'),
                        'url' => route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => 'outputs']),
                    ],
                ];
            }

            if (! $dispatchWorkflow['eligible']) {
                $nextStep = $dispatchWorkflow['next_step'] ?? null;

                return [
                    'key' => 'dispatch_blocked',
                    'label' => $dispatchWorkflow['status_label'] ?? __('Dispatch blocked'),
                    'next_action' => $this->firstBlockerMessage($dispatchWorkflow['blockers'] ?? [])
                        ?? __('Complete the remaining requirements before creating a delivery note.'),
                    'next_step' => $nextStep,
                ];
            }

            return [
                'key' => 'dispatch',
                'label' => __('Ready for delivery note'),
                'next_action' => __('Create a delivery note to begin outbound dispatch.'),
                'next_step' => [
                    'label' => __('Open dispatch'),
                    'url' => route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => 'dispatch']),
                ],
            ];
        }

        return [
            'key' => 'other',
            'label' => $jobCard->status->label(),
            'next_action' => __('Continue the current production step.'),
            'next_step' => null,
        ];
    }

    /**
     * @param  array<string, mixed>  $dispatchSummary
     * @param  array<string, mixed>  $dispatchWorkflow
     * @return list<array{label: string, variant: string}>
     */
    protected function resolveBadges(
        ProductionJobCard $jobCard,
        bool $hasPostedOutput,
        bool $hasDeliveryNote,
        array $dispatchSummary,
        array $dispatchWorkflow,
    ): array {
        if ($hasDeliveryNote) {
            return [[
                'label' => $dispatchSummary['workflow_label'] ?? __('Dispatch'),
                'variant' => match ($dispatchSummary['workflow_phase'] ?? '') {
                    'delivered', 'closed' => 'success',
                    'dispatched' => 'in_production',
                    default => 'neutral',
                },
            ]];
        }

        if (in_array($jobCard->status, [ProductionJobCardStatus::Completed, ProductionJobCardStatus::ReadyForDispatch], true)) {
            $badges = [['label' => __('Production complete'), 'variant' => 'success']];

            if (! $hasPostedOutput) {
                $badges[] = ['label' => __('Finished goods pending'), 'variant' => 'warning'];

                return $badges;
            }

            if (! $dispatchWorkflow['eligible']) {
                $badges[] = [
                    'label' => $dispatchWorkflow['status_label'] ?? __('Dispatch blocked'),
                    'variant' => 'warning',
                ];

                return $badges;
            }

            $badges[] = ['label' => __('Ready for delivery note'), 'variant' => 'success'];

            return $badges;
        }

        return [[
            'label' => $jobCard->status->label(),
            'variant' => match ($jobCard->status) {
                ProductionJobCardStatus::InProduction, ProductionJobCardStatus::QualityCheck => 'in_production',
                ProductionJobCardStatus::Cancelled => 'danger',
                default => 'neutral',
            },
        ]];
    }

    /**
     * @param  \Illuminate\Support\Collection<int, array<string, mixed>>  $checklist
     * @param  array<string, mixed>  $completion
     * @return list<array{passed: bool, label: string, action: ?string, action_label: ?string, hint: ?string}>
     */
    protected function readinessItems(
        ProductionJobCard $jobCard,
        $checklist,
        array $completion,
        bool $hasPostedOutput,
    ): array {
        $operations = $checklist->firstWhere('key', 'operations');
        $qc = $checklist->firstWhere('key', 'qc');
        $materials = $checklist->firstWhere('key', 'materials');
        $blockerCodes = $completion['blocker_codes'] ?? [];
        $fgWarehouse = $completion['fg_warehouse'] ?? null;

        $productionPassed = ($operations['state'] ?? null) === 'passed'
            || in_array($jobCard->status, [
                ProductionJobCardStatus::Completed,
                ProductionJobCardStatus::ReadyForDispatch,
            ], true);

        $items = [];

        $items[] = [
            'passed' => $productionPassed,
            'label' => __('Production complete'),
            'action' => null,
            'action_label' => null,
            'hint' => null,
        ];

        $items[] = [
            'passed' => ($qc['state'] ?? null) === 'passed',
            'label' => __('QC approved'),
            'action' => ($qc['state'] ?? null) !== 'passed'
                ? route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => 'quality'])
                : null,
            'action_label' => __('Open QC'),
            'hint' => null,
        ];

        $materialsPassed = ($materials['state'] ?? null) === 'passed';
        $items[] = [
            'passed' => $materialsPassed,
            'label' => $materialsPassed
                ? __('Material consumption recorded')
                : __('Material consumption missing'),
            'action' => ! $materialsPassed
                ? route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => 'material-consumption', 'open' => 'record-consumption-modal'])
                : null,
            'action_label' => __('Record consumption'),
            'hint' => null,
        ];

        if (in_array('fg_warehouse', $blockerCodes, true)) {
            $items[] = [
                'passed' => false,
                'label' => __('Finished goods warehouse setup incomplete'),
                'action' => null,
                'action_label' => null,
                'hint' => __('Create a branch for this company, then use Verify defaults on Virtual Locations (Inventory → Store Operations).'),
            ];
        } elseif ($fgWarehouse) {
            $items[] = [
                'passed' => true,
                'label' => __('Finished goods warehouse (:code)', ['code' => $fgWarehouse['code']]),
                'action' => null,
                'action_label' => null,
                'hint' => null,
            ];
        }

        if (in_array('stock_role', $blockerCodes, true)) {
            $productItem = $jobCard->inventoryItem;
            $items[] = [
                'passed' => false,
                'label' => __('Product stock role incorrect'),
                'action' => $productItem ? route('admin.inventory.items.edit', $productItem) : route('admin.inventory.items.index'),
                'action_label' => __('Open product'),
                'hint' => null,
            ];
        }

        if ($hasPostedOutput) {
            $items[] = [
                'passed' => true,
                'label' => __('Finished goods posted'),
                'action' => null,
                'action_label' => null,
                'hint' => null,
            ];
        } elseif (($completion['eligible'] ?? false) && $productionPassed && $materialsPassed) {
            $items[] = [
                'passed' => true,
                'label' => __('Ready to post finished goods'),
                'action' => null,
                'action_label' => null,
                'hint' => null,
            ];
        } elseif ($jobCard->status === ProductionJobCardStatus::ReadyForDispatch && ! $hasPostedOutput) {
            $items[] = [
                'passed' => false,
                'label' => __('Finished goods not posted'),
                'action' => route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => 'outputs']),
                'action_label' => __('Post finished goods'),
                'hint' => null,
            ];
        }

        $dispatchWorkflow = $this->controls->deliveryNoteWorkflow($jobCard);
        if ($hasPostedOutput && ! $dispatchWorkflow['eligible']) {
            $nextStep = $dispatchWorkflow['next_step'] ?? null;
            $items[] = [
                'passed' => false,
                'label' => $dispatchWorkflow['status_label'] ?? __('Dispatch blocked'),
                'action' => $nextStep['url'] ?? route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => 'dispatch']),
                'action_label' => $nextStep['label'] ?? __('Open dispatch'),
                'hint' => $this->firstBlockerMessage($dispatchWorkflow['blockers'] ?? []),
            ];
        } elseif ($hasPostedOutput && $dispatchWorkflow['eligible']) {
            $items[] = [
                'passed' => true,
                'label' => __('Ready for delivery note'),
                'action' => route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => 'dispatch']),
                'action_label' => __('Open dispatch'),
                'hint' => null,
            ];
        }

        return $items;
    }

    /**
     * @param  \Illuminate\Support\Collection<int, array<string, mixed>>  $checklist
     * @param  array<string, mixed>  $dispatchSummary
     * @param  array<string, mixed>  $dispatchWorkflow
     */
    protected function workflowProgressPercent(
        ProductionJobCard $jobCard,
        $checklist,
        bool $hasPostedOutput,
        bool $hasDeliveryNote,
        array $dispatchSummary,
        array $dispatchWorkflow,
    ): int {
        $materials = $checklist->firstWhere('key', 'materials');
        $operations = $checklist->firstWhere('key', 'operations');
        $qc = $checklist->firstWhere('key', 'qc');

        $materialsDone = in_array($materials['state'] ?? null, ['passed', 'warning'], true)
            || ($jobCard->material_consumptions_count ?? 0) > 0;
        $productionDone = ($operations['state'] ?? null) === 'passed'
            || in_array($jobCard->status, [ProductionJobCardStatus::Completed, ProductionJobCardStatus::ReadyForDispatch, ProductionJobCardStatus::QualityCheck], true);
        $qcDone = ($qc['state'] ?? null) === 'passed';
        $fgDone = $hasPostedOutput;
        $dispatchDone = $hasDeliveryNote && in_array($dispatchSummary['workflow_phase'] ?? '', ['delivered', 'closed'], true);
        $dispatchReady = $dispatchWorkflow['eligible'] ?? false;

        $score = 0;
        if ($materialsDone) {
            $score += 15;
        }
        if ($productionDone) {
            $score += 25;
        }
        if ($qcDone) {
            $score += 20;
        }
        if ($fgDone) {
            $score += 25;
        }
        if ($dispatchDone) {
            $score += 15;
        } elseif ($dispatchReady || $hasDeliveryNote) {
            $score += 10;
        }

        return min(100, $score);
    }

    /**
     * @param  list<string>  $blockers
     */
    protected function firstBlockerMessage(array $blockers): ?string
    {
        return $blockers[0] ?? null;
    }
}
