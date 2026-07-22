<?php

namespace App\Services\Production;

use App\Enums\FulfilmentStatus;
use App\Enums\ProductionFloorStage;
use App\Enums\ProductionJobCardStatus;
use App\Enums\ProductionPriority;
use App\Models\Assets\FixedAsset;
use App\Models\Production\ProductionJobCard;
use App\Models\Production\WorkCenter;
use App\Models\Procurement\Vendor;
use App\Models\User;
use App\Support\Production\JobCardOutsourceService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;

class ProductionFloorService
{
    public function __construct(
        protected ProductionDashboardCommandCenterService $commandCenter,
        protected ProductionFloorActionService $actions,
        protected JobCardOutsourceService $outsource,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function build(Request $request): array
    {
        $filters = $this->normalizeFilters($request);
        $query = $this->baseQuery($filters);
        $jobs = (clone $query)
            ->with([
                'customer:id,public_id,company_name',
                'inventoryItem:id,item_name,sku',
                'outsourceVendor:id,vendor_name',
                'assignedMachine:id,public_id,asset_name,asset_number',
                'fulfilment:id,production_job_card_id,status',
                'salesOrder:id,public_id,order_number,required_date',
                'queues.workCenter:id,public_id,name',
            ])
            ->latest('created_at')
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        $operatorMode = $request->user()?->prefersProductionOperatorMode() ?? false;

        return [
            'summary' => $this->summaryStrip(),
            'stage_counts' => $this->stageCounts($filters),
            'filters' => $filters,
            'jobs' => $jobs,
            'rows' => collect($jobs->items())->map(fn (ProductionJobCard $job) => $this->presentRow($job)),
            'filter_options' => $this->filterOptions(),
            'can_create' => auth()->user()?->can('create', ProductionJobCard::class) ?? false,
            'create_url' => Route::has('admin.production.job-cards.create')
                ? route('admin.production.job-cards.create', array_filter([
                    'from' => $operatorMode ? 'production-floor' : null,
                ]))
                : null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function panel(ProductionJobCard $jobCard, bool $operatorMode = false): array
    {
        $jobCard->loadMissing([
            'customer:id,public_id,company_name,customer_code',
            'inventoryItem:id,item_name,sku',
            'salesOrder:id,public_id,order_number,required_date,fulfilment_method,status',
            'outsourceVendor:id,vendor_name',
            'assignedMachine:id,public_id,asset_name',
            'fulfilment',
        ]);

        $outsource = $this->outsourcePanelData($jobCard);

        return [
            'job' => $this->presentRow($jobCard),
            'header' => [
                'job_number' => $jobCard->job_card_number,
                'customer' => $jobCard->customer?->company_name,
                'product' => $jobCard->inventoryItem?->item_name,
                'status' => $jobCard->status->label(),
                'stage' => $this->resolveStage($jobCard)->label(),
                'required_date' => $jobCard->required_date?->toDateString()
                    ?? $jobCard->salesOrder?->required_date?->toDateString(),
                'label_url' => route('admin.production.job-cards.label', $jobCard),
            ],
            'primary_action' => $this->actions->primaryAction($jobCard),
            'secondary_actions' => $this->actions->secondaryActions($jobCard),
            'operator_actions' => $this->actions->operatorActions($jobCard),
            'outsource' => $outsource,
            'fulfilment' => [
                'status' => $jobCard->fulfilment?->status?->value,
                'status_label' => $jobCard->fulfilment?->status?->label() ?? __('Not started'),
                'method' => $jobCard->salesOrder?->fulfilment_method?->value,
            ],
            'links' => [
                'job' => route('admin.production.job-cards.show', array_filter([
                    'jobCard' => $jobCard,
                    'from' => $operatorMode ? 'production-floor' : null,
                ])),
                'sales_order' => $jobCard->salesOrder
                    ? route('admin.sales-orders.show', array_filter([
                        'salesOrder' => $jobCard->salesOrder,
                        'from' => $operatorMode ? 'production-floor' : null,
                    ]))
                    : null,
            ],
            'machines' => $this->machinesForPanel(),
            'blockers' => app(JobProductionControlService::class)->dispatchEligibility($jobCard)['blockers'] ?? [],
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    protected function baseQuery(array $filters): Builder
    {
        $query = ProductionJobCard::query()
            ->forTenant()
            ->whereNot('status', ProductionJobCardStatus::Cancelled);

        if ($filters['stage'] === ProductionFloorStage::Out->value) {
            $query->whereHas('fulfilment', fn (Builder $q) => $q->whereIn('status', [
                FulfilmentStatus::Collected,
                FulfilmentStatus::Delivered,
            ]));
        } elseif ($filters['stage'] !== '') {
            $this->applyStageFilter($query, ProductionFloorStage::from($filters['stage']));
        } else {
            $query->where(function (Builder $q) {
                $q->whereNotIn('status', [ProductionJobCardStatus::ReadyForDispatch])
                    ->orWhereDoesntHave('fulfilment', fn (Builder $f) => $f->whereIn('status', [
                        FulfilmentStatus::Collected,
                        FulfilmentStatus::Delivered,
                    ]));
            });
        }

        if ($filters['search'] !== '') {
            $search = $filters['search'];
            $query->where(function (Builder $q) use ($search) {
                $q->where('job_card_number', 'like', "%{$search}%")
                    ->orWhereHas('customer', fn (Builder $c) => $c->where('company_name', 'like', "%{$search}%"))
                    ->orWhereHas('inventoryItem', fn (Builder $i) => $i->where('item_name', 'like', "%{$search}%"));
            });
        }

        if ($filters['machine_id'] !== '') {
            $query->where('assigned_machine_asset_id', $filters['machine_id']);
        }

        if ($filters['vendor_id'] !== '') {
            $query->where('outsource_vendor_id', $filters['vendor_id']);
        }

        if ($filters['priority'] !== '') {
            $query->where('priority', $filters['priority']);
        }

        if ($filters['overdue'] === '1') {
            $today = now()->toDateString();
            $query->whereNotNull('required_date')
                ->whereDate('required_date', '<', $today)
                ->whereNotIn('status', [
                    ProductionJobCardStatus::ReadyForDispatch,
                    ProductionJobCardStatus::Completed,
                    ProductionJobCardStatus::Cancelled,
                ]);
        }

        return $query;
    }

    protected function applyStageFilter(Builder $query, ProductionFloorStage $stage): void
    {
        match ($stage) {
            ProductionFloorStage::Waiting => $query->whereIn('status', [
                ProductionJobCardStatus::Draft,
                ProductionJobCardStatus::Queued,
            ]),
            ProductionFloorStage::OnPress => $query->whereIn('status', [
                ProductionJobCardStatus::InProduction,
                ProductionJobCardStatus::Rework,
            ]),
            ProductionFloorStage::AtVendor => $query->where('status', ProductionJobCardStatus::Outsourced),
            ProductionFloorStage::Finishing => $query->whereIn('status', [
                ProductionJobCardStatus::Returned,
                ProductionJobCardStatus::Completed,
            ]),
            ProductionFloorStage::Qc => $query->whereIn('status', [
                ProductionJobCardStatus::QualityCheck,
                ProductionJobCardStatus::AwaitingCustomerApproval,
            ]),
            ProductionFloorStage::Ready => $query->where('status', ProductionJobCardStatus::ReadyForDispatch)
                ->where(function (Builder $q) {
                    $q->whereDoesntHave('fulfilment')
                        ->orWhereHas('fulfilment', fn (Builder $f) => $f->whereNotIn('status', [
                            FulfilmentStatus::Collected,
                            FulfilmentStatus::Delivered,
                        ]));
                }),
            ProductionFloorStage::Out => $query->whereHas('fulfilment', fn (Builder $f) => $f->whereIn('status', [
                FulfilmentStatus::Collected,
                FulfilmentStatus::Delivered,
            ])),
            ProductionFloorStage::OnHold => $query->where('status', ProductionJobCardStatus::OnHold),
        };
    }

    protected function resolveStage(ProductionJobCard $jobCard): ProductionFloorStage
    {
        if ($jobCard->fulfilment && in_array($jobCard->fulfilment->status, [
            FulfilmentStatus::Collected,
            FulfilmentStatus::Delivered,
        ], true)) {
            return ProductionFloorStage::Out;
        }

        return ProductionFloorStage::fromJobStatus($jobCard->status);
    }

    /**
     * @return array<string, mixed>
     */
    protected function presentRow(ProductionJobCard $jobCard): array
    {
        $stage = $this->resolveStage($jobCard);

        return [
            'id' => $jobCard->id,
            'public_id' => $jobCard->public_id,
            'job_number' => $jobCard->job_card_number,
            'customer' => $jobCard->customer?->company_name,
            'product' => $jobCard->inventoryItem?->item_name,
            'sku' => $jobCard->inventoryItem?->sku,
            'stage' => $stage->value,
            'stage_label' => $stage->label(),
            'status' => $jobCard->status->value,
            'status_label' => $jobCard->status->label(),
            'priority' => $jobCard->priority->value,
            'priority_label' => ucfirst($jobCard->priority->value),
            'required_date' => $jobCard->required_date?->toDateString()
                ?? $jobCard->salesOrder?->required_date?->toDateString(),
            'is_overdue' => $jobCard->isDelayed(),
            'machine_id' => $jobCard->assigned_machine_asset_id,
            'machine' => $jobCard->assignedMachine?->asset_name,
            'vendor' => $jobCard->outsourceVendor?->vendor_name,
            'vendor_expected_return' => $jobCard->outsource_expected_return?->toDateString(),
            'vendor_quoted_cost' => $jobCard->outsource_quoted_cost !== null ? (float) $jobCard->outsource_quoted_cost : null,
            'work_center' => $jobCard->queues->first()?->workCenter?->name,
            'primary_action' => $this->actions->primaryAction($jobCard),
            'panel_url' => route('admin.production.floor.panel', $jobCard),
            'job_url' => route('admin.production.job-cards.show', $jobCard),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function summaryStrip(): array
    {
        $dashboard = $this->commandCenter->build();
        $snapshot = collect($dashboard['snapshot'] ?? [])->keyBy('key');

        return [
            [
                'key' => 'open',
                'label' => __('Open'),
                'value' => $snapshot->get('open')['value'] ?? '0',
                'filter' => ['stage' => ProductionFloorStage::Waiting->value],
            ],
            [
                'key' => 'in_production',
                'label' => __('On press'),
                'value' => $snapshot->get('in_production')['value'] ?? '0',
                'filter' => ['stage' => ProductionFloorStage::OnPress->value],
            ],
            [
                'key' => 'at_vendor',
                'label' => __('At vendor'),
                'value' => (string) ProductionJobCard::query()->forTenant()->where('status', ProductionJobCardStatus::Outsourced)->count(),
                'filter' => ['stage' => ProductionFloorStage::AtVendor->value],
            ],
            [
                'key' => 'awaiting_qc',
                'label' => __('QC'),
                'value' => $snapshot->get('awaiting_qc')['value'] ?? '0',
                'filter' => ['stage' => ProductionFloorStage::Qc->value],
            ],
            [
                'key' => 'ready_for_dispatch',
                'label' => __('Ready'),
                'value' => $snapshot->get('ready_for_dispatch')['value'] ?? '0',
                'filter' => ['stage' => ProductionFloorStage::Ready->value],
            ],
            [
                'key' => 'delayed',
                'label' => __('Overdue'),
                'value' => $snapshot->get('delayed')['value'] ?? '0',
                'filter' => ['overdue' => '1'],
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, int>
     */
    protected function stageCounts(array $filters): array
    {
        unset($filters['stage']);

        $counts = [];
        foreach (ProductionFloorStage::activeStages() as $stage) {
            $counts[$stage->value] = $this->baseQuery([...$filters, 'stage' => $stage->value])->count();
        }

        return $counts;
    }

    /**
     * @return array<string, mixed>
     */
    protected function normalizeFilters(Request $request): array
    {
        return [
            'search' => trim((string) $request->input('search', '')),
            'stage' => (string) $request->input('stage', ''),
            'machine_id' => (string) $request->input('machine_id', ''),
            'vendor_id' => (string) $request->input('vendor_id', ''),
            'priority' => (string) $request->input('priority', ''),
            'overdue' => $request->input('overdue') === '1' ? '1' : '',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function filterOptions(): array
    {
        return [
            'stages' => collect(ProductionFloorStage::activeStages())
                ->map(fn (ProductionFloorStage $stage) => ['value' => $stage->value, 'label' => $stage->label()])
                ->all(),
            'priorities' => collect(ProductionPriority::cases())
                ->map(fn (ProductionPriority $priority) => ['value' => $priority->value, 'label' => ucfirst($priority->value)])
                ->all(),
            'machines' => FixedAsset::query()
                ->forTenant()
                ->whereHas('machineProfile')
                ->orderBy('asset_name')
                ->get(['id', 'asset_name'])
                ->map(fn ($m) => ['value' => (string) $m->id, 'label' => $m->asset_name])
                ->all(),
            'vendors' => Vendor::query()
                ->forTenant()
                ->where('is_production_vendor', true)
                ->orderBy('vendor_name')
                ->get(['id', 'vendor_name'])
                ->map(fn ($v) => ['value' => (string) $v->id, 'label' => $v->vendor_name])
                ->all(),
            'work_centers' => WorkCenter::query()
                ->forTenant()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn ($w) => ['value' => (string) $w->id, 'label' => $w->name])
                ->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function outsourcePanelData(ProductionJobCard $jobCard): array
    {
        $exposure = $this->outsource->costExposure($jobCard);

        return [
            'vendor' => $jobCard->outsourceVendor ? [
                'id' => $jobCard->outsourceVendor->id,
                'vendor_name' => $jobCard->outsourceVendor->vendor_name,
            ] : null,
            'issue_date' => $jobCard->outsource_issue_date?->toDateString(),
            'expected_return' => $jobCard->outsource_expected_return?->toDateString(),
            'quoted_cost' => $jobCard->outsource_quoted_cost !== null ? (float) $jobCard->outsource_quoted_cost : null,
            'actual_cost' => $jobCard->outsource_actual_cost !== null ? (float) $jobCard->outsource_actual_cost : null,
            'notes' => $jobCard->outsource_notes,
            'cost_exposure' => $exposure,
            'can_outsource' => auth()->user()?->can('update', $jobCard)
                && $jobCard->status->canTransitionTo(ProductionJobCardStatus::Outsourced),
            'can_return' => auth()->user()?->can('update', $jobCard)
                && $jobCard->status === ProductionJobCardStatus::Outsourced,
            'outsource_url' => route('admin.production.job-cards.outsource', $jobCard),
            'return_url' => route('admin.production.job-cards.outsource.return', $jobCard),
            'production_vendors' => Vendor::query()
                ->forTenant()
                ->where('is_production_vendor', true)
                ->orderBy('vendor_name')
                ->get(['id', 'vendor_name'])
                ->map(fn ($v) => ['id' => $v->id, 'vendor_name' => $v->vendor_name])
                ->values()
                ->all(),
        ];
    }

    /**
     * @return Collection<int, FixedAsset>
     */
    protected function machinesForPanel(): Collection
    {
        return FixedAsset::query()
            ->forTenant()
            ->whereHas('machineProfile')
            ->orderBy('asset_name')
            ->get(['id', 'asset_name']);
    }
}
