<?php

namespace App\Services\Production;

use App\Enums\ArtworkApprovalDecision;
use App\Enums\ArtworkRequestStatus;
use App\Enums\ProductionJobCardStatus;
use App\Enums\QualityCheckResult;
use App\Enums\SalesOrderStatus;
use App\Models\Artwork\ArtworkRequest;
use App\Models\Inventory\InventoryItem;
use App\Models\Inventory\ProductionMaterialConsumption;
use App\Models\Inventory\Warehouse;
use App\Models\Production\ProductionJobCard;
use App\Models\Production\ProductionOperation;
use App\Models\Production\ProductionQueue;
use App\Models\Production\ProductionStage;
use App\Models\Production\QualityCheck;
use App\Models\Assets\MachineProfile;
use App\Models\Production\WorkCenter;
use App\Services\Assets\MachineJobAssignmentService;
use App\Services\Production\ProductionCompletionService;
use Illuminate\Support\Facades\Schema;

class Job360WorkspaceService
{
    public function __construct(
        protected JobProductionControlService $controls,
        protected MachineJobAssignmentService $machineAssignments,
    ) {}

    public const TAB_OVERVIEW = 'overview';

    public const TAB_TRACEABILITY = 'traceability';

    public const TAB_ARTWORK = 'artwork';

    public const TAB_OPERATIONS = 'operations';

    public const TAB_MATERIALS = 'materials';

    public const TAB_QUALITY = 'quality';

    public const TAB_OUTPUTS = 'outputs';

    public const TAB_DISPATCH = 'dispatch';

    public const TAB_TIMELINE = 'timeline';

    /** @var list<string> */
    public const TABS = [
        self::TAB_OVERVIEW,
        self::TAB_TRACEABILITY,
        self::TAB_ARTWORK,
        self::TAB_OPERATIONS,
        self::TAB_MATERIALS,
        self::TAB_OUTPUTS,
        self::TAB_QUALITY,
        self::TAB_DISPATCH,
        self::TAB_TIMELINE,
    ];

    /**
     * @param  array<string, mixed>  $timelineQuery
     * @return array<string, mixed>
     */
    public function build(ProductionJobCard $jobCard, ?string $tab = null, array $timelineQuery = []): array
    {
        $activeTab = $this->resolveTab($tab);
        $this->loadBaseRelations($jobCard);

        return [
            'jobCard' => $jobCard,
            'header' => $this->header($jobCard),
            'kpis' => $this->controls->productionKpis($jobCard),
            'control_alerts' => $this->controls->controlAlerts($jobCard),
            'quick_actions' => $this->quickActions($jobCard),
            'active_tab' => $activeTab,
            'tabs' => $this->tabNavigation($jobCard, $activeTab),
            'tab_data' => $this->tabData($jobCard, $activeTab, $timelineQuery),
        ];
    }

    public function resolveTab(?string $tab): string
    {
        $tab = $tab ?? self::TAB_OVERVIEW;

        return in_array($tab, self::TABS, true) ? $tab : self::TAB_OVERVIEW;
    }

    protected function loadBaseRelations(ProductionJobCard $jobCard): void
    {
        $jobCard->loadMissing([
            'customer:id,company_name,customer_code',
            'branch:id,name',
            'creator:id,name',
            'salesOrder:id,order_number,status,required_date',
            'quotation:id,quotation_number,status',
            'artworkRequest:id,request_number,status,current_version,title',
            'queues' => fn ($q) => $q->with(['workCenter:id,name', 'assignedOperator:id,name'])
                ->orderBy('queue_position'),
        ])->loadCount([
            'operations',
            'operations as completed_operations_count' => fn ($q) => $q->whereNotNull('ended_at'),
            'operations as assigned_operations_count' => fn ($q) => $q->whereNotNull('assigned_employee_id'),
            'qualityChecks',
            'qualityChecks as passed_qc_count' => fn ($q) => $q->where('result', QualityCheckResult::Passed),
            'qualityChecks as failed_qc_count' => fn ($q) => $q->whereIn('result', [
                QualityCheckResult::Failed,
                QualityCheckResult::ReworkRequired,
            ]),
            'materialConsumptions',
            'productionOutputs',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function header(ProductionJobCard $jobCard): array
    {
        $primaryQueue = $jobCard->queues->first();

        return [
            'job_number' => $jobCard->job_card_number,
            'status' => $jobCard->status,
            'priority' => $jobCard->priority,
            'customer_name' => $jobCard->customer?->company_name,
            'customer_id' => $jobCard->customer_id,
            'sales_order_number' => $jobCard->salesOrder?->order_number,
            'sales_order_id' => $jobCard->sales_order_id,
            'quotation_number' => $jobCard->quotation?->quotation_number,
            'quotation_id' => $jobCard->quotation_id,
            'artwork_number' => $jobCard->artworkRequest?->request_number,
            'artwork_request_id' => $jobCard->artwork_request_id,
            'production_type' => $jobCard->production_type,
            'due_date' => $jobCard->planned_end_date,
            'work_center' => $primaryQueue?->workCenter?->name,
            'progress_percent' => $this->progressPercent($jobCard),
            'branch' => $jobCard->branch?->name,
            'created_by' => $jobCard->creator?->name,
            'created_at' => $jobCard->created_at,
            'is_delayed' => $jobCard->isDelayed(),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function quickActions(ProductionJobCard $jobCard): array
    {
        $user = auth()->user();
        $actions = [];

        if ($user?->can('start', $jobCard) && $jobCard->status->canTransitionTo(ProductionJobCardStatus::InProduction)) {
            $actions[] = [
                'label' => __('Start Job'),
                'type' => 'post',
                'url' => route('admin.production.job-cards.start', $jobCard),
            ];
        }

        if ($user?->can('start', $jobCard)) {
            $actions[] = [
                'label' => __('Log Operation'),
                'url' => route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => self::TAB_OPERATIONS]).'#log-operation',
            ];
        }

        if ($user?->can('inventory.issue')) {
            $actions[] = [
                'label' => __('Consume Material'),
                'url' => route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => self::TAB_MATERIALS]).'#consume-material',
            ];
        }

        if ($user?->can('create', [QualityCheck::class, $jobCard])) {
            $actions[] = [
                'label' => __('Add QC Check'),
                'url' => route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => self::TAB_QUALITY]).'#add-qc',
            ];
        }

        if (
            $user?->can('complete', $jobCard)
            && $jobCard->status->canTransitionTo(ProductionJobCardStatus::ReadyForDispatch)
            && $this->controls->dispatchEligibility($jobCard)['eligible']
        ) {
            $actions[] = [
                'label' => __('Mark Ready For Dispatch'),
                'type' => 'post',
                'url' => route('admin.production.job-cards.ready-for-dispatch', $jobCard),
            ];
        }

        if ($jobCard->customer_id && $user?->can('view', $jobCard->customer)) {
            $actions[] = [
                'label' => __('View Customer 360'),
                'url' => route('admin.crm.customers.show', $jobCard->customer_id),
            ];
        }

        if ($jobCard->sales_order_id && $user?->can('view', $jobCard->salesOrder)) {
            $actions[] = [
                'label' => __('View Sales Order'),
                'url' => route('admin.sales-orders.show', $jobCard->sales_order_id),
            ];
        }

        if ($jobCard->artwork_request_id && $user?->can('view', $jobCard->artworkRequest)) {
            $actions[] = [
                'label' => __('View Artwork'),
                'url' => route('admin.artwork.show', $jobCard->artwork_request_id),
            ];
        }

        return $actions;
    }

    /**
     * @return list<array<string, string|bool>>
     */
    protected function tabNavigation(ProductionJobCard $jobCard, string $activeTab): array
    {
        return collect(self::TABS)->map(fn (string $tab) => [
            'id' => $tab,
            'label' => $this->tabLabel($tab),
            'url' => route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => $tab]),
            'active' => $tab === $activeTab,
        ])->all();
    }

    protected function tabLabel(string $tab): string
    {
        return match ($tab) {
            self::TAB_OVERVIEW => __('Overview'),
            self::TAB_TRACEABILITY => __('Traceability'),
            self::TAB_ARTWORK => __('Artwork'),
            self::TAB_OPERATIONS => __('Operations'),
            self::TAB_MATERIALS => __('Materials'),
            self::TAB_OUTPUTS => __('Finished goods'),
            self::TAB_QUALITY => __('Quality Control'),
            self::TAB_DISPATCH => __('Dispatch'),
            self::TAB_TIMELINE => __('Timeline'),
            default => ucfirst($tab),
        };
    }

    /**
     * @param  array<string, mixed>  $timelineQuery
     * @return array<string, mixed>
     */
    protected function tabData(ProductionJobCard $jobCard, string $tab, array $timelineQuery = []): array
    {
        return match ($tab) {
            self::TAB_OVERVIEW => $this->overviewTab($jobCard),
            self::TAB_TRACEABILITY => ['chain' => $this->traceabilityChain($jobCard)],
            self::TAB_ARTWORK => $this->artworkTab($jobCard),
            self::TAB_OPERATIONS => $this->operationsTab($jobCard),
            self::TAB_MATERIALS => $this->materialsTab($jobCard),
            self::TAB_OUTPUTS => $this->outputsTab($jobCard),
            self::TAB_QUALITY => $this->qualityTab($jobCard),
            self::TAB_DISPATCH => $this->dispatchTab($jobCard),
            self::TAB_TIMELINE => array_merge(
                app(JobTimelineService::class)->paginate(
                    $jobCard,
                    $timelineQuery['timeline_filter'] ?? null,
                    $timelineQuery['timeline_search'] ?? null,
                    isset($timelineQuery['timeline_page']) ? (int) $timelineQuery['timeline_page'] : null,
                ),
                ['ready' => true],
            ),
            default => [],
        };
    }

    /**
     * @return array<string, mixed>
     */
    protected function overviewTab(ProductionJobCard $jobCard): array
    {
        $completion = app(ProductionCompletionService::class)->eligibility($jobCard);

        return [
            'completion' => $completion,
            'finished_items' => InventoryItem::query()
                ->where('company_id', $jobCard->company_id)
                ->where('branch_id', $jobCard->branch_id)
                ->where('stock_role', \App\Enums\InventoryStockRole::FinishedGood)
                ->where('is_active', true)
                ->orderBy('item_name')
                ->get(['id', 'sku', 'item_name']),
            'summary' => [
                'production_type' => $jobCard->production_type->value,
                'priority' => $jobCard->priority->value,
                'planned' => [
                    'start' => $jobCard->planned_start_date?->format('Y-m-d'),
                    'end' => $jobCard->planned_end_date?->format('Y-m-d'),
                ],
                'actual' => [
                    'start' => $jobCard->actual_start_date?->format('Y-m-d H:i'),
                    'end' => $jobCard->actual_end_date?->format('Y-m-d H:i'),
                ],
            ],
            'customer' => $jobCard->customer ? [
                'name' => $jobCard->customer->company_name,
                'code' => $jobCard->customer->customer_code,
            ] : null,
            'sales_order' => $jobCard->salesOrder ? [
                'number' => $jobCard->salesOrder->order_number,
                'status' => $jobCard->salesOrder->status->value,
            ] : null,
            'quotation' => $jobCard->quotation ? [
                'number' => $jobCard->quotation->quotation_number,
                'status' => $jobCard->quotation->status->value,
            ] : null,
            'artwork' => $jobCard->artworkRequest ? [
                'number' => $jobCard->artworkRequest->request_number,
                'status' => $jobCard->artworkRequest->status->value,
            ] : null,
            'status_explanation' => $this->statusExplanation($jobCard),
            'next_action' => $this->nextExpectedAction($jobCard),
            'dispatch_eligibility' => $this->controls->dispatchEligibility($jobCard),
            'control_alerts' => $this->controls->controlAlerts($jobCard),
            'machine' => $this->machineAssignments->jobMachineContext($jobCard),
            'machine_options' => $this->assignableMachines(),
        ];
    }

    /**
     * @return \Illuminate\Support\Collection<int, MachineProfile>
     */
    protected function assignableMachines(): \Illuminate\Support\Collection
    {
        if (! auth()->user()?->can('machines.assign')) {
            return collect();
        }

        return MachineProfile::query()
            ->forTenant()
            ->productionMachines()
            ->with('asset:id,asset_name,asset_number')
            ->orderBy('machine_code')
            ->get();
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function traceabilityChain(ProductionJobCard $jobCard): array
    {
        $steps = [];

        $steps[] = $this->chainStep(
            __('Quotation'),
            $jobCard->quotation?->quotation_number,
            $jobCard->quotation_id ? route('admin.quotations.show', $jobCard->quotation_id) : null,
            (bool) $jobCard->quotation_id,
        );

        $steps[] = $this->chainStep(
            __('Sales Order'),
            $jobCard->salesOrder?->order_number,
            $jobCard->sales_order_id ? route('admin.sales-orders.show', $jobCard->sales_order_id) : null,
            (bool) $jobCard->sales_order_id,
        );

        $steps[] = $this->chainStep(
            __('Artwork Request'),
            $jobCard->artworkRequest?->request_number,
            $jobCard->artwork_request_id ? route('admin.artwork.show', $jobCard->artwork_request_id) : null,
            (bool) $jobCard->artwork_request_id,
        );

        $latestApproval = $jobCard->artwork_request_id
            ? \App\Models\Artwork\ArtworkApproval::query()
                ->where('artwork_request_id', $jobCard->artwork_request_id)
                ->latest('created_at')
                ->first()
            : null;

        $steps[] = [
            'label' => __('Artwork Approval'),
            'reference' => $latestApproval?->decision->value ?? __('Pending'),
            'url' => $jobCard->artwork_request_id ? route('admin.artwork.show', $jobCard->artwork_request_id) : null,
            'state' => $latestApproval ? 'complete' : 'pending',
            'placeholder' => ! $latestApproval,
        ];

        $steps[] = $this->chainStep(
            __('Job Card'),
            $jobCard->job_card_number,
            route('admin.production.job-cards.show', $jobCard),
            true,
        );

        $steps[] = [
            'label' => __('Quality Control'),
            'reference' => $jobCard->quality_checks_count > 0
                ? (string) $jobCard->quality_checks_count.' '.__('checks')
                : __('None recorded'),
            'url' => route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => self::TAB_QUALITY]),
            'state' => $jobCard->quality_checks_count > 0 ? 'complete' : 'pending',
            'placeholder' => false,
        ];

        $steps[] = [
            'label' => __('Dispatch'),
            'reference' => $this->dispatchReadinessLabel($jobCard),
            'url' => route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => self::TAB_DISPATCH]),
            'state' => $jobCard->status === ProductionJobCardStatus::ReadyForDispatch ? 'complete' : 'pending',
            'placeholder' => false,
        ];

        if (Schema::hasTable('delivery_notes')) {
            $delivered = $jobCard->deliveryNotes()
                ->where('status', \App\Enums\Dispatch\DeliveryNoteStatus::Delivered)
                ->exists();
            $active = $jobCard->deliveryNotes()
                ->whereNot('status', \App\Enums\Dispatch\DeliveryNoteStatus::Cancelled)
                ->latest('id')
                ->first();

            $steps[] = [
                'label' => __('Delivery'),
                'reference' => $active?->delivery_note_number,
                'url' => $active
                    ? route('admin.dispatch.delivery-notes.show', $active)
                    : route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => self::TAB_DISPATCH]),
                'state' => $delivered ? 'complete' : ($active ? 'pending' : 'pending'),
                'placeholder' => false,
            ];
        } else {
            $steps[] = [
                'label' => __('Delivery'),
                'reference' => null,
                'url' => null,
                'state' => 'placeholder',
                'placeholder' => true,
                'placeholder_message' => __('Delivery tracking available after Dispatch module activation'),
            ];
        }

        return $steps;
    }

    /**
     * @return array<string, mixed>
     */
    protected function chainStep(string $label, ?string $reference, ?string $url, bool $complete): array
    {
        return [
            'label' => $label,
            'reference' => $reference ?? __('Not linked'),
            'url' => $url,
            'state' => $complete ? 'complete' : 'missing',
            'placeholder' => false,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function artworkTab(ProductionJobCard $jobCard): array
    {
        if (! $jobCard->artwork_request_id) {
            return ['empty' => true];
        }

        $request = ArtworkRequest::query()
            ->with([
                'approvals.approver:id,name',
                'files',
                'assignedDesigner:id,name',
            ])
            ->find($jobCard->artwork_request_id);

        $latestApproval = $request?->approvals->first();
        $rejection = $request?->approvals->first(
            fn ($a) => $a->decision === ArtworkApprovalDecision::Rejected,
        );

        return [
            'request' => $request,
            'approval_status' => $latestApproval?->decision->value ?? $request?->status->value,
            'revision_count' => max(0, ($request?->current_version ?? 0) - 1),
            'latest_approval' => $latestApproval,
            'rejection_reason' => $rejection?->comments,
            'portal_placeholder' => __('Customer portal approval workflow not yet activated'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function operationsTab(ProductionJobCard $jobCard): array
    {
        $operations = ProductionOperation::query()
            ->where('production_job_card_id', $jobCard->id)
            ->with(['workCenter:id,name', 'stage:id,name', 'assignedEmployee:id,first_name,last_name'])
            ->orderByDesc('started_at')
            ->paginate(25, pageName: 'operations_page');

        return [
            'operations' => $operations,
            'work_centers' => WorkCenter::query()->forTenant()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'stages' => ProductionStage::query()->forTenant()->where('is_active', true)->orderBy('sort_order')->get(['id', 'name']),
            'operators' => $this->controls->scopedOperators($jobCard),
            'operator_assignment_available' => $this->controls->operatorAssignmentAvailable(),
            'can_log' => auth()->user()?->can('start', $jobCard) ?? false,
            'can_assign' => auth()->user()?->can('start', $jobCard) && $this->controls->operatorAssignmentAvailable(),
            'can_complete_op' => auth()->user()?->can('complete', $jobCard) ?? false,
            'can_queue' => auth()->user()?->can('create', [ProductionQueue::class, $jobCard]) ?? false,
            'queues' => $jobCard->queues,
            'controls' => $this->controls,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function outputsTab(ProductionJobCard $jobCard): array
    {
        $outputs = $jobCard->productionOutputs()
            ->with(['finishedItem:id,sku,item_name', 'finishedWarehouse:id,code,name', 'completedByUser:id,name', 'postedJournal:id,reference'])
            ->paginate(25, pageName: 'outputs_page');

        return [
            'outputs' => $outputs,
            'completion' => app(ProductionCompletionService::class)->eligibility($jobCard),
            'finished_items' => InventoryItem::query()
                ->where('company_id', $jobCard->company_id)
                ->where('branch_id', $jobCard->branch_id)
                ->where('stock_role', \App\Enums\InventoryStockRole::FinishedGood)
                ->where('is_active', true)
                ->orderBy('item_name')
                ->get(['id', 'sku', 'item_name']),
            'virtual_locations_url' => route('admin.inventory.virtual-locations.index'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function materialsTab(ProductionJobCard $jobCard): array
    {
        $consumptions = ProductionMaterialConsumption::query()
            ->where('production_job_card_id', $jobCard->id)
            ->with(['inventoryItem:id,sku,item_name,unit_of_measure_id', 'inventoryItem.unitOfMeasure:id,code', 'warehouse:id,name', 'movement:id,reference', 'consumer:id,name'])
            ->latest('consumed_at')
            ->paginate(25, pageName: 'materials_page');

        return [
            'consumptions' => $consumptions,
            'bom_warning' => __('Required materials/BOM not yet activated'),
            'material_requirements_placeholder' => __('Material requirements not activated'),
            'wastage' => $this->controls->wastageSummary($jobCard),
            'can_consume' => auth()->user()?->can('inventory.issue') ?? false,
            'inventory_items' => InventoryItem::query()->forTenant()->where('is_active', true)->orderBy('sku')->get(['id', 'sku', 'item_name']),
            'warehouses' => Warehouse::query()->forTenant()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function qualityTab(ProductionJobCard $jobCard): array
    {
        $checks = QualityCheck::query()
            ->where('production_job_card_id', $jobCard->id)
            ->with('checker:id,name')
            ->latest('checked_at')
            ->paginate(25, pageName: 'quality_page');

        return [
            'checks' => $checks,
            'can_record' => auth()->user()?->can('create', [QualityCheck::class, $jobCard]) ?? false,
            'qc_blocking' => $this->controls->hasUnresolvedQcFailure($jobCard),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function dispatchTab(ProductionJobCard $jobCard): array
    {
        $eligibility = $this->controls->dispatchEligibility($jobCard);
        $creationEligibility = $this->controls->deliveryNoteCreationEligibility($jobCard);

        $deliveryNotes = Schema::hasTable('delivery_notes')
            ? $jobCard->deliveryNotes()
                ->with(['dispatcher:id,name', 'deliverer:id,name'])
                ->orderByDesc('id')
                ->get()
            : collect();

        $activeNote = $deliveryNotes->first(
            fn ($note) => $note->status !== \App\Enums\Dispatch\DeliveryNoteStatus::Cancelled
        );

        return [
            'ready_for_dispatch' => $jobCard->status === ProductionJobCardStatus::ReadyForDispatch,
            'readiness_label' => $this->dispatchReadinessLabel($jobCard),
            'readiness_score' => $this->controls->dispatchReadinessScore($jobCard),
            'checklist' => $this->controls->readinessChecklist($jobCard),
            'dispatch_eligibility' => $eligibility,
            'delivery_note_eligibility' => $creationEligibility,
            'sales_order_status' => $jobCard->salesOrder?->status->value,
            'sales_order_delivered' => in_array($jobCard->salesOrder?->status, [
                SalesOrderStatus::Delivered,
                SalesOrderStatus::Closed,
            ], true),
            'active_delivery_note' => $activeNote,
            'delivery_history' => $deliveryNotes,
            'invoice_status' => app(\App\Services\Accounting\DeliveryInvoiceService::class)
                ->billingStatusForJob($jobCard->id),
        ];
    }

    public function progressPercent(ProductionJobCard $jobCard): int
    {
        $total = (int) ($jobCard->operations_count ?? 0);

        if ($total === 0) {
            return match ($jobCard->status) {
                ProductionJobCardStatus::Completed,
                ProductionJobCardStatus::ReadyForDispatch => 100,
                ProductionJobCardStatus::Draft => 0,
                ProductionJobCardStatus::Queued => 10,
                ProductionJobCardStatus::InProduction => 40,
                ProductionJobCardStatus::QualityCheck => 85,
                default => 25,
            };
        }

        $completed = (int) ($jobCard->completed_operations_count ?? 0);

        return (int) min(100, round(($completed / $total) * 100));
    }

    protected function currentStageLabel(ProductionJobCard $jobCard): string
    {
        $openOp = ProductionOperation::query()
            ->where('production_job_card_id', $jobCard->id)
            ->whereNull('ended_at')
            ->with('stage:id,name', 'workCenter:id,name')
            ->latest('started_at')
            ->first();

        if ($openOp) {
            return $openOp->stage?->name ?? $openOp->workCenter?->name ?? __('In progress');
        }

        return str_replace('_', ' ', $jobCard->status->value);
    }

    protected function dispatchReadinessLabel(ProductionJobCard $jobCard): string
    {
        return match ($jobCard->status) {
            ProductionJobCardStatus::ReadyForDispatch => __('Ready'),
            ProductionJobCardStatus::Completed => __('Awaiting dispatch mark'),
            ProductionJobCardStatus::Cancelled => __('Cancelled'),
            default => __('Not ready'),
        };
    }

    protected function statusExplanation(ProductionJobCard $jobCard): string
    {
        return match ($jobCard->status) {
            ProductionJobCardStatus::Draft => __('Job card is drafted and awaiting scheduling.'),
            ProductionJobCardStatus::Queued => __('Job is queued for production.'),
            ProductionJobCardStatus::InProduction => __('Job is actively in production.'),
            ProductionJobCardStatus::QualityCheck => __('Job is undergoing quality inspection.'),
            ProductionJobCardStatus::Completed => __('Production is complete; dispatch may be pending.'),
            ProductionJobCardStatus::ReadyForDispatch => __('Job is ready for dispatch.'),
            ProductionJobCardStatus::OnHold => __('Job is on hold.'),
            ProductionJobCardStatus::Rework => __('Job requires rework.'),
            ProductionJobCardStatus::Cancelled => __('Job has been cancelled.'),
        };
    }

    protected function nextExpectedAction(ProductionJobCard $jobCard): string
    {
        $user = auth()->user();

        if ($jobCard->status->canTransitionTo(ProductionJobCardStatus::Queued) && $user?->can('schedule', $jobCard)) {
            return __('Queue the job for production.');
        }

        if ($jobCard->status->canTransitionTo(ProductionJobCardStatus::InProduction) && $user?->can('start', $jobCard)) {
            return __('Start production.');
        }

        if ($jobCard->status === ProductionJobCardStatus::InProduction && $user?->can('start', $jobCard)) {
            return __('Log operations and consume materials.');
        }

        if ($jobCard->status->canTransitionTo(ProductionJobCardStatus::QualityCheck) && $user?->can('complete', $jobCard)) {
            return __('Send job to quality check.');
        }

        if ($jobCard->status === ProductionJobCardStatus::QualityCheck && $user?->can('create', [QualityCheck::class, $jobCard])) {
            return __('Record quality check results.');
        }

        if ($jobCard->status->canTransitionTo(ProductionJobCardStatus::ReadyForDispatch) && $user?->can('complete', $jobCard)) {
            if (! $this->controls->dispatchEligibility($jobCard)['eligible']) {
                return __('Resolve dispatch blockers before marking ready for dispatch.');
            }

            return __('Mark job ready for dispatch.');
        }

        if ($jobCard->status === ProductionJobCardStatus::ReadyForDispatch) {
            return __('Awaiting dispatch and delivery processing.');
        }

        return __('Monitor job progress.');
    }
}
