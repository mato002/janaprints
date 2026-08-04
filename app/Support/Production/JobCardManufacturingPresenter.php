<?php

namespace App\Support\Production;

use App\Enums\ProductionJobCardStatus;
use App\Enums\ProductionSpecificationApprovalStatus;
use App\Models\Production\ProductionJobCard;
use App\Models\Production\ProductionSpecification;

class JobCardManufacturingPresenter
{
    public function __construct(
        protected JobCardSpecificationBridgeService $bridge,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function present(ProductionJobCard $jobCard): array
    {
        $jobCard->loadMissing(['costSheet', 'assignedMachine', 'salesOrder.items', 'creator:id,name']);

        $spec = $this->bridge->resolveForJobCard($jobCard);
        $template = $spec?->printProductTemplate;
        $queue = app(RouteStepQueueService::class)->currentQueueContext($jobCard);
        if ($queue['current'] && ! $queue['current']->relationLoaded('assignedOperator')) {
            $queue['current']->load('assignedOperator:id,name');
        }
        $costSheet = $jobCard->costSheet;

        if (! $spec) {
            return [
                'has_specification' => false,
                'empty_message' => __('No structured Production Specification available.'),
                'legacy' => $this->legacyFallback($jobCard),
                'timeline_pipeline' => $this->timelinePipeline($jobCard, null),
                'operators' => $this->operators($jobCard, $queue),
                'cost_summary' => $this->costSummary($costSheet),
            ];
        }

        $gsm = $template?->gsm ?? $this->extractGsmFromNotes($spec->production_notes);
        $numberOfColours = $template?->number_of_colours ?? $this->parseColourCount($spec->colour_mode);

        return [
            'has_specification' => true,
            'specification_id' => $spec->id,
            'template_name' => $template?->name,
            'edit_url' => $this->editUrl($spec),
            'sections' => [
                'general' => $this->fields([
                    __('Product') => $spec->product_description ?? $jobCard->inventoryItem?->item_name,
                    __('Quantity') => $spec->quantity !== null ? number_format((float) $spec->quantity, 0) : null,
                    __('Unit') => $spec->unit,
                    __('Production type') => $spec->production_type?->value
                        ? str_replace('_', ' ', ucfirst($spec->production_type->value))
                        : str_replace('_', ' ', ucfirst($jobCard->production_type->value)),
                ]),
                'material' => $this->fields([
                    __('Paper') => $spec->paperInventoryItem?->item_name,
                    __('Material') => $spec->materialInventoryItem?->item_name,
                    __('GSM') => $gsm,
                    __('Sheet size') => $spec->sheet_size,
                    __('Finished size') => $spec->finished_size ?? $spec->size,
                ]),
                'printing' => $this->fields([
                    __('Colour mode') => $spec->colour_mode,
                    __('Number of colours') => $numberOfColours,
                    __('Sides') => $spec->sides,
                    __('Ink type') => $spec->ink_type?->label(),
                ]),
                'finishing' => $this->finishingFields($spec),
                'production' => $this->fields([
                    __('Ups') => $spec->ups,
                    __('Estimated sheets') => $spec->estimated_sheets,
                    __('Waste allowance') => $spec->waste_allowance_percent !== null
                        ? number_format((float) $spec->waste_allowance_percent, 1).'%'
                        : null,
                    __('Preferred machine') => $template?->preferredMachineAsset?->asset_name
                        ?? $jobCard->assignedMachine?->asset_name,
                    __('Preferred department') => $spec->production_type?->value
                        ? str_replace('_', ' ', ucfirst($spec->production_type->value))
                        : null,
                ]),
                'artwork' => $this->fields([
                    __('Artwork reference') => $spec->artwork_reference ?? $jobCard->artworkRequest?->request_number,
                    __('Artwork version') => $spec->artwork_version ?? $jobCard->artworkRequest?->current_version,
                    __('Approval status') => $spec->approval_status?->label(),
                ]),
                'delivery' => $this->fields([
                    __('Delivery notes') => $spec->delivery_notes,
                ]),
                'notes' => $this->fields([
                    __('Production notes') => $spec->production_notes,
                ]),
            ],
            'material_plan' => [
                'paper' => $spec->paperInventoryItem?->item_name,
                'estimated_sheets' => $spec->estimated_sheets,
                'waste_percent' => $spec->waste_allowance_percent,
                'quantity' => $spec->quantity,
            ],
            'recommendations' => [
                'work_center' => $template?->preferredWorkCenter?->name ?? $queue['work_center']?->name,
                'machine' => $template?->preferredMachineAsset?->asset_name ?? $jobCard->assignedMachine?->asset_name,
                'department' => $spec->production_type?->value
                    ? str_replace('_', ' ', ucfirst($spec->production_type->value))
                    : null,
                'operator_skill' => $template?->preferred_operator_skill,
                'packaging' => $template?->recommended_packaging,
            ],
            'qc_hints' => $this->qcHints($spec, $template),
            'operators' => $this->operators($jobCard, $queue),
            'cost_summary' => $this->costSummary($costSheet),
            'timeline_pipeline' => $this->timelinePipeline($jobCard, $spec),
            'legacy' => null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function legacyFallback(ProductionJobCard $jobCard): array
    {
        $item = $jobCard->salesOrder?->items?->first();

        return [
            'product' => $jobCard->inventoryItem?->item_name ?? $item?->item_name,
            'description' => $item?->description,
            'quantity' => $item?->quantity,
            'sales_order_notes' => $jobCard->salesOrder?->notes,
        ];
    }

    /**
     * @return list<array{label: string, state: string, at: ?string}>
     */
    protected function timelinePipeline(ProductionJobCard $jobCard, ?ProductionSpecification $spec): array
    {
        $status = $jobCard->status;
        $specApproved = $spec?->approval_status === ProductionSpecificationApprovalStatus::Approved;

        $stage = fn (string $label, bool $complete, bool $current = false, ?string $at = null) => [
            'label' => $label,
            'state' => $complete ? 'complete' : ($current ? 'current' : 'upcoming'),
            'at' => $at,
        ];

        return [
            $stage(__('Created'), true, false, $jobCard->created_at?->toDateTimeString()),
            $stage(
                __('Specification approved'),
                $specApproved || ! $spec,
                $spec && ! $specApproved,
                $spec?->updated_at?->toDateTimeString(),
            ),
            $stage(
                __('Queued'),
                in_array($status, [
                    ProductionJobCardStatus::Queued,
                    ProductionJobCardStatus::InProduction,
                    ProductionJobCardStatus::QualityCheck,
                    ProductionJobCardStatus::Completed,
                    ProductionJobCardStatus::ReadyForDispatch,
                ], true),
                $status === ProductionJobCardStatus::Queued,
            ),
            $stage(
                __('Assigned'),
                in_array($status, [
                    ProductionJobCardStatus::InProduction,
                    ProductionJobCardStatus::QualityCheck,
                    ProductionJobCardStatus::Completed,
                    ProductionJobCardStatus::ReadyForDispatch,
                ], true) || $jobCard->assigned_machine_asset_id,
                false,
            ),
            $stage(
                __('Printing'),
                in_array($status, [
                    ProductionJobCardStatus::InProduction,
                    ProductionJobCardStatus::QualityCheck,
                    ProductionJobCardStatus::Completed,
                    ProductionJobCardStatus::ReadyForDispatch,
                ], true),
                $status === ProductionJobCardStatus::InProduction,
                $jobCard->actual_start_date?->toDateTimeString(),
            ),
            $stage(
                __('Finishing'),
                in_array($status, [ProductionJobCardStatus::QualityCheck, ProductionJobCardStatus::Completed, ProductionJobCardStatus::ReadyForDispatch], true),
                $status === ProductionJobCardStatus::Returned,
            ),
            $stage(
                __('QC'),
                in_array($status, [ProductionJobCardStatus::Completed, ProductionJobCardStatus::ReadyForDispatch], true),
                $status === ProductionJobCardStatus::QualityCheck,
            ),
            $stage(
                __('Completed'),
                in_array($status, [ProductionJobCardStatus::Completed, ProductionJobCardStatus::ReadyForDispatch], true),
                $status === ProductionJobCardStatus::Completed,
                $jobCard->actual_end_date?->toDateTimeString(),
            ),
            $stage(
                __('Ready for dispatch'),
                $status === ProductionJobCardStatus::ReadyForDispatch,
                false,
            ),
        ];
    }

    /**
     * @param  array<string, mixed>  $queue
     * @return array<string, mixed>
     */
    protected function operators(ProductionJobCard $jobCard, array $queue): array
    {
        return [
            'operator' => $queue['current']?->assignedOperator?->name,
            'machine' => $jobCard->assignedMachine?->asset_name ?? $queue['work_center']?->name,
            'department' => $queue['work_center']?->name,
            'supervisor' => $jobCard->creator?->name,
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function costSummary($costSheet): ?array
    {
        if (! $costSheet) {
            return null;
        }

        return [
            'material' => (float) $costSheet->material_cost,
            'labor' => (float) $costSheet->labor_cost,
            'outsource' => (float) $costSheet->outsourced_cost,
            'total' => (float) $costSheet->total_cost,
            'read_only' => true,
        ];
    }

    /**
     * @return list<string>
     */
    protected function qcHints(?ProductionSpecification $spec, $template): array
    {
        $lines = $template?->recommendedQcChecklist?->lines
            ?->where('is_active', true)
            ?->pluck('label')
            ?->all() ?? [];

        if ($lines !== []) {
            return $lines;
        }

        return [
            __('Colours verified'),
            __('Registration'),
            __('Cut accuracy'),
            __('Binding quality'),
            __('Lamination quality'),
            __('Packaging'),
            __('Delivery ready'),
        ];
    }

    protected function editUrl(?ProductionSpecification $spec): ?string
    {
        if (! $spec || ! $spec->sales_order_id || ! $spec->sales_order_item_id) {
            return null;
        }

        if (! auth()->user()?->can('update', $spec)) {
            return null;
        }

        return route('admin.sales-orders.items.specification.edit', [
            'salesOrder' => $spec->sales_order_id,
            'salesOrderItem' => $spec->sales_order_item_id,
            'specification' => $spec->id,
        ]);
    }

    protected function extractGsmFromNotes(?string $notes): ?string
    {
        if (! $notes || ! preg_match('/GSM:\s*([^\n]+)/i', $notes, $matches)) {
            return null;
        }

        return trim($matches[1]);
    }

    protected function parseColourCount(?string $colourMode): ?int
    {
        if (! $colourMode || ! preg_match('/(\d+)/', $colourMode, $matches)) {
            return null;
        }

        return (int) $matches[1];
    }

    /**
     * @return list<array{label: string, value: mixed}>
     */
    protected function finishingFields(ProductionSpecification $spec): array
    {
        return $this->fields([
            __('Lamination') => $spec->lamination ? __('Yes') : __('No'),
            __('Binding') => $spec->binding_type,
            __('Spot UV') => $spec->spot_uv ? __('Yes') : __('No'),
            __('Foiling') => $spec->foiling ? __('Yes') : __('No'),
            __('Embossing') => $spec->embossing ? __('Yes') : __('No'),
            __('Debossing') => $spec->debossing ? __('Yes') : __('No'),
            __('Die cutting') => $spec->die_cutting ? __('Yes') : __('No'),
            __('Perforation') => $spec->perforation ? __('Yes') : __('No'),
            __('Creasing') => $spec->creasing ? __('Yes') : __('No'),
            __('Eyelets') => $spec->eyelets ? __('Yes') : __('No'),
            __('Finishing type') => $spec->finishing_type,
        ]);
    }

    /**
     * @param  array<string, mixed>  $pairs
     * @return list<array{label: string, value: mixed}>
     */
    protected function fields(array $pairs): array
    {
        return collect($pairs)
            ->filter(fn ($value) => $value !== null && $value !== '')
            ->map(fn ($value, $label) => ['label' => $label, 'value' => $value])
            ->values()
            ->all();
    }

    /**
     * Status cards for the manufacturing overview grid.
     *
     * @param  array<string, mixed>  $context  Full manufacturing tab payload (sections, artwork, quality, etc.)
     * @return list<array{id: string, label: string, status: string, tone: string, summary: ?string}>
     */
    public function dashboardCards(ProductionJobCard $jobCard, array $context): array
    {
        $sections = $context['sections'] ?? [];
        $materialPlan = $context['material_plan'] ?? [];
        $operators = $context['operators'] ?? [];
        $recommendations = $context['recommendations'] ?? [];
        $pipeline = collect($context['timeline_pipeline'] ?? []);
        $artwork = $context['artwork'] ?? [];
        $status = $jobCard->status;

        $pipelineState = fn (string $needle) => $pipeline->first(
            fn ($stage) => str_contains(strtolower($stage['label']), strtolower($needle)),
        )['state'] ?? 'upcoming';

        $stageStatus = function (string $needle) use ($pipelineState, $status): array {
            $state = $pipelineState($needle);

            return match ($state) {
                'complete' => ['status' => __('Complete'), 'tone' => 'success'],
                'current' => match ($needle) {
                    'printing' => ['status' => __('Running'), 'tone' => 'active'],
                    'qc' => ['status' => __('In progress'), 'tone' => 'active'],
                    default => ['status' => __('In progress'), 'tone' => 'active'],
                },
                default => match ($status) {
                    ProductionJobCardStatus::OnHold => ['status' => __('On hold'), 'tone' => 'warning'],
                    ProductionJobCardStatus::Cancelled => ['status' => __('Cancelled'), 'tone' => 'neutral'],
                    default => ['status' => __('Pending'), 'tone' => 'neutral'],
                },
            };
        };

        $generalFields = $sections['general'] ?? [];
        $generalReady = count($generalFields) >= 2;

        $materialReady = ! empty($materialPlan['paper']) || ! empty($sections['material']);
        $materialSummary = $materialPlan['paper']
            ?? collect($sections['material'] ?? [])->firstWhere('label', __('Paper'))['value'] ?? null;

        $machineName = $operators['machine']
            ?? $recommendations['machine']
            ?? null;

        $productionStatus = match (true) {
            in_array($status, [ProductionJobCardStatus::Completed, ProductionJobCardStatus::ReadyForDispatch], true) => ['status' => __('Complete'), 'tone' => 'success'],
            $status === ProductionJobCardStatus::InProduction => ['status' => __('Running'), 'tone' => 'active'],
            $status === ProductionJobCardStatus::Queued => ['status' => __('Queued'), 'tone' => 'neutral'],
            $status === ProductionJobCardStatus::OnHold => ['status' => __('On hold'), 'tone' => 'warning'],
            default => ['status' => __('Waiting'), 'tone' => 'neutral'],
        };

        $dispatchStatus = match (true) {
            $status === ProductionJobCardStatus::ReadyForDispatch => ['status' => __('Ready'), 'tone' => 'success'],
            in_array($status, [ProductionJobCardStatus::Completed], true) => ['status' => __('Pending'), 'tone' => 'neutral'],
            default => ['status' => __('Pending'), 'tone' => 'neutral'],
        };

        $artworkStatus = $this->artworkCardStatus($artwork, $sections['artwork'] ?? []);

        $qcStatus = match (true) {
            in_array($status, [ProductionJobCardStatus::Completed, ProductionJobCardStatus::ReadyForDispatch], true) => ['status' => __('Passed'), 'tone' => 'success'],
            $status === ProductionJobCardStatus::QualityCheck => ['status' => __('In progress'), 'tone' => 'active'],
            $status === ProductionJobCardStatus::Rework => ['status' => __('Rework'), 'tone' => 'warning'],
            default => ['status' => __('Not started'), 'tone' => 'neutral'],
        };

        return [
            [
                'id' => 'general',
                'label' => __('General'),
                'status' => $generalReady ? __('Ready') : __('Incomplete'),
                'tone' => $generalReady ? 'success' : 'warning',
                'summary' => collect($generalFields)->take(2)->pluck('value')->filter()->implode(' · ') ?: null,
            ],
            [
                'id' => 'materials',
                'label' => __('Materials'),
                'status' => $materialReady ? __('Ready') : __('Missing'),
                'tone' => $materialReady ? 'success' : 'warning',
                'summary' => $materialSummary,
            ],
            [
                'id' => 'production',
                'label' => __('Production'),
                'status' => $productionStatus['status'],
                'tone' => $productionStatus['tone'],
                'summary' => collect($sections['production'] ?? [])->firstWhere('label', __('Estimated sheets'))['value'] ?? null,
            ],
            [
                'id' => 'printing',
                'label' => __('Printing'),
                'status' => $stageStatus('printing')['status'],
                'tone' => $stageStatus('printing')['tone'],
                'summary' => collect($sections['printing'] ?? [])->firstWhere('label', __('Colour mode'))['value'] ?? null,
            ],
            [
                'id' => 'finishing',
                'label' => __('Finishing'),
                'status' => $stageStatus('finishing')['status'],
                'tone' => $stageStatus('finishing')['tone'],
                'summary' => collect($sections['finishing'] ?? [])->first()['value'] ?? null,
            ],
            [
                'id' => 'qc',
                'label' => __('QC'),
                'status' => $qcStatus['status'],
                'tone' => $qcStatus['tone'],
                'summary' => null,
            ],
            [
                'id' => 'dispatch',
                'label' => __('Dispatch'),
                'status' => $dispatchStatus['status'],
                'tone' => $dispatchStatus['tone'],
                'summary' => null,
            ],
            [
                'id' => 'artwork',
                'label' => __('Artwork'),
                'status' => $artworkStatus['status'],
                'tone' => $artworkStatus['tone'],
                'summary' => $artworkStatus['summary'],
            ],
            [
                'id' => 'machine',
                'label' => __('Machine'),
                'status' => $machineName ? __('Assigned') : __('Unassigned'),
                'tone' => $machineName ? 'success' : 'warning',
                'summary' => $machineName,
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $artwork
     * @param  list<array{label: string, value: mixed}>  $artworkSection
     * @return array{status: string, tone: string, summary: ?string}
     */
    protected function artworkCardStatus(array $artwork, array $artworkSection): array
    {
        if (! empty($artwork['empty'])) {
            $ref = collect($artworkSection)->firstWhere('label', __('Artwork reference'))['value'] ?? null;

            return [
                'status' => $ref ? __('Linked') : __('Not linked'),
                'tone' => $ref ? 'neutral' : 'warning',
                'summary' => $ref,
            ];
        }

        $approval = strtolower((string) ($artwork['approval_status'] ?? ''));

        if (str_contains($approval, 'approved') || str_contains($approval, 'pass')) {
            return [
                'status' => __('Approved'),
                'tone' => 'success',
                'summary' => $artwork['request']?->request_number ?? null,
            ];
        }

        if (str_contains($approval, 'reject')) {
            return [
                'status' => __('Rejected'),
                'tone' => 'danger',
                'summary' => $artwork['request']?->request_number ?? null,
            ];
        }

        if ($artwork['request'] ?? null) {
            return [
                'status' => __('Pending'),
                'tone' => 'warning',
                'summary' => $artwork['request']->request_number,
            ];
        }

        return [
            'status' => __('Not linked'),
            'tone' => 'warning',
            'summary' => null,
        ];
    }
}
