<?php

namespace App\Services\Production;

use App\Enums\ArtworkApprovalDecision;
use App\Enums\ArtworkRequestStatus;
use App\Enums\InventoryStockRole;
use App\Enums\JobCardRouteStepStatus;
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
use App\Services\Dispatch\JobDispatchPresentationService;
use App\Services\Production\ProductionCompletionService;
use App\Support\Production\JobCardManufacturingPresenter;
use App\Support\Production\JobCardOutsourceService;
use App\Support\Production\JobCardPrintUrl;
use App\Support\Production\ProductBomService;
use App\Support\Production\ProductionRouteService;
use App\Support\Production\ProductionSessionService;
use App\Support\Production\ProductionSpecificationService;
use App\Support\Production\SerialNumberGovernanceService;
use App\Support\Communications\Email\EmailVisibilityService;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

class Job360WorkspaceService
{
    /** @var array<string, \Illuminate\Support\Collection<int, InventoryItem>> */
    protected array $finishedItemsCache = [];

    public function __construct(
        protected JobProductionControlService $controls,
        protected MachineJobAssignmentService $machineAssignments,
    ) {}

    public const TAB_OVERVIEW = 'overview';

    public const TAB_TRACEABILITY = 'traceability';

    public const TAB_ARTWORK = 'artwork';

    public const TAB_OPERATIONS = 'operations';

    public const TAB_ROUTE = 'route';

    public const TAB_SERIALS = 'serials';

    public const TAB_SESSIONS = 'sessions';

    public const TAB_MATERIALS = 'materials';

    public const TAB_MATERIAL_ISSUES = 'material-issues';

    public const TAB_MATERIAL_CONSUMPTION = 'material-consumption';

    public const TAB_QUALITY = 'quality';

    public const TAB_FULFILMENT = 'fulfilment';

    public const TAB_OUTPUTS = 'outputs';

    public const TAB_DISPATCH = 'dispatch';

    public const TAB_MANUFACTURING = 'manufacturing';

    public const TAB_COMMERCIAL = 'commercial';

    public const TAB_COMMUNICATIONS = 'communications';

    public const TAB_SPECIFICATION = 'specification';

    public const TAB_TIMELINE = 'timeline';

    /**
     * Visible Job 360 navigation — production work path only.
     * Advanced / rare screens stay deep-linkable via TABS but are not shown in the tab bar.
     *
     * @var list<string>
     */
    public const PRIMARY_TABS = [
        self::TAB_OVERVIEW,
        self::TAB_MANUFACTURING,
        self::TAB_MATERIALS,
        self::TAB_ARTWORK,
        self::TAB_OPERATIONS,
        self::TAB_QUALITY,
        self::TAB_OUTPUTS,
        self::TAB_DISPATCH,
    ];

    /**
     * Secondary overflow menu. Kept empty so Job 360 stays a work tool, not a sitemap.
     *
     * @var list<string>
     */
    public const MORE_TABS = [];

    /**
     * All resolvable tabs (including hidden ones used by Overview history tiles, FABs, and tests).
     *
     * @var list<string>
     */
    public const TABS = [
        self::TAB_OVERVIEW,
        self::TAB_MANUFACTURING,
        self::TAB_MATERIALS,
        self::TAB_ARTWORK,
        self::TAB_OPERATIONS,
        self::TAB_QUALITY,
        self::TAB_OUTPUTS,
        self::TAB_DISPATCH,
        self::TAB_COMMERCIAL,
        self::TAB_TIMELINE,
        self::TAB_COMMUNICATIONS,
        self::TAB_SPECIFICATION,
        self::TAB_TRACEABILITY,
        self::TAB_ROUTE,
        self::TAB_SERIALS,
        self::TAB_SESSIONS,
        self::TAB_MATERIAL_ISSUES,
        self::TAB_MATERIAL_CONSUMPTION,
        self::TAB_FULFILMENT,
    ];

    /**
     * @param  array<string, mixed>  $timelineQuery
     * @return array<string, mixed>
     */
    public function build(ProductionJobCard $jobCard, ?string $tab = null, array $timelineQuery = []): array
    {
        $activeTab = $this->resolveTab($tab);
        $this->loadBaseRelations($jobCard);

        $completion = app(ProductionCompletionService::class)->eligibility($jobCard);
        $floorActions = app(ProductionFloorActionService::class);
        $needsFinishedItems = ($completion['eligible'] ?? false)
            || in_array($activeTab, [self::TAB_OUTPUTS, self::TAB_OVERVIEW], true);

        $header = $this->header($jobCard);
        $header['quantity'] = $completion['suggested_quantity_completed'] ?? null;
        $hasPostedOutput = app(ProductionCompletionService::class)->hasPostedFinishedGoods($jobCard);
        $dispatchSummary = app(JobDispatchPresentationService::class)->build($jobCard);
        $workflowPresentation = app(JobWorkflowPresentationService::class)->present($jobCard);
        $executionState = $this->mergeWorkflowIntoExecutionState(
            app(JobExecutionStateService::class)->state($jobCard),
            $workflowPresentation,
            $dispatchSummary,
        );
        $header['progress_percent'] = $workflowPresentation['workflow_progress_percent'];
        $header['current_stage_label'] = $workflowPresentation['current_stage_label'];
        $controlAlerts = $this->filterControlAlertsForDispatch($this->controls->controlAlerts($jobCard), $dispatchSummary);
        $materialReadiness = app(\App\Support\Production\MaterialReadinessService::class)->assess($jobCard);

        return [
            'jobCard' => $jobCard,
            'header' => $header,
            'execution_state' => $executionState,
            'workflow_presentation' => $workflowPresentation,
            'dispatch_summary' => $dispatchSummary,
            'has_posted_output' => $hasPostedOutput,
            'material_readiness' => $materialReadiness,
            'readiness_checklist' => $this->controls->readinessChecklist($jobCard),
            'kpis' => $this->controls->productionKpis($jobCard),
            'control_alerts' => $controlAlerts,
            'primary_action' => $floorActions->adaptForJobWorkspace($floorActions->primaryAction($jobCard), $jobCard),
            'secondary_actions' => $floorActions->adaptSecondaryForJobWorkspace($floorActions->secondaryActions($jobCard), $jobCard),
            'link_actions' => $this->linkActions($jobCard),
            'quick_actions' => $this->linkActions($jobCard),
            'completion' => $completion,
            'finished_items' => $needsFinishedItems ? $this->finishedItemsCatalog($jobCard) : collect(),
            'dispatch_eligibility' => $this->controls->dispatchEligibility($jobCard),
            'assignable_machines' => $this->assignableMachines(),
            'active_tab' => $activeTab,
            'tab_groups' => $this->tabGroups($jobCard, $activeTab),
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
            'salesOrder:id,public_id,company_id,branch_id,order_number,status,required_date,fulfilment_method,total_amount',
            'quotation:id,quotation_number,status',
            'artworkRequest:id,request_number,status,current_version,title',
            'inventoryItem:id,item_name,sku,uses_serial_numbers',
            'customerArtwork:id,artwork_name,version_number,customer_id',
            'serialAllocation',
            'outsourceVendor:id,vendor_name,vendor_code',
            'productionSpecification.paperInventoryItem:id,item_name,sku',
            'productionSpecification.materialInventoryItem:id,item_name,sku',
            'productionSpecification.inkProfile:id,name',
            'productionSpecification.printProductTemplate.preferredWorkCenter:id,name',
            'productionSpecification.printProductTemplate.preferredMachineAsset:id,asset_name',
            'assignedMachine:id,asset_name,asset_number',
            'costSheet',
            'routeSteps' => fn ($q) => $q->with(['completedByUser:id,name', 'workCenter:id,name'])->orderBy('sequence'),
            'queues' => fn ($q) => $q->with(['workCenter:id,name,code,requires_machine', 'assignedOperator:id,name'])
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
            'product_name' => $jobCard->inventoryItem?->item_name,
            'product_sku' => $jobCard->inventoryItem?->sku,
            'sales_order_number' => $jobCard->salesOrder?->order_number,
            'sales_order_id' => $jobCard->sales_order_id,
            'quotation_number' => $jobCard->quotation?->quotation_number,
            'quotation_id' => $jobCard->quotation_id,
            'artwork_number' => $jobCard->artworkRequest?->request_number,
            'artwork_request_id' => $jobCard->artwork_request_id,
            'production_type' => $jobCard->production_type,
            'due_date' => $jobCard->planned_end_date,
            'work_center' => $primaryQueue?->workCenter?->name,
            'machine_name' => $jobCard->assignedMachine?->asset_name,
            'operator_name' => $primaryQueue?->assignedOperator?->name,
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
    protected function linkActions(ProductionJobCard $jobCard): array
    {
        $user = auth()->user();
        $links = [];

        if ($jobCard->sales_order_id && $user?->can('view', $jobCard->salesOrder)) {
            $jobCard->loadMissing('salesOrder:id,public_id,order_number');
            $links[] = ['label' => __('Sales order'), 'url' => route('admin.sales-orders.show', $jobCard->salesOrder)];
        }

        if ($jobCard->customer_id && $user?->can('view', $jobCard->customer)) {
            $jobCard->loadMissing('customer:id,public_id,company_name');
            $links[] = ['label' => __('Customer 360'), 'url' => route('admin.crm.customers.show', $jobCard->customer)];
        }

        if ($jobCard->artwork_request_id && $user?->can('view', $jobCard->artworkRequest)) {
            $jobCard->loadMissing('artworkRequest:id,public_id,request_number');
            $links[] = ['label' => __('Artwork'), 'url' => route('admin.artwork.show', $jobCard->artworkRequest)];
        }

        if ($user?->can('start', $jobCard)) {
            $links[] = ['label' => __('Log operation'), 'url' => route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => self::TAB_OPERATIONS])];
        }

        if ($user?->can('inventory.issue')) {
            $links[] = ['label' => __('Materials'), 'url' => route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => self::TAB_MATERIALS])];
        }

        $links[] = ['label' => __('Production floor'), 'url' => route('admin.production.floor')];

        if (JobCardPrintUrl::usesJobSheet($jobCard) && ($printUrl = JobCardPrintUrl::resolve($jobCard))) {
            $links[] = [
                'label' => JobCardPrintUrl::actionLabel($jobCard),
                'url' => $printUrl,
                'target' => '_blank',
            ];
        }

        if (Route::has('admin.production.job-cards.floor-display')) {
            $links[] = ['label' => __('Job sheet'), 'url' => route('admin.production.job-cards.floor-display', $jobCard), 'target' => '_blank'];
        }

        return $links;
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function quickActions(ProductionJobCard $jobCard): array
    {
        return $this->linkActions($jobCard);
    }

    /**
     * @return list<array<string, string|bool>>
     */
    protected function tabNavigation(ProductionJobCard $jobCard, string $activeTab): array
    {
        $navTabs = array_values(array_unique([...self::PRIMARY_TABS, ...self::MORE_TABS]));

        return collect($navTabs)->map(fn (string $tab) => [
            'id' => $tab,
            'label' => $this->tabLabel($tab),
            'url' => route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => $tab]),
            'active' => $tab === $activeTab,
            'primary' => in_array($tab, self::PRIMARY_TABS, true),
        ])->all();
    }

    /**
     * @return array{primary: list<array<string, mixed>>, more: list<array<string, mixed>>, more_open: bool}
     */
    protected function tabGroups(ProductionJobCard $jobCard, string $activeTab): array
    {
        $tabs = $this->tabNavigation($jobCard, $activeTab);

        return [
            'primary' => collect($tabs)->where('primary', true)->values()->all(),
            'more' => collect($tabs)->where('primary', false)->values()->all(),
            'more_open' => in_array($activeTab, self::MORE_TABS, true),
        ];
    }

    protected function tabLabel(string $tab): string
    {
        return match ($tab) {
            self::TAB_OVERVIEW => __('Overview'),
            self::TAB_MANUFACTURING => __('Manufacturing'),
            self::TAB_SPECIFICATION => __('Specification'),
            self::TAB_COMMERCIAL => __('Commercial'),
            self::TAB_COMMUNICATIONS => __('Communications'),
            self::TAB_TRACEABILITY => __('Traceability'),
            self::TAB_ARTWORK => __('Artwork'),
            self::TAB_ROUTE => __('Production Route'),
            self::TAB_SERIALS => __('Serial Numbers'),
            self::TAB_SESSIONS => __('Production Sessions'),
            self::TAB_OPERATIONS => __('Production'),
            self::TAB_MATERIALS => __('Materials'),
            self::TAB_MATERIAL_ISSUES => __('Issues'),
            self::TAB_MATERIAL_CONSUMPTION => __('Consumption'),
            self::TAB_OUTPUTS => __('Finished Goods'),
            self::TAB_QUALITY => __('QC'),
            self::TAB_FULFILMENT => __('Fulfilment'),
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
            self::TAB_MANUFACTURING => $this->manufacturingTab($jobCard),
            self::TAB_SPECIFICATION => $this->specificationTab($jobCard),
            self::TAB_COMMERCIAL => $this->commercialTab($jobCard),
            self::TAB_COMMUNICATIONS => $this->communicationsTab($jobCard),
            self::TAB_TRACEABILITY => ['chain' => $this->traceabilityChain($jobCard)],
            self::TAB_ARTWORK => $this->artworkTab($jobCard),
            self::TAB_ROUTE => $this->routeTab($jobCard),
            self::TAB_SERIALS => $this->serialsTab($jobCard),
            self::TAB_SESSIONS => $this->sessionsTab($jobCard),
            self::TAB_OPERATIONS => $this->operationsTab($jobCard),
            self::TAB_MATERIALS => $this->materialsTab($jobCard),
            self::TAB_MATERIAL_ISSUES => $this->materialIssuesTab($jobCard),
            self::TAB_MATERIAL_CONSUMPTION => $this->materialConsumptionTab($jobCard),
            self::TAB_OUTPUTS => $this->outputsTab($jobCard),
            self::TAB_QUALITY => $this->qualityTab($jobCard),
            self::TAB_FULFILMENT => $this->fulfilmentTab($jobCard),
            self::TAB_DISPATCH => $this->dispatchTab($jobCard),
            self::TAB_TIMELINE => array_merge(
                app(JobTimelineService::class)->paginate(
                    $jobCard,
                    $timelineQuery['timeline_filter'] ?? null,
                    $timelineQuery['timeline_search'] ?? null,
                    isset($timelineQuery['timeline_page']) ? (int) $timelineQuery['timeline_page'] : null,
                ),
                [
                    'ready' => true,
                    'communications' => $this->jobCommunications($jobCard),
                    'manufacturing_pipeline' => app(JobCardManufacturingPresenter::class)
                        ->present($jobCard)['timeline_pipeline'] ?? [],
                ],
            ),
            default => [],
        };
    }

    /**
     * @return array<string, mixed>
     */
    protected function manufacturingTab(ProductionJobCard $jobCard): array
    {
        $manufacturing = app(JobCardManufacturingPresenter::class)->present($jobCard);

        $data = array_merge($manufacturing, [
            'artwork' => $this->artworkTab($jobCard),
            'quality' => $this->qualityTab($jobCard),
        ]);

        $data['dashboard_cards'] = app(JobCardManufacturingPresenter::class)
            ->dashboardCards($jobCard, $data);

        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    protected function commercialTab(ProductionJobCard $jobCard): array
    {
        $costSheet = $jobCard->costSheet;
        $canViewCosting = auth()->user()?->can('production.costing.view') ?? false;
        $salesOrder = $jobCard->salesOrder;

        return [
            'can_view_costing' => $canViewCosting,
            'sales_order' => $salesOrder ? [
                'number' => $salesOrder->order_number,
                'status' => $salesOrder->status->value,
                'total' => $salesOrder->total_amount ?? null,
                'currency' => config('accounting.base_currency', 'KES'),
            ] : null,
            'cost_summary' => ($canViewCosting && $costSheet) ? [
                'material' => (float) $costSheet->material_cost,
                'labor' => (float) $costSheet->labor_cost,
                'outsource' => (float) $costSheet->outsourced_cost,
                'total' => (float) $costSheet->total_cost,
                'revenue' => (float) $costSheet->revenue,
                'gross_profit' => (float) $costSheet->gross_profit,
                'read_only' => true,
            ] : null,
            'cost_detail_url' => ($canViewCosting && Route::has('admin.production.job-cards.costing'))
                ? route('admin.production.job-cards.costing', $jobCard)
                : null,
            'outsource' => $this->outsourceContext($jobCard),
            'manufacturing_cost_hint' => app(JobCardManufacturingPresenter::class)->present($jobCard)['cost_summary'] ?? null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function communicationsTab(ProductionJobCard $jobCard): array
    {
        return [
            'communications' => $this->jobCommunications($jobCard),
            'can_view' => auth()->user()?->can('communications.email.view') ?? false,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function specificationTab(ProductionJobCard $jobCard): array
    {
        $service = app(ProductionSpecificationService::class);
        $spec = $jobCard->productionSpecification ?? $service->findForJobCard($jobCard);

        if ($spec && ! $spec->relationLoaded('paperInventoryItem')) {
            $spec->load(['paperInventoryItem', 'materialInventoryItem', 'inkProfile']);
        }

        return [
            'specification' => $service->present($spec),
            'edit_url' => ($spec && $spec->sales_order_id && $spec->sales_order_item_id && auth()->user()?->can('update', $spec))
                ? route('admin.sales-orders.items.specification.edit', [
                    'salesOrder' => $spec->sales_order_id,
                    'salesOrderItem' => $spec->sales_order_item_id,
                    'specification' => $spec->id,
                ])
                : null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function overviewTab(ProductionJobCard $jobCard): array
    {
        $completion = app(ProductionCompletionService::class)->eligibility($jobCard);
        $manufacturing = app(JobCardManufacturingPresenter::class)->present($jobCard);

        return [
            'completion' => $completion,
            'manufacturing_summary' => [
                'has_specification' => $manufacturing['has_specification'],
                'product' => collect($manufacturing['sections']['general'] ?? [])->firstWhere('label', __('Product'))['value'] ?? null,
                'quantity' => collect($manufacturing['sections']['general'] ?? [])->firstWhere('label', __('Quantity'))['value'] ?? null,
                'production_type' => collect($manufacturing['sections']['general'] ?? [])->firstWhere('label', __('Production type'))['value'] ?? null,
                'estimated_sheets' => $manufacturing['material_plan']['estimated_sheets'] ?? null,
                'empty_message' => $manufacturing['empty_message'] ?? null,
                'manufacturing_url' => route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => self::TAB_MANUFACTURING]),
            ],
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
            'print_specification_source' => $this->printSpecificationSource($jobCard),
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
            'outsource' => $this->outsourceContext($jobCard),
            'queue' => $this->queueContext($jobCard),
            'open_operations' => ProductionOperation::query()
                ->where('production_job_card_id', $jobCard->id)
                ->whereNull('ended_at')
                ->with(['workCenter:id,name', 'stage:id,name', 'assignedEmployee:id,first_name,last_name'])
                ->orderBy('started_at')
                ->get(),
            'can_complete_op' => auth()->user()?->can('complete', $jobCard) ?? false,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function queueContext(ProductionJobCard $jobCard): array
    {
        $context = app(\App\Support\Production\RouteStepQueueService::class)->currentQueueContext($jobCard);

        return [
            'status' => $context['current']?->status?->value,
            'status_label' => $context['current']?->status?->label(),
            'work_center' => $context['work_center']?->name,
            'work_center_code' => $context['work_center']?->code,
            'position' => $context['position'],
            'priority' => $jobCard->priority?->value,
            'required_date' => $jobCard->required_date?->format('Y-m-d')
                ?? $jobCard->planned_end_date?->format('Y-m-d'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function outsourceContext(ProductionJobCard $jobCard): array
    {
        $canOutsource = auth()->user()?->can('update', $jobCard)
            && $jobCard->status->canTransitionTo(ProductionJobCardStatus::Outsourced);

        $canReturn = $jobCard->status === ProductionJobCardStatus::Outsourced
            && auth()->user()?->can('update', $jobCard);

        return [
            'vendor' => $jobCard->outsourceVendor,
            'quoted_cost' => $jobCard->outsource_quoted_cost,
            'actual_cost' => $jobCard->outsource_actual_cost,
            'issue_date' => $jobCard->outsource_issue_date,
            'expected_return' => $jobCard->outsource_expected_return,
            'notes' => $jobCard->outsource_notes,
            'outsourced_at' => $jobCard->outsourced_at,
            'returned_at' => $jobCard->returned_at,
            'can_outsource' => $canOutsource,
            'can_return' => $canReturn,
            'production_vendors' => $canOutsource
                ? \App\Models\Procurement\Vendor::query()
                    ->forTenant()
                    ->where('is_production_vendor', true)
                    ->where('status', 'active')
                    ->orderBy('vendor_name')
                    ->get(['id', 'vendor_name', 'vendor_code'])
                : collect(),
            'cost_exposure' => app(JobCardOutsourceService::class)->costExposure($jobCard),
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
            $jobCard->quotation ? route('admin.quotations.show', $jobCard->quotation) : null,
            (bool) $jobCard->quotation_id,
        );

        $steps[] = $this->chainStep(
            __('Sales Order'),
            $jobCard->salesOrder?->order_number,
            $jobCard->salesOrder ? route('admin.sales-orders.show', $jobCard->salesOrder) : null,
            (bool) $jobCard->sales_order_id,
        );

        $steps[] = $this->chainStep(
            __('Artwork Request'),
            $jobCard->artworkRequest?->request_number,
            $jobCard->artworkRequest ? route('admin.artwork.show', $jobCard->artworkRequest) : null,
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
            'url' => $jobCard->artworkRequest ? route('admin.artwork.show', $jobCard->artworkRequest) : null,
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

        $dispatchPresentation = app(JobDispatchPresentationService::class)->build($jobCard);

        $steps[] = [
            'label' => __('Dispatch'),
            'reference' => $this->dispatchReadinessLabel($jobCard, $dispatchPresentation),
            'url' => ($dispatchPresentation['has_delivery_note'] ?? false) && ! empty($dispatchPresentation['summary']['show_url'])
                ? $dispatchPresentation['summary']['show_url']
                : route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => self::TAB_DISPATCH]),
            'state' => match (true) {
                ($dispatchPresentation['has_delivery_note'] ?? false) => 'complete',
                $jobCard->status === ProductionJobCardStatus::ReadyForDispatch => 'complete',
                $jobCard->status === ProductionJobCardStatus::Completed => 'pending',
                default => 'pending',
            },
            'placeholder' => false,
        ];

        if (Schema::hasTable('delivery_notes')) {
            $delivered = $jobCard->deliveryNotes()
                ->where('status', \App\Enums\Dispatch\DeliveryNoteStatus::Delivered)
                ->exists();
            $active = $dispatchPresentation['delivery_note'] ?? $jobCard->deliveryNotes()
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
        $customerArtwork = $jobCard->customerArtwork;

        if (! $jobCard->artwork_request_id && ! $customerArtwork) {
            return ['empty' => true];
        }

        $request = $jobCard->artwork_request_id && ! $jobCard->isDirectOrderSource()
            ? ArtworkRequest::query()
                ->with([
                    'approvals.approver:id,name',
                    'files',
                    'assignedDesigner:id,name',
                ])
                ->find($jobCard->artwork_request_id)
            : null;

        $latestApproval = $request?->approvals->first();
        $rejection = $request?->approvals->first(
            fn ($a) => $a->decision === ArtworkApprovalDecision::Rejected,
        );

        return [
            'request' => $request,
            'customer_artwork' => $jobCard->customerArtwork,
            'print_specification_source' => $this->printSpecificationSource($jobCard),
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
    protected function routeTab(ProductionJobCard $jobCard): array
    {
        $progress = app(ProductionRouteService::class)->routeProgress($jobCard);
        $steps = $progress['all'];

        return [
            'progress' => $progress,
            'can_update' => auth()->user()?->can('start', $jobCard) ?? false,
            'statuses' => JobCardRouteStepStatus::cases(),
            'summary' => [
                'total' => $steps->count(),
                'completed' => $progress['completed']->count(),
                'pending' => $progress['pending']->count(),
                'current' => $progress['current']?->step_name,
                'percent' => $steps->isEmpty() ? 0 : (int) round(($progress['completed']->count() / $steps->count()) * 100),
            ],
            'outsource' => $this->outsourceContext($jobCard),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function serialsTab(ProductionJobCard $jobCard): array
    {
        $allocation = $jobCard->serialAllocation;
        $service = app(SerialNumberGovernanceService::class);

        return [
            'allocation' => $allocation,
            'next_range_preview' => $allocation ? $service->nextRangePreview($allocation) : null,
            'loss_metrics' => $service->productionLossMetrics($jobCard),
            'spoiled_ranges' => $allocation
                ? $jobCard->spoiledSerialRanges()->with('recordedByUser:id,name')->get()
                : collect(),
            'can_confirm' => $allocation && ! $allocation->is_confirmed && (auth()->user()?->can('complete', $jobCard) ?? false),
        ];
    }

    /**
     * @return \Illuminate\Support\Collection<int, InventoryItem>
     */
    protected function finishedItemsCatalog(ProductionJobCard $jobCard): \Illuminate\Support\Collection
    {
        $key = $jobCard->company_id.'_'.$jobCard->branch_id.'_'.$jobCard->id;

        if (! isset($this->finishedItemsCache[$key])) {
            $catalog = InventoryItem::query()
                ->where('company_id', $jobCard->company_id)
                ->where('branch_id', $jobCard->branch_id)
                ->where('stock_role', \App\Enums\InventoryStockRole::FinishedGood)
                ->where('is_active', true)
                ->orderBy('item_name')
                ->get(['id', 'sku', 'item_name', 'stock_role']);

            $linkedIds = $catalog->pluck('id');

            $suggested = app(ProductionCompletionService::class)->resolveFinishedItem($jobCard, null, false);
            if ($suggested !== null && ! $linkedIds->contains($suggested->id)) {
                $suggested->loadMissing([]);
                $catalog->prepend($suggested);
            }

            $this->finishedItemsCache[$key] = $catalog->unique('id')->values();
        }

        return $this->finishedItemsCache[$key];
    }

    /**
     * @return array{
     *     finishedItems: \Illuminate\Support\Collection<int, InventoryItem>,
     *     rawMaterials: \Illuminate\Support\Collection<int, InventoryItem>,
     *     suggestedLines: list<array<string, mixed>>
     * }
     */
    protected function materialsBomFormMeta(ProductionJobCard $jobCard, ?int $preselectedFinishedItemId): array
    {
        $finishedItems = InventoryItem::query()
            ->where('company_id', $jobCard->company_id)
            ->where('branch_id', $jobCard->branch_id)
            ->where('is_active', true)
            ->where(function ($query) use ($preselectedFinishedItemId) {
                $query->where('stock_role', InventoryStockRole::FinishedGood);

                if ($preselectedFinishedItemId) {
                    $query->orWhere('id', $preselectedFinishedItemId);
                }
            })
            ->orderBy('item_name')
            ->get(['id', 'sku', 'item_name']);

        $rawMaterials = InventoryItem::query()
            ->where('company_id', $jobCard->company_id)
            ->where('branch_id', $jobCard->branch_id)
            ->where('is_active', true)
            ->whereIn('stock_role', [
                InventoryStockRole::RawMaterial,
                InventoryStockRole::Consumable,
                InventoryStockRole::Packaging,
            ])
            ->with('category:id,name,code')
            ->orderBy('sku')
            ->get(['id', 'sku', 'item_name', 'inventory_category_id', 'stock_role']);

        return [
            'finishedItems' => $finishedItems,
            'rawMaterials' => $rawMaterials,
            'suggestedLines' => app(ProductBomService::class)->suggestedLinesForJobCard($jobCard),
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function printSpecificationSource(ProductionJobCard $jobCard): ?array
    {
        if (! $jobCard->customer_print_specification_id && ! $jobCard->specification_code) {
            return null;
        }

        $specLabel = $jobCard->specification_code
            ? trim($jobCard->specification_code.($jobCard->specification_name ? ' — '.$jobCard->specification_name : ''))
            : ($jobCard->specification_name ?? null);

        return [
            'order_source' => $jobCard->order_source,
            'order_source_label' => $jobCard->isDirectOrderSource()
                ? __('Direct Order')
                : ($jobCard->isQuotationOrderSource() ? __('Quotation') : __('Unknown')),
            'specification_code' => $jobCard->specification_code,
            'specification_name' => $jobCard->specification_name,
            'specification_label' => $specLabel,
            'artwork_version' => $jobCard->artwork_version_number,
            'product_name' => $jobCard->inventoryItem?->item_name,
            'production_notes' => $jobCard->production_notes_snapshot,
            'commercial_notes' => $jobCard->commercial_notes_snapshot,
            'customer_instructions' => $jobCard->customer_instructions_snapshot,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function sessionsTab(ProductionJobCard $jobCard): array
    {
        $service = app(ProductionSessionService::class);

        return [
            'sessions' => $jobCard->productionSessions()->with('operator:id,name')->paginate(20, pageName: 'sessions_page'),
            'metrics' => $service->jobMetrics($jobCard),
            'waste_reasons' => $service->wasteReasons(),
            'can_log' => auth()->user()?->can('start', $jobCard) ?? false,
            'material_requirements' => app(\App\Support\Production\MaterialRequirementsService::class)->panelRows($jobCard),
            'can_capture_materials' => auth()->user()?->can('production.materials.consume') ?? false,
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
            'can_manage_queue' => auth()->user()?->can('schedule', $jobCard) ?? false,
            'queue_statuses' => \App\Enums\ProductionQueueStatus::cases(),
            'queues' => $jobCard->queues()->with('workCenter:id,name')->get(),
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
            'finished_items' => $this->finishedItemsCatalog($jobCard),
            'readiness_checklist' => $this->controls->readinessChecklist($jobCard),
            'has_posted_output' => app(ProductionCompletionService::class)->hasPostedFinishedGoods($jobCard),
            'dispatch_eligibility' => $this->controls->dispatchEligibility($jobCard),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function materialsTab(ProductionJobCard $jobCard): array
    {
        $requirementsService = app(\App\Support\Production\MaterialRequirementsService::class);
        $requirements = $requirementsService->panelRows($jobCard);
        $costs = app(\App\Support\Production\ProductionMaterialCostVisibilityService::class)->summary($jobCard);
        $jobCard->loadMissing('inventoryItem:id,sku,item_name');
        $workflow = $requirementsService->workflowChecklist($jobCard);
        $user = auth()->user();
        $canCreateBom = (bool) ($user?->can('create', \App\Models\Production\ProductBom::class)
            || $user?->can('production.edit'));
        $canGenerateMaterials = (bool) ($user?->can('production.materials.generate')
            || $user?->can('production.edit'));
        $canLinkProduct = (bool) ($user?->can('production.edit') || $user?->can('production.materials.generate'));

        $missingBomActions = collect($workflow['missing_boms'] ?? [])->map(function (array $source) use ($jobCard, $canCreateBom) {
            return [
                'finished_item_id' => $source['finished_item_id'],
                'label' => trim(($source['sku'] ?? '').' — '.($source['item_name'] ?? __('Finished product'))),
                'create_url' => $canCreateBom
                    ? route('admin.production.boms.create', [
                        'finished_item_id' => $source['finished_item_id'],
                        'job_card_id' => $jobCard->getRouteKey(),
                        'name' => trim(($source['item_name'] ?? 'BOM').' BOM'),
                    ])
                    : null,
            ];
        })->values()->all();

        if ($missingBomActions === [] && ($workflow['current_key'] ?? null) === 'bom' && $jobCard->inventory_item_id) {
            $item = $jobCard->inventoryItem;
            $missingBomActions[] = [
                'finished_item_id' => (int) $jobCard->inventory_item_id,
                'label' => trim(($item?->sku ?? '').' — '.($item?->item_name ?? __('Finished product'))),
                'create_url' => $canCreateBom
                    ? route('admin.production.boms.create', [
                        'finished_item_id' => $jobCard->inventory_item_id,
                        'job_card_id' => $jobCard->getRouteKey(),
                        'name' => trim(($item?->item_name ?? 'BOM').' BOM'),
                    ])
                    : null,
            ];
        }

        $preselectedFinishedItemId = (int) ($missingBomActions[0]['finished_item_id'] ?? $jobCard->inventory_item_id ?? 0) ?: null;
        $bomFormMeta = $this->materialsBomFormMeta($jobCard, $preselectedFinishedItemId);

        $readiness = app(\App\Support\Production\MaterialReadinessService::class)->assess($jobCard);
        $shortages = collect($readiness['missing'] ?? [])->values()->all();
        $hasShortages = count($shortages) > 0;
        $reservableCount = $requirements->filter(fn (array $row) => (bool) ($row['can_reserve'] ?? false))->count();
        $pendingConsumeCount = $requirements->filter(fn (array $row) => (float) ($row['remaining'] ?? 0) > 0)->count();
        $consumableCount = $requirements->filter(function (array $row) {
            return (float) ($row['remaining'] ?? 0) > 0 && (float) ($row['available'] ?? 0) > 0;
        })->count();

        return [
            'requirements' => $requirements,
            'costs' => $costs,
            'workflow' => $workflow,
            'has_requirements' => $requirements->isNotEmpty(),
            'shortages' => $shortages,
            'has_shortages' => $hasShortages,
            'short_count' => (int) ($readiness['short_count'] ?? 0),
            'ready_count' => (int) ($readiness['ready_count'] ?? 0),
            'reservable_count' => $reservableCount,
            'pending_consume_count' => $pendingConsumeCount,
            'consumable_count' => $consumableCount,
            'missing_boms' => $missingBomActions,
            'can_create_bom' => $canCreateBom,
            'can_link_product' => $canLinkProduct && ! ($workflow['has_finished_product'] ?? false),
            'finished_items' => $this->finishedItemsCatalog($jobCard),
            'bom_finished_items' => $bomFormMeta['finishedItems'],
            'bom_raw_materials' => $bomFormMeta['rawMaterials'],
            'bom_suggested_lines' => $bomFormMeta['suggestedLines'],
            'bom_preselected_finished_item_id' => $preselectedFinishedItemId,
            'bom_prefilled_name' => $preselectedFinishedItemId
                ? trim(($missingBomActions[0]['label'] ?? 'BOM').' BOM')
                : null,
            'can_generate' => $canGenerateMaterials && (bool) ($workflow['can_generate'] ?? false),
            'can_show_generate_form' => $canGenerateMaterials,
            'can_reserve' => (auth()->user()?->can('production.materials.reserve') ?? false)
                && $requirements->isNotEmpty(),
            'can_receive_stock' => auth()->user()?->can('inventory.receive') ?? false,
            'receipts_url' => route('admin.inventory.receipts.create', [
                'job_card_id' => $jobCard->getRouteKey(),
            ]),
            'can_consume' => $this->userCanRecordMaterialConsumption(),
            'warehouses' => Warehouse::query()
                ->forTenant()
                ->physical()
                ->where('is_active', true)
                ->orderByRaw("CASE WHEN code = 'MAIN' THEN 0 WHEN code = 'FG' THEN 9 ELSE 1 END")
                ->orderBy('name')
                ->get(['id', 'name']),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function materialIssuesTab(ProductionJobCard $jobCard): array
    {
        $issues = \App\Models\Production\ProductionMaterialIssue::query()
            ->where('production_job_card_id', $jobCard->id)
            ->with(['inventoryItem:id,sku,item_name', 'inventoryItem.unitOfMeasure:id,code', 'warehouse:id,name', 'issuer:id,name', 'requirement:id,required_quantity'])
            ->orderByDesc('issued_at')
            ->paginate(25, pageName: 'issues_page');

        $requirements = app(\App\Support\Production\MaterialRequirementsService::class)->panelRows($jobCard);

        return [
            'issues' => $issues,
            'requirements' => $requirements,
            'can_issue' => auth()->user()?->can('production.materials.issue') ?? false,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function materialConsumptionTab(ProductionJobCard $jobCard): array
    {
        $consumptions = ProductionMaterialConsumption::query()
            ->where('production_job_card_id', $jobCard->id)
            ->with(['inventoryItem:id,sku,item_name,unit_of_measure_id', 'inventoryItem.unitOfMeasure:id,code', 'warehouse:id,name', 'movement:id,movement_type', 'consumer:id,name'])
            ->latest('consumed_at')
            ->paginate(25, pageName: 'consumption_page');

        $wastage = app(\App\Support\Production\ProductionWastageService::class)->summaryForJob($jobCard);
        $sessionMetrics = app(\App\Support\Production\ProductionSessionService::class)->jobMetrics($jobCard);
        $serialLoss = app(\App\Support\Production\SerialNumberGovernanceService::class)->productionLossMetrics($jobCard);
        $requirements = app(\App\Support\Production\MaterialRequirementsService::class)->panelRows($jobCard);

        return [
            'consumptions' => $consumptions,
            'wastage' => $wastage,
            'session_waste' => $sessionMetrics,
            'serial_spoilage' => $serialLoss,
            'material_requirements' => $requirements,
            'can_consume' => $this->userCanRecordMaterialConsumption(),
            'can_record_waste' => auth()->user()?->can('production.wastage.record') ?? false,
            'inventory_items' => InventoryItem::query()
                ->forTenant()
                ->where('is_active', true)
                ->where('stock_role', InventoryStockRole::RawMaterial)
                ->orderBy('sku')
                ->get(['id', 'sku', 'item_name']),
            'warehouses' => Warehouse::query()
                ->forTenant()
                ->physical()
                ->where('is_active', true)
                ->orderByRaw("CASE WHEN code = 'MAIN' THEN 0 WHEN code = 'FG' THEN 2 ELSE 1 END")
                ->orderBy('name')
                ->get(['id', 'name', 'code']),
        ];
    }

    protected function userCanRecordMaterialConsumption(): bool
    {
        $user = auth()->user();

        return $user !== null
            && $user->can('production.materials.consume')
            && $user->can('inventory.issue');
    }

    /**
     * @return array<string, mixed>
     */
    protected function qualityTab(ProductionJobCard $jobCard): array
    {
        $checks = QualityCheck::query()
            ->where('production_job_card_id', $jobCard->id)
            ->with(['checker:id,name', 'customerApprover:id,name'])
            ->latest('checked_at')
            ->paginate(25, pageName: 'quality_page');

        $snapshot = \App\Models\Production\JobCardQcSnapshot::query()
            ->where('production_job_card_id', $jobCard->id)
            ->first();

        $serialService = app(SerialNumberGovernanceService::class);
        $allocation = $jobCard->serialAllocation;
        $qcSummary = $this->controls->qcStatusSummary($jobCard);
        $needsQc = ($qcSummary['status'] ?? 'none') === 'none'
            || $this->controls->hasUnresolvedQcFailure($jobCard);
        $canSendToQc = (auth()->user()?->can('complete', $jobCard) ?? false)
            && $jobCard->status->canTransitionTo(ProductionJobCardStatus::QualityCheck);

        return [
            'checks' => $checks,
            'snapshot' => $snapshot,
            'rework_summary' => app(\App\Support\Production\QualityInspectionService::class)->reworkSummary($jobCard),
            'serial_ranges' => [
                'allocated_start' => $allocation?->serial_start,
                'allocated_end' => $allocation?->serial_end,
                'produced_end' => $allocation?->produced_end,
                'spoiled_quantity' => $allocation?->spoiled_quantity,
                'loss_metrics' => $serialService->productionLossMetrics($jobCard),
                'spoiled_ranges' => $allocation
                    ? $jobCard->spoiledSerialRanges()->get(['serial_start', 'serial_end', 'quantity'])
                    : collect(),
            ],
            'fail_reasons' => \App\Enums\QualityFailReason::cases(),
            'rework_reasons' => \App\Enums\QualityReworkReason::cases(),
            'can_record' => auth()->user()?->can('create', [QualityCheck::class, $jobCard]) ?? false,
            'can_send_to_qc' => $canSendToQc,
            'needs_qc' => $needsQc,
            'qc_summary' => $qcSummary,
            'can_approve_customer' => auth()->user()?->can('approveCustomerHold', $jobCard) ?? false,
            'qc_blocking' => $this->controls->hasUnresolvedQcFailure($jobCard)
                || (($qcSummary['status'] ?? null) === 'none' && $needsQc),
            'pending_customer_approval' => $checks->first(fn ($c) => $c->requires_customer_approval
                && $c->result === \App\Enums\QualityCheckResult::ConditionalPass
                && $c->customer_approved_at === null),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function fulfilmentTab(ProductionJobCard $jobCard): array
    {
        $fulfilment = app(\App\Support\Production\ProductionFulfilmentService::class)
            ->resolveForJobCard($jobCard)
            ->load(['preparedByUser:id,name', 'dispatchedByUser:id,name', 'deliveryNote:id,delivery_note_number,status']);

        $method = $jobCard->salesOrder?->fulfilment_method
            ?? $fulfilment->fulfilment_method;

        return [
            'fulfilment' => $fulfilment,
            'fulfilment_method' => $method,
            'ready_for_dispatch' => $jobCard->status === ProductionJobCardStatus::ReadyForDispatch,
            'can_fulfil' => auth()->user()?->can('fulfil', $jobCard) ?? false,
            'invoice_ready' => $fulfilment->invoice_ready,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function dispatchTab(ProductionJobCard $jobCard): array
    {
        $dispatchPresentation = app(JobDispatchPresentationService::class)->build($jobCard);
        $eligibility = $this->controls->dispatchEligibility($jobCard);
        $creationEligibility = $this->controls->deliveryNoteCreationEligibility($jobCard);

        $deliveryNotes = Schema::hasTable('delivery_notes')
            ? $jobCard->deliveryNotes()
                ->with(['dispatcher:id,name', 'deliverer:id,name'])
                ->orderByDesc('id')
                ->get()
            : collect();

        $activeNote = $dispatchPresentation['delivery_note'] ?? $deliveryNotes->first(
            fn ($note) => $note->status !== \App\Enums\Dispatch\DeliveryNoteStatus::Cancelled
        );

        return [
            'dispatch_presentation' => $dispatchPresentation,
            'ready_for_dispatch' => $jobCard->status === ProductionJobCardStatus::ReadyForDispatch
                && ! ($dispatchPresentation['has_delivery_note'] ?? false),
            'readiness_label' => $this->dispatchReadinessLabel($jobCard, $dispatchPresentation),
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
                ProductionJobCardStatus::ReadyForDispatch => 100,
                ProductionJobCardStatus::Completed => 90,
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

    protected function dispatchReadinessLabel(ProductionJobCard $jobCard, ?array $dispatchPresentation = null): string
    {
        $dispatchPresentation ??= app(JobDispatchPresentationService::class)->build($jobCard);

        if ($dispatchPresentation['has_delivery_note'] ?? false) {
            return $dispatchPresentation['workflow_label'];
        }

        return app(JobWorkflowPresentationService::class)->present($jobCard)['phase_label'];
    }

    /**
     * @param  array<string, mixed>  $executionState
     * @param  array<string, mixed>  $workflow
     * @param  array<string, mixed>  $dispatchSummary
     * @return array<string, mixed>
     */
    protected function mergeWorkflowIntoExecutionState(array $executionState, array $workflow, array $dispatchSummary): array
    {
        if ($dispatchSummary['has_delivery_note'] ?? false) {
            $executionState['dispatch_summary'] = $dispatchSummary;

            return $executionState;
        }

        if (! in_array($workflow['phase'] ?? '', ['awaiting_fg_post', 'dispatch_blocked', 'dispatch'], true)) {
            return $executionState;
        }

        $executionState['phase'] = $workflow['phase'];
        $executionState['phase_label'] = $workflow['phase_label'];
        $executionState['next_action'] = $workflow['next_action'];
        $executionState['workflow_next_step'] = $workflow['next_step'];

        return $executionState;
    }

    /**
     * @param  list<array{type: string, message: string}>  $alerts
     * @param  array<string, mixed>  $dispatchSummary
     * @return list<array{type: string, message: string}>
     */
    protected function filterControlAlertsForDispatch(array $alerts, array $dispatchSummary): array
    {
        if (! ($dispatchSummary['has_delivery_note'] ?? false)) {
            return $alerts;
        }

        return array_values(array_filter($alerts, function (array $alert): bool {
            $message = strtolower($alert['message']);

            return ! str_contains($message, 'dispatch')
                && ! str_contains($message, 'delivery note')
                && ! str_contains($message, 'ready for dispatch');
        }));
    }

    protected function statusExplanation(ProductionJobCard $jobCard): string
    {
        return match ($jobCard->status) {
            ProductionJobCardStatus::Draft => __('Job card is drafted and awaiting scheduling.'),
            ProductionJobCardStatus::Queued => __('Job is queued for production.'),
            ProductionJobCardStatus::InProduction => __('Job is actively in production.'),
            ProductionJobCardStatus::QualityCheck => __('Job is undergoing quality inspection.'),
            ProductionJobCardStatus::Completed => __('Production is complete — post finished goods before dispatch.'),
            ProductionJobCardStatus::ReadyForDispatch => __('Finished goods posted — job is ready for dispatch.'),
            ProductionJobCardStatus::OnHold => __('Job is on hold.'),
            ProductionJobCardStatus::Rework => __('Job requires rework.'),
            ProductionJobCardStatus::Outsourced => __('Job is outsourced to an external vendor.'),
            ProductionJobCardStatus::Returned => __('Job has returned from vendor — pending internal QC or production.'),
            ProductionJobCardStatus::AwaitingCustomerApproval => __('Job is awaiting customer approval for conditional pass inspection.'),
            ProductionJobCardStatus::Cancelled => __('Job has been cancelled.'),
        };
    }

    protected function nextExpectedAction(ProductionJobCard $jobCard): string
    {
        return app(JobExecutionStateService::class)->state($jobCard)['next_action'];
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function jobCommunications(ProductionJobCard $jobCard): array
    {
        if (! auth()->user()?->can('communications.email.view')) {
            return [];
        }

        $visibility = app(EmailVisibilityService::class);

        return $visibility->forJobCard($jobCard)
            ->map(fn ($message) => $visibility->presentJobCommunication($message))
            ->values()
            ->all();
    }
}
