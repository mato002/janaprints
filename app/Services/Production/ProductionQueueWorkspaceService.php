<?php

namespace App\Services\Production;

use App\Enums\ProductionJobCardStatus;
use App\Enums\ProductionPriority;
use App\Enums\ProductionQueueStatus;
use App\Enums\QualityCheckResult;
use App\Models\Assets\FixedAsset;
use App\Models\Production\ProductionJobCard;
use App\Models\Production\ProductionQueue;
use App\Models\User;
use App\Support\Production\DepartmentQueueRegistry;
use App\Support\Production\DepartmentQueueRoutingService;
use App\Support\Production\ProductionQueueOrderingService;
use App\Support\Sales\SalesOrderFinancialStatusService;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;

class ProductionQueueWorkspaceService
{
    private const STATUS_COLUMN = 'production_queues.status';

    public function __construct(
        protected ProductionQueueOrderingService $ordering,
        protected DepartmentQueueRegistry $departments,
        protected DepartmentQueueRoutingService $routing,
        protected ProductionFloorActionService $floorActions,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function buildIndex(Request $request, ?string $department = null): array
    {
        return [
            'queues' => $this->paginatedIndex($request, $department),
            'kpis' => $this->kpiCounts($request, $department),
            'metrics' => $this->liveMetrics($request, $department),
            'department_nav' => $this->departments->navigation($department),
            'active_department' => $department,
            'active_department_label' => $department
                ? ($this->departments->department($department)['label'] ?? ucfirst($department))
                : null,
            'workCenters' => \App\Models\Production\WorkCenter::query()->forTenant()->orderBy('name')->get(['id', 'name', 'code']),
            'stages' => \App\Models\Production\ProductionStage::query()->forTenant()->where('is_active', true)->orderBy('sort_order')->get(['id', 'name']),
            'operators' => $this->operatorOptions(),
            'machines' => $this->machineOptions(),
            'customers' => $this->customerOptions(),
            'filters' => $this->extractFilters($request),
            'workspace' => $this,
        ];
    }

    /**
     * @return array<string, int>
     */
    public function kpiCounts(?Request $request = null, ?string $department = null): array
    {
        $base = $this->scopedBaseQuery($request, $department);

        return [
            'waiting' => (clone $base)->where(self::STATUS_COLUMN, ProductionQueueStatus::Waiting)->count(),
            'queued' => (clone $base)->where(self::STATUS_COLUMN, ProductionQueueStatus::Queued)->count(),
            'assigned' => (clone $base)->where(self::STATUS_COLUMN, ProductionQueueStatus::Assigned)->count(),
            'in_progress' => (clone $base)->where(self::STATUS_COLUMN, ProductionQueueStatus::InProgress)->count(),
            'paused' => (clone $base)->where(self::STATUS_COLUMN, ProductionQueueStatus::Paused)->count(),
            'blocked' => (clone $base)
                ->where(self::STATUS_COLUMN, ProductionQueueStatus::Waiting)
                ->whereNull('assigned_operator_id')
                ->count(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function liveMetrics(?Request $request = null, ?string $department = null): array
    {
        $base = $this->scopedBaseQuery($request, $department);
        $today = now()->startOfDay();

        $overdue = (clone $base)->whereHas('jobCard', function (Builder $q) {
            $q->whereNotNull('required_date')
                ->whereDate('required_date', '<', now()->toDateString())
                ->whereNotIn('status', [
                    ProductionJobCardStatus::Completed,
                    ProductionJobCardStatus::ReadyForDispatch,
                    ProductionJobCardStatus::Cancelled,
                ]);
        })->count();

        $completedToday = (clone $base)
            ->where(self::STATUS_COLUMN, ProductionQueueStatus::Completed)
            ->whereDate('updated_at', $today)
            ->count();

        $activeEntries = (clone $base)
            ->whereIn(self::STATUS_COLUMN, ProductionQueueStatus::activeStatuses())
            ->get(['created_at']);

        $avgAgeHours = $activeEntries->isEmpty()
            ? null
            : round($activeEntries->avg(fn (ProductionQueue $queue) => $queue->created_at?->diffInHours(now()) ?? 0), 1);

        $operatorWorkload = (clone $base)
            ->whereIn(self::STATUS_COLUMN, [ProductionQueueStatus::Assigned, ProductionQueueStatus::InProgress])
            ->whereNotNull('assigned_operator_id')
            ->selectRaw('assigned_operator_id, count(*) as workload')
            ->groupBy('assigned_operator_id')
            ->orderByDesc('workload')
            ->limit(5)
            ->get()
            ->map(fn ($row) => [
                'operator_id' => $row->assigned_operator_id,
                'operator_name' => User::query()->find($row->assigned_operator_id)?->name,
                'workload' => (int) $row->workload,
            ])
            ->values()
            ->all();

        return [
            'jobs_waiting' => (clone $base)->whereIn(self::STATUS_COLUMN, [
                ProductionQueueStatus::Waiting,
                ProductionQueueStatus::Queued,
            ])->count(),
            'jobs_running' => (clone $base)->where(self::STATUS_COLUMN, ProductionQueueStatus::InProgress)->count(),
            'jobs_paused' => (clone $base)->where(self::STATUS_COLUMN, ProductionQueueStatus::Paused)->count(),
            'jobs_overdue' => $overdue,
            'jobs_completed_today' => $completedToday,
            'average_queue_age_hours' => $avgAgeHours,
            'operator_workload' => $operatorWorkload,
        ];
    }

    public function paginatedIndex(Request $request, ?string $department = null): LengthAwarePaginator
    {
        $query = $this->filteredQuery($request, $department);

        return $this->ordering
            ->applyPriorityOrdering($query)
            ->with([
                'jobCard:id,public_id,company_id,branch_id,job_card_number,customer_id,status,planned_end_date,required_date,priority,created_at,sales_order_id,inventory_item_id,production_type,assigned_machine_asset_id,artwork_request_id,estimated_duration_minutes,outsource_vendor_id,outsource_issue_date,outsource_expected_return,outsource_quoted_cost,outsource_actual_cost,outsource_notes,outsourced_at,returned_at,actual_end_date,updated_at',
                'jobCard.customer:id,public_id,company_name',
                'jobCard.salesOrder:id,public_id,order_number,status,required_date,total_amount',
                'jobCard.salesOrder.items:id,sales_order_id,item_name,quantity,unit_price,line_total',
                'jobCard.outsourceVendor:id,vendor_name',
                'jobCard.productionSpecification:id,production_job_card_id,product_description,size,finished_size,sheet_size,quantity,unit,ups,estimated_sheets,paper_inventory_item_id,material_inventory_item_id,colour_mode,ink_type,binding_type,lamination,finishing_type,production_type,print_product_template_id,numbering_required,spot_uv,foiling,embossing,die_cutting,eyelets',
                'jobCard.serialAllocation:id,production_job_card_id,serial_prefix,serial_padding_length,serial_start,serial_end',
                'jobCard.productionSpecification.paperInventoryItem:id,item_name',
                'jobCard.productionSpecification.materialInventoryItem:id,item_name',
                'jobCard.productionSpecification.printProductTemplate.preferredWorkCenter:id,name,code',
                'jobCard.inventoryItem:id,item_name',
                'jobCard.assignedMachine:id,asset_name,asset_number',
                'jobCard.qualityChecks:id,production_job_card_id,result,requires_customer_approval,customer_approved_at',
                'workCenter:id,public_id,name,code',
                'routeStep:id,step_name,sequence',
                'assignedOperator:id,name',
            ])
            ->paginate(25)
            ->withQueryString();
    }

    /**
     * @return array<string, mixed>
     */
    public function presentRow(ProductionQueue $queue, ?User $user = null): array
    {
        $user ??= auth()->user();
        $jobCard = $queue->jobCard;
        $spec = $jobCard?->productionSpecification;
        $routing = $jobCard ? $this->routing->resolveForJobCard($jobCard) : null;
        $dueDate = $jobCard?->required_date ?? $jobCard?->planned_end_date;
        $daysRemaining = $dueDate ? now()->startOfDay()->diffInDays($dueDate->copy()->startOfDay(), false) : null;
        $operational = $this->operationalStatus($queue, $jobCard);
        $alerts = $this->visualAlerts($queue, $jobCard);
        $payment = $jobCard?->salesOrder
            ? app(SalesOrderFinancialStatusService::class)->snapshot($jobCard->salesOrder)
            : null;

        $job360Url = ($user?->can('production.view') && $jobCard && Route::has('admin.production.job-cards.show'))
            ? route('admin.production.job-cards.show', $jobCard)
            : null;

        return [
            'id' => $queue->id,
            'queue_position' => $queue->queue_position,
            'priority' => $jobCard?->priority?->value,
            'priority_label' => $jobCard?->priority?->value ? str_replace('_', ' ', $jobCard->priority->value) : '—',
            'status' => $queue->status,
            'status_label' => $this->statusLabel($queue->status),
            'operational_status' => $operational['label'],
            'operational_variant' => $operational['variant'],
            'job_card_number' => $jobCard?->job_card_number ?? '—',
            'job_card_id' => $jobCard?->id,
            'customer_name' => $jobCard?->customer?->company_name ?? '—',
            'customer_id' => $jobCard?->customer_id,
            'product' => $spec?->product_description ?? $jobCard?->inventoryItem?->item_name ?? '—',
            'description' => $spec?->product_description ?? $jobCard?->inventoryItem?->item_name,
            'quantity' => $spec?->quantity,
            'unit' => $spec?->unit,
            'finished_size' => $spec?->finished_size ?? $spec?->size,
            'paper_material' => $spec?->paperInventoryItem?->item_name
                ?? $spec?->materialInventoryItem?->item_name,
            'colour_mode' => $spec?->colour_mode,
            'binding' => $spec?->binding_type,
            'finishing' => $this->finishingSummary($spec),
            'due_date' => $dueDate?->format('Y-m-d'),
            'days_remaining' => $daysRemaining,
            'operator_name' => $queue->assignedOperator?->name ?? '—',
            'machine_name' => $jobCard?->assignedMachine?->asset_name ?? '—',
            'department' => $routing['department_label'] ?? $queue->workCenter?->name ?? '—',
            'payment_status' => $payment['financial_status_label'] ?? null,
            'estimated_duration_minutes' => $jobCard?->estimated_duration_minutes,
            'work_center_name' => $queue->workCenter?->name ?? '—',
            'is_delayed' => $jobCard?->isDelayed() ?? false,
            'waiting_hours' => $queue->created_at ? (int) $queue->created_at->diffInHours(now()) : null,
            'waiting_label' => $this->waitingLabel($queue),
            'progress_percent' => $this->progressPercent($queue, $jobCard),
            'badges' => $this->statusBadges($queue, $jobCard, $alerts),
            'alerts' => $alerts,
            'row_tone' => $this->rowTone($alerts),
            'spec_summary' => $spec ? [
                'product' => $spec->product_description ?? $jobCard?->inventoryItem?->item_name,
                'size' => $spec->size,
                'paper' => $spec->paperInventoryItem?->item_name,
                'ups' => $spec->ups,
                'estimated_sheets' => $spec->estimated_sheets,
            ] : null,
            'job_360_url' => $job360Url,
            'work_center_url' => ($user?->can('production.work-centers.view') && $queue->workCenter)
                ? route('admin.production.work-centers.show', $queue->workCenter)
                : null,
            'quick_actions' => $jobCard ? $this->quickActions($jobCard, $user) : [],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function quickActions(ProductionJobCard $jobCard, ?User $user = null): array
    {
        $user ??= auth()->user();
        $actions = [];

        if ($user?->can('view', $jobCard)) {
            $actions[] = ['label' => __('Open Job 360'), 'url' => route('admin.production.job-cards.show', $jobCard), 'type' => 'link'];
        }

        $primary = $this->floorActions->primaryAction($jobCard, $user);
        if ($primary) {
            $actions[] = [
                'label' => $primary['label'],
                'url' => $primary['url'],
                'type' => $primary['type'] ?? 'link',
                'method' => $primary['method'] ?? 'get',
                'variant' => $primary['variant'] ?? 'primary',
            ];
        }

        foreach ($this->floorActions->secondaryActions($jobCard, $user) as $secondary) {
            $actions[] = [
                'label' => $secondary['label'],
                'url' => $secondary['url'],
                'type' => $secondary['type'] ?? 'post',
                'method' => 'post',
                'variant' => $secondary['variant'] ?? 'ghost',
            ];
        }

        if ($user?->can('view', $jobCard) && $jobCard->artwork_request_id && $user->can('artwork.view')) {
            $jobCard->loadMissing('artworkRequest:id,public_id');
            if ($jobCard->artworkRequest) {
                $actions[] = ['label' => __('View artwork'), 'url' => route('admin.artwork.show', $jobCard->artworkRequest), 'type' => 'link'];
            }
        }

        if ($jobCard->customer_id && $user?->can('view', $jobCard->customer)) {
            $actions[] = ['label' => __('View customer'), 'url' => route('admin.crm.customers.show', $jobCard->customer), 'type' => 'link'];
        }

        if ($jobCard->sales_order_id && $user?->can('view', $jobCard->salesOrder)) {
            $actions[] = ['label' => __('View sales order'), 'url' => route('admin.sales-orders.show', $jobCard->salesOrder), 'type' => 'link'];
        }

        if ($user?->can('view', $jobCard) && $jobCard->productionSpecification) {
            $actions[] = [
                'label' => __('View specification'),
                'url' => route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => 'manufacturing']),
                'type' => 'link',
            ];
        }

        if ($user?->can('view', $jobCard) && Route::has('admin.production.job-cards.floor-display')) {
            $actions[] = [
                'label' => __('Print job card'),
                'url' => route('admin.production.job-cards.floor-display', $jobCard),
                'type' => 'link',
            ];
        }

        return $actions;
    }

    public function statusLabel(ProductionQueueStatus $status): string
    {
        return match ($status) {
            ProductionQueueStatus::Waiting => __('Waiting'),
            ProductionQueueStatus::Queued => __('Queued'),
            ProductionQueueStatus::Assigned => __('Assigned'),
            ProductionQueueStatus::InProgress => __('In progress'),
            ProductionQueueStatus::Paused => __('Paused'),
            ProductionQueueStatus::Completed => __('Completed'),
            ProductionQueueStatus::Cancelled => __('Cancelled'),
        };
    }

    /**
     * @return array{label: string, variant: string}
     */
    public function operationalStatus(ProductionQueue $queue, ?ProductionJobCard $jobCard): array
    {
        if (! $jobCard) {
            return ['label' => $this->statusLabel($queue->status), 'variant' => 'neutral'];
        }

        return match ($jobCard->status) {
            ProductionJobCardStatus::Outsourced => ['label' => __('Outsourced'), 'variant' => 'warning'],
            ProductionJobCardStatus::QualityCheck => ['label' => __('Awaiting QC'), 'variant' => 'warning'],
            ProductionJobCardStatus::Rework => ['label' => __('Rework'), 'variant' => 'danger'],
            ProductionJobCardStatus::ReadyForDispatch => ['label' => __('Dispatch ready'), 'variant' => 'success'],
            ProductionJobCardStatus::Completed => ['label' => __('Completed'), 'variant' => 'success'],
            ProductionJobCardStatus::AwaitingCustomerApproval => ['label' => __('Customer approval pending'), 'variant' => 'warning'],
            ProductionJobCardStatus::OnHold => ['label' => __('Paused'), 'variant' => 'neutral'],
            ProductionJobCardStatus::InProduction => match ($queue->status) {
                ProductionQueueStatus::Paused => ['label' => __('Paused'), 'variant' => 'neutral'],
                ProductionQueueStatus::InProgress => ['label' => __('Printing'), 'variant' => 'primary'],
                default => ['label' => __('Printing'), 'variant' => 'primary'],
            },
            default => match ($queue->status) {
                ProductionQueueStatus::Waiting => ['label' => __('Waiting'), 'variant' => 'neutral'],
                ProductionQueueStatus::Queued => ['label' => __('Queued'), 'variant' => 'neutral'],
                ProductionQueueStatus::Assigned => ['label' => __('Assigned'), 'variant' => 'info'],
                ProductionQueueStatus::InProgress => ['label' => __('In progress'), 'variant' => 'primary'],
                ProductionQueueStatus::Paused => ['label' => __('Paused'), 'variant' => 'neutral'],
                ProductionQueueStatus::Completed => ['label' => __('Completed'), 'variant' => 'success'],
                default => ['label' => $this->statusLabel($queue->status), 'variant' => 'neutral'],
            },
        };
    }

    /**
     * @return list<array{code: string, label: string}>
     */
    public function visualAlerts(ProductionQueue $queue, ?ProductionJobCard $jobCard): array
    {
        if (! $jobCard) {
            return [];
        }

        $alerts = [];
        $dueDate = $jobCard->required_date ?? $jobCard->planned_end_date;
        $waitingDays = (int) config('production.queue_waiting_alert_days', 3);

        if ($jobCard->isDelayed()) {
            $alerts[] = ['code' => 'overdue', 'label' => __('Overdue')];
        } elseif ($dueDate?->isToday()) {
            $alerts[] = ['code' => 'due_today', 'label' => __('Due today')];
        }

        if ($queue->created_at && $queue->created_at->lt(now()->subDays($waitingDays))
            && in_array($queue->status, [ProductionQueueStatus::Waiting, ProductionQueueStatus::Queued], true)) {
            $alerts[] = ['code' => 'waiting_long', 'label' => __('Waiting > :days days', ['days' => $waitingDays])];
        }

        if ($jobCard->relationLoaded('qualityChecks')
            && $jobCard->qualityChecks->contains(fn ($check) => in_array($check->result, [
                QualityCheckResult::Failed,
                QualityCheckResult::ReworkRequired,
            ], true))) {
            $alerts[] = ['code' => 'qc_blocked', 'label' => __('QC blocked')];
        }

        if (! $jobCard->artwork_request_id) {
            $alerts[] = ['code' => 'artwork_missing', 'label' => __('Artwork missing')];
        }

        if ($jobCard->status === ProductionJobCardStatus::AwaitingCustomerApproval
            || $jobCard->qualityChecks?->contains(fn ($check) => $check->requires_customer_approval && $check->customer_approved_at === null)) {
            $alerts[] = ['code' => 'customer_approval', 'label' => __('Customer approval pending')];
        }

        if ($jobCard->status === ProductionJobCardStatus::ReadyForDispatch) {
            $alerts[] = ['code' => 'ready', 'label' => __('Ready')];
        }

        if ($queue->status === ProductionQueueStatus::Waiting && ! $queue->assigned_operator_id) {
            $alerts[] = ['code' => 'blocked', 'label' => __('Blocked')];
        }

        return $alerts;
    }

    protected function waitingLabel(ProductionQueue $queue): string
    {
        if (! $queue->created_at) {
            return '—';
        }

        $hours = (int) $queue->created_at->diffInHours(now());

        if ($hours < 24) {
            return __(':hours h', ['hours' => $hours]);
        }

        return __(':days d', ['days' => (int) floor($hours / 24)]);
    }

    protected function progressPercent(ProductionQueue $queue, ?ProductionJobCard $jobCard): int
    {
        if (! $jobCard) {
            return 0;
        }

        return match ($jobCard->status) {
            ProductionJobCardStatus::Draft => 5,
            ProductionJobCardStatus::Queued => 15,
            ProductionJobCardStatus::InProduction => $queue->status === ProductionQueueStatus::InProgress ? 55 : 40,
            ProductionJobCardStatus::OnHold => 35,
            ProductionJobCardStatus::Outsourced => 45,
            ProductionJobCardStatus::Returned => 50,
            ProductionJobCardStatus::QualityCheck => 75,
            ProductionJobCardStatus::Rework => 60,
            ProductionJobCardStatus::ReadyForDispatch, ProductionJobCardStatus::Completed => 100,
            default => 20,
        };
    }

    /**
     * @param  list<array{code: string, label: string}>  $alerts
     * @return list<array{code: string, label: string, variant: string}>
     */
    protected function statusBadges(ProductionQueue $queue, ?ProductionJobCard $jobCard, array $alerts): array
    {
        $badges = [];

        foreach ($alerts as $alert) {
            $variant = match ($alert['code']) {
                'overdue', 'qc_blocked', 'blocked' => 'danger',
                'ready' => 'success',
                'due_today', 'waiting_long', 'customer_approval', 'artwork_missing' => 'warning',
                default => 'neutral',
            };
            $badges[] = ['code' => $alert['code'], 'label' => $alert['label'], 'variant' => $variant];
        }

        if ($jobCard?->status === ProductionJobCardStatus::OnHold || $queue->status === ProductionQueueStatus::Paused) {
            $badges[] = ['code' => 'paused', 'label' => __('Paused'), 'variant' => 'neutral'];
        }

        return $badges;
    }

    protected function rowTone(array $alerts): string
    {
        $codes = collect($alerts)->pluck('code');

        if ($codes->contains('overdue') || $codes->contains('qc_blocked')) {
            return 'danger';
        }

        if ($codes->contains('due_today') || $codes->contains('customer_approval') || $codes->contains('artwork_missing')) {
            return 'warning';
        }

        return 'default';
    }

    protected function finishingSummary($spec): ?string
    {
        if (! $spec) {
            return null;
        }

        $parts = array_filter([
            $spec->finishing_type,
            $spec->binding_type,
            $spec->lamination ? __('Lamination') : null,
        ]);

        return $parts !== [] ? implode(', ', $parts) : null;
    }

    protected function scopedBaseQuery(?Request $request, ?string $department): Builder
    {
        $query = ProductionQueue::query()->forTenant();

        if ($department) {
            $this->departments->applyDepartmentScope($query, $department);
        }

        if ($request) {
            $query = $this->applySharedFilters($query, $request, false);
        }

        return $query;
    }

    public function filteredQueryForExport(Request $request, ?string $department = null): \Illuminate\Database\Eloquent\Builder
    {
        $query = $this->filteredQuery($request, $department);

        return $this->ordering->applyPriorityOrdering($query)->with([
            'jobCard.customer',
            'jobCard.salesOrder.items',
            'jobCard.outsourceVendor',
            'jobCard.productionSpecification.paperInventoryItem',
            'jobCard.productionSpecification.materialInventoryItem',
            'jobCard.productionSpecification.printProductTemplate',
            'jobCard.assignedMachine',
            'jobCard.qualityChecks',
            'jobCard.deliveryNotes',
            'jobCard.costSheet',
            'assignedOperator',
            'workCenter',
        ]);
    }

    protected function filteredQuery(Request $request, ?string $department = null): Builder
    {
        $query = ProductionQueue::query()->forTenant();

        if ($department) {
            $this->departments->applyDepartmentScope($query, $department);
        }

        return $this->applySharedFilters($query, $request, true, $department);
    }

    protected function applySharedFilters(Builder $query, Request $request, bool $includeDepartmentPresets, ?string $department = null): Builder
    {
        if ($status = ProductionQueueStatus::tryFromFilter($request->query('status'))) {
            $query->where(self::STATUS_COLUMN, $status);
        } elseif (filled($request->query('status')) && $request->query('status') !== 'blocked') {
            $raw = ProductionQueueStatus::tryFrom((string) $request->query('status'));
            if ($raw) {
                $query->where(self::STATUS_COLUMN, $raw);
            }
        }

        if ($request->query('status') === 'blocked') {
            $query->where(self::STATUS_COLUMN, ProductionQueueStatus::Waiting)
                ->whereNull('assigned_operator_id');
        }

        if ($workCenterId = $request->query('work_center_id')) {
            $query->where('work_center_id', (int) $workCenterId);
        }

        if ($request->query('operator_id') === 'unassigned') {
            $query->whereNull('assigned_operator_id');
        } elseif ($operatorId = $request->integer('operator_id')) {
            $query->where('assigned_operator_id', $operatorId);
        }

        if ($machineId = $request->integer('machine_id')) {
            $query->whereHas('jobCard', fn (Builder $q) => $q->where('assigned_machine_asset_id', $machineId));
        }

        if ($customerId = $request->integer('customer_id')) {
            $query->whereHas('jobCard', fn (Builder $q) => $q->where('customer_id', $customerId));
        }

        if ($priority = ProductionPriority::tryFrom((string) $request->query('priority'))) {
            $query->whereHas('jobCard', fn (Builder $q) => $q->where('priority', $priority));
        }

        if ($stageId = $request->query('stage_id')) {
            $query->whereHas('jobCard.operations', fn (Builder $q) => $q->where('production_stage_id', (int) $stageId));
        }

        if ($date = $request->query('date')) {
            $query->whereDate('updated_at', $date);
        }

        if ($fromDate = $request->query('from_date')) {
            $query->whereDate('production_queues.created_at', '>=', $fromDate);
        }

        if ($toDate = $request->query('to_date')) {
            $query->whereDate('production_queues.created_at', '<=', $toDate);
        }

        if (
            $includeDepartmentPresets
            && in_array($department, ['digital', 'offset'], true)
            && ! $request->boolean('all_dates')
            && ! $request->filled('from_date')
            && ! $request->filled('to_date')
            && ! $request->filled('due')
            && ! $request->filled('search')
            && ! $request->filled('status')
        ) {
            $today = today()->toDateString();
            $query->whereDate('production_queues.created_at', '>=', $today)
                ->whereDate('production_queues.created_at', '<=', $today);
        }

        if ($vendorId = $request->integer('vendor_id')) {
            $query->whereHas('jobCard', fn (Builder $q) => $q->where('outsource_vendor_id', $vendorId));
        }

        if ($includeDepartmentPresets && $due = $request->query('due')) {
            $query->whereHas('jobCard', function (Builder $q) use ($due) {
                $column = 'required_date';
                match ($due) {
                    'today' => $q->whereDate($column, today()),
                    'tomorrow' => $q->whereDate($column, today()->addDay()),
                    'week' => $q->whereBetween($column, [today(), today()->addDays(7)]),
                    'month' => $q->whereBetween($column, [today()->startOfMonth(), today()->endOfMonth()]),
                    'overdue' => $q->whereDate($column, '<', today())
                        ->whereNotIn('status', [
                            ProductionJobCardStatus::Completed,
                            ProductionJobCardStatus::ReadyForDispatch,
                            ProductionJobCardStatus::Cancelled,
                        ]),
                    default => null,
                };
            });
        }

        if ($search = trim((string) $request->query('search'))) {
            $like = '%'.addcslashes($search, '%_\\').'%';
            $query->whereHas('jobCard', function (Builder $q) use ($like) {
                $q->where('job_card_number', 'like', $like)
                    ->orWhereHas('customer', fn (Builder $c) => $c->where('company_name', 'like', $like))
                    ->orWhereHas('productionSpecification', fn (Builder $s) => $s->where('product_description', 'like', $like));
            });
        }

        return $query;
    }

    /**
     * @return array<string, mixed>
     */
    protected function extractFilters(Request $request): array
    {
        return [
            'status' => $request->query('status'),
            'work_center_id' => $request->query('work_center_id'),
            'operator_id' => $request->query('operator_id'),
            'machine_id' => $request->query('machine_id'),
            'customer_id' => $request->query('customer_id'),
            'priority' => $request->query('priority'),
            'stage_id' => $request->query('stage_id'),
            'date' => $request->query('date'),
            'from_date' => $request->query('from_date', in_array($request->route('department'), ['digital', 'offset'], true) && ! $request->boolean('all_dates')
                ? today()->toDateString()
                : null),
            'to_date' => $request->query('to_date', in_array($request->route('department'), ['digital', 'offset'], true) && ! $request->boolean('all_dates')
                ? today()->toDateString()
                : null),
            'all_dates' => $request->boolean('all_dates'),
            'due' => $request->query('due'),
            'search' => $request->query('search'),
        ];
    }

    /**
     * @return Collection<int, User>
     */
    public function operatorOptions(): Collection
    {
        $operatorIds = ProductionQueue::query()
            ->forTenant()
            ->whereNotNull('assigned_operator_id')
            ->distinct()
            ->pluck('assigned_operator_id');

        if ($operatorIds->isEmpty()) {
            return collect();
        }

        return User::query()
            ->whereIn('id', $operatorIds)
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    /**
     * @return Collection<int, FixedAsset>
     */
    public function machineOptions(): Collection
    {
        $machineIds = ProductionJobCard::query()
            ->forTenant()
            ->whereNotNull('assigned_machine_asset_id')
            ->distinct()
            ->pluck('assigned_machine_asset_id');

        if ($machineIds->isEmpty()) {
            return collect();
        }

        return FixedAsset::query()
            ->whereIn('id', $machineIds)
            ->orderBy('asset_name')
            ->get(['id', 'asset_name', 'asset_number']);
    }

    /**
     * @return Collection<int, \App\Models\Crm\Customer>
     */
    public function customerOptions(): Collection
    {
        $customerIds = ProductionJobCard::query()
            ->forTenant()
            ->whereHas('queues')
            ->distinct()
            ->pluck('customer_id')
            ->filter();

        if ($customerIds->isEmpty()) {
            return collect();
        }

        return \App\Models\Crm\Customer::query()
            ->whereIn('id', $customerIds)
            ->orderBy('company_name')
            ->limit(100)
            ->get(['id', 'company_name']);
    }
}
