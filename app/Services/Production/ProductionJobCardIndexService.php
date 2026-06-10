<?php

namespace App\Services\Production;

use App\Enums\ArtworkRequestStatus;
use App\Enums\Dispatch\DeliveryNoteStatus;
use App\Enums\ProductionJobCardStatus;
use App\Enums\ProductionPriority;
use App\Enums\QualityCheckResult;
use App\Models\Crm\Customer;
use App\Models\Production\ProductionJobCard;
use App\Models\Production\WorkCenter;
use App\Models\Sales\SalesOrder;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;

class ProductionJobCardIndexService
{
    /**
     * @return array<string, mixed>
     */
    public function build(Request $request, ?User $user = null): array
    {
        $user ??= auth()->user();
        $filters = $this->filtersFromRequest($request);

        return [
            'job_cards' => $this->paginatedIndex($request),
            'filters' => $filters,
            'filter_options' => $this->filterOptions($request),
            'active_filter_chips' => $this->activeFilterChips($request),
            'has_active_filters' => $this->hasActiveFilters($request),
            'status_tabs' => $this->statusTabs($request),
            'saved_view_presets' => $this->savedViewPresets(),
            'register_columns' => $this->registerColumns(),
            'bulk_actions' => $this->bulkActions($user),
            'can_create' => $user?->can('create', ProductionJobCard::class) ?? false,
            'sales_orders_url' => Route::has('admin.sales-orders.dashboard')
                ? route('admin.sales-orders.dashboard')
                : null,
            'create_url' => Route::has('admin.production.job-cards.create')
                ? route('admin.production.job-cards.create')
                : null,
        ];
    }

    public function paginatedIndex(Request $request): LengthAwarePaginator
    {
        return $this->indexQuery($request)->paginate(15)->withQueryString();
    }

    /**
     * @return Collection<int, ProductionJobCard>
     */
    public function exportIndex(Request $request): Collection
    {
        return $this->indexQuery($request)->limit(5000)->get();
    }

    /**
     * @return list<string>
     */
    public function exportHeaders(): array
    {
        return array_column($this->registerColumns(), 'label');
    }

    /**
     * @return list<string|int|float|null>
     */
    public function exportRow(ProductionJobCard $jobCard, ?User $user = null): array
    {
        $presented = $this->presentRow($jobCard, $user);

        return array_map(function (array $column) use ($presented) {
            $key = $column['key'];

            if ($key === 'status') {
                return $presented['badge']['label'] ?? '';
            }

            if ($key === 'priority') {
                return str_replace('_', ' ', ucfirst((string) ($presented['priority'] ?? '')));
            }

            return $presented[$key] ?? '';
        }, $this->registerColumns());
    }

    protected function indexQuery(Request $request): Builder
    {
        $query = ProductionJobCard::query()
            ->forTenant()
            ->with([
                'customer:id,company_name',
                'salesOrder:id,order_number',
                'salesOrder.items:id,sales_order_id,item_name,description,quantity',
                'artworkRequest:id,status',
                'queues' => fn ($q) => $q
                    ->with(['workCenter:id,name', 'assignedOperator:id,name'])
                    ->orderBy('queue_position')
                    ->limit(1),
                'qualityChecks' => fn ($q) => $q->latest('checked_at')->limit(1),
                'deliveryNotes' => fn ($q) => $q->latest('id')->limit(1),
            ])
            ->withCount([
                'qualityChecks as passed_qc_count' => fn (Builder $q) => $q->where('result', QualityCheckResult::Passed),
            ]);

        $this->applyFilters($query, $request);
        $this->applySort($query, $request);

        return $query;
    }

    /**
     * @return array{
     *     customers: Collection<int, Customer>,
     *     sales_orders: Collection<int, SalesOrder>,
     *     work_centers: Collection<int, WorkCenter>,
     *     statuses: list<ProductionJobCardStatus>,
     *     priorities: list<ProductionPriority>
     * }
     */
    public function filterOptions(Request $request): array
    {
        $tenantJobCards = ProductionJobCard::query()->forTenant();

        $customerIds = (clone $tenantJobCards)->distinct()->pluck('customer_id')->filter();
        $salesOrderIds = (clone $tenantJobCards)->distinct()->pluck('sales_order_id')->filter();

        return [
            'customers' => Customer::query()
                ->forTenant()
                ->whereIn('id', $customerIds)
                ->orderBy('company_name')
                ->limit(100)
                ->get(['id', 'company_name']),
            'sales_orders' => SalesOrder::query()
                ->forTenant()
                ->whereIn('id', $salesOrderIds)
                ->orderByDesc('order_date')
                ->limit(100)
                ->get(['id', 'order_number']),
            'work_centers' => WorkCenter::query()
                ->forTenant()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name']),
            'statuses' => ProductionJobCardStatus::cases(),
            'priorities' => ProductionPriority::cases(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function filtersFromRequest(Request $request): array
    {
        return [
            'stage' => $request->query('stage'),
            'status' => $request->query('status'),
            'priority' => $request->query('priority'),
            'customer_id' => $request->integer('customer_id') ?: null,
            'sales_order_id' => $request->integer('sales_order_id') ?: null,
            'work_center_id' => $request->integer('work_center_id') ?: null,
            'date_from' => $request->query('date_from'),
            'date_to' => $request->query('date_to'),
            'due_today' => $request->boolean('due_today'),
            'overdue' => $request->boolean('overdue'),
            'awaiting_qc' => $request->boolean('awaiting_qc'),
            'ready_dispatch' => $request->boolean('ready_dispatch'),
            'search' => trim((string) $request->query('search', '')),
            'sort' => $request->query('sort', 'updated_at'),
            'direction' => $request->query('direction', 'desc'),
        ];
    }

    /**
     * @return list<array{key: string, label: string, url: string, active: bool}>
     */
    public function statusTabs(Request $request): array
    {
        $base = route('admin.production.job-cards.index');
        $filters = $this->filtersFromRequest($request);
        $activeStage = $filters['stage'] ?? null;

        $tabs = [
            ['key' => '', 'label' => __('All')],
            ['key' => 'artwork', 'label' => __('Artwork')],
            ['key' => 'scheduled', 'label' => __('Scheduled')],
            ['key' => 'queued', 'label' => __('Queued')],
            ['key' => 'production', 'label' => __('Production')],
            ['key' => 'qc', 'label' => __('QC')],
            ['key' => 'dispatch', 'label' => __('Dispatch')],
            ['key' => 'delivered', 'label' => __('Delivered')],
        ];

        return collect($tabs)->map(function (array $tab) use ($base, $filters, $activeStage) {
            $query = collect($filters)
                ->except(['stage', 'page'])
                ->filter(fn ($v) => filled($v) && $v !== false)
                ->all();

            if (filled($tab['key'])) {
                $query['stage'] = $tab['key'];
            }

            return [
                'key' => $tab['key'],
                'label' => $tab['label'],
                'url' => $base.(count($query) ? '?'.http_build_query($query) : ''),
                'active' => ($activeStage ?? '') === $tab['key'],
            ];
        })->all();
    }

    /**
     * @return list<array{key: string, label: string, query: array<string, mixed>}>
     */
    public function savedViewPresets(): array
    {
        return [
            ['key' => 'all', 'label' => __('All jobs'), 'query' => []],
            ['key' => 'overdue', 'label' => __('Overdue'), 'query' => ['overdue' => 1]],
            ['key' => 'due_today', 'label' => __('Due today'), 'query' => ['due_today' => 1]],
            ['key' => 'awaiting_qc', 'label' => __('Awaiting QC'), 'query' => ['awaiting_qc' => 1]],
            ['key' => 'ready_dispatch', 'label' => __('Ready dispatch'), 'query' => ['ready_dispatch' => 1]],
        ];
    }

    /**
     * @return list<array{key: string, label: string, default: bool, sortable: bool}>
     */
    public function registerColumns(): array
    {
        return [
            ['key' => 'job_number', 'label' => __('Job Number'), 'default' => true, 'sortable' => true],
            ['key' => 'customer', 'label' => __('Customer'), 'default' => true, 'sortable' => true],
            ['key' => 'order', 'label' => __('Order'), 'default' => true, 'sortable' => false],
            ['key' => 'product', 'label' => __('Product'), 'default' => true, 'sortable' => false],
            ['key' => 'quantity', 'label' => __('Quantity'), 'default' => true, 'sortable' => false],
            ['key' => 'department', 'label' => __('Department'), 'default' => true, 'sortable' => false],
            ['key' => 'current_stage', 'label' => __('Current Stage'), 'default' => true, 'sortable' => false],
            ['key' => 'priority', 'label' => __('Priority'), 'default' => true, 'sortable' => true],
            ['key' => 'due_date', 'label' => __('Due Date'), 'default' => true, 'sortable' => true],
            ['key' => 'assigned_team', 'label' => __('Assigned Team'), 'default' => true, 'sortable' => false],
            ['key' => 'status', 'label' => __('Status'), 'default' => true, 'sortable' => true],
        ];
    }

    /**
     * @return list<array{key: string, label: string, url: string}>
     */
    public function activeFilterChips(Request $request): array
    {
        $filters = $this->filtersFromRequest($request);
        $chips = [];
        $base = route('admin.production.job-cards.index');

        $remove = function (array $except) use ($filters, $base): string {
            $query = collect($filters)
                ->except($except)
                ->filter(fn ($v) => filled($v) && $v !== false)
                ->all();

            return $base.(count($query) ? '?'.http_build_query($query) : '');
        };

        if (filled($filters['stage'])) {
            $chips[] = [
                'key' => 'stage',
                'label' => __('Stage').': '.ucfirst($filters['stage']),
                'url' => $remove(['stage']),
            ];
        }

        if (filled($filters['status'])) {
            $chips[] = [
                'key' => 'status',
                'label' => __('Status').': '.str_replace('_', ' ', $filters['status']),
                'url' => $remove(['status']),
            ];
        }

        if (filled($filters['priority'])) {
            $chips[] = [
                'key' => 'priority',
                'label' => __('Priority').': '.$filters['priority'],
                'url' => $remove(['priority']),
            ];
        }

        if ($filters['customer_id']) {
            $name = Customer::query()->find($filters['customer_id'])?->company_name ?? '#'.$filters['customer_id'];
            $chips[] = ['key' => 'customer_id', 'label' => __('Customer').': '.$name, 'url' => $remove(['customer_id'])];
        }

        if ($filters['sales_order_id']) {
            $number = SalesOrder::query()->find($filters['sales_order_id'])?->order_number ?? '#'.$filters['sales_order_id'];
            $chips[] = ['key' => 'sales_order_id', 'label' => __('Sales order').': '.$number, 'url' => $remove(['sales_order_id'])];
        }

        if ($filters['work_center_id']) {
            $name = WorkCenter::query()->find($filters['work_center_id'])?->name ?? '#'.$filters['work_center_id'];
            $chips[] = ['key' => 'work_center_id', 'label' => __('Department').': '.$name, 'url' => $remove(['work_center_id'])];
        }

        if (filled($filters['date_from']) || filled($filters['date_to'])) {
            $chips[] = [
                'key' => 'date_range',
                'label' => __('Date range').': '.($filters['date_from'] ?? '…').' – '.($filters['date_to'] ?? '…'),
                'url' => $remove(['date_from', 'date_to']),
            ];
        }

        foreach ([
            'due_today' => __('Due today'),
            'overdue' => __('Overdue'),
            'awaiting_qc' => __('Awaiting QC'),
            'ready_dispatch' => __('Ready dispatch'),
        ] as $flag => $label) {
            if ($filters[$flag]) {
                $chips[] = ['key' => $flag, 'label' => $label, 'url' => $remove([$flag])];
            }
        }

        if (filled($filters['search'])) {
            $chips[] = [
                'key' => 'search',
                'label' => __('Search').': '.$filters['search'],
                'url' => $remove(['search']),
            ];
        }

        return $chips;
    }

    public function hasActiveFilters(Request $request): bool
    {
        return count($this->activeFilterChips($request)) > 0;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function bulkActions(?User $user): array
    {
        $actions = [];

        if ($user?->can('production.view')) {
            $actions[] = [
                'key' => 'export',
                'label' => __('Export'),
                'type' => 'client_export',
                'supported' => true,
            ];
        }

        return $actions;
    }

    /**
     * @return array<string, mixed>
     */
    public function presentRow(ProductionJobCard $jobCard, ?User $user = null): array
    {
        $user ??= auth()->user();
        $badge = $this->displayBadge($jobCard);
        $primaryQueue = $jobCard->queues->first();

        return [
            'id' => $jobCard->id,
            'job_number' => $jobCard->job_card_number,
            'customer' => $jobCard->customer?->company_name ?? '—',
            'order' => $jobCard->salesOrder?->order_number ?? '—',
            'product' => $this->productDescription($jobCard),
            'quantity' => $this->totalQuantity($jobCard),
            'department' => $primaryQueue?->workCenter?->name ?? '—',
            'current_stage' => $this->currentStageLabel($jobCard),
            'priority' => $jobCard->priority->value,
            'due_date' => $jobCard->planned_end_date?->format('Y-m-d') ?? '—',
            'due_date_iso' => $jobCard->planned_end_date?->toDateString(),
            'assigned_team' => $primaryQueue?->assignedOperator?->name ?? '—',
            'badge' => $badge,
            'is_delayed' => $jobCard->isDelayed(),
            'job_360_url' => ($user?->can('view', $jobCard) && Route::has('admin.production.job-cards.show'))
                ? route('admin.production.job-cards.show', $jobCard)
                : null,
            'edit_url' => ($user?->can('update', $jobCard) && Route::has('admin.production.job-cards.edit'))
                ? route('admin.production.job-cards.edit', $jobCard)
                : null,
            'workflow_actions' => $this->rowWorkflowActions($jobCard, $user),
        ];
    }

    /**
     * @return array{label: string, variant: string}
     */
    public function displayBadge(ProductionJobCard $jobCard): array
    {
        if ($jobCard->status === ProductionJobCardStatus::Cancelled) {
            return ['label' => __('Cancelled'), 'variant' => 'danger'];
        }

        if ($jobCard->status === ProductionJobCardStatus::OnHold) {
            return ['label' => __('On Hold'), 'variant' => 'warning'];
        }

        $delivered = $jobCard->deliveryNotes->contains(
            fn ($note) => $note->status === DeliveryNoteStatus::Delivered
        );
        if ($delivered) {
            return ['label' => __('Delivered'), 'variant' => 'success'];
        }

        if ($jobCard->isDelayed()) {
            return ['label' => __('Delayed'), 'variant' => 'danger'];
        }

        if ($jobCard->status === ProductionJobCardStatus::ReadyForDispatch) {
            return ['label' => __('Ready Dispatch'), 'variant' => 'success'];
        }

        $latestQc = $jobCard->qualityChecks->first();
        if ($latestQc?->result === QualityCheckResult::Passed && $jobCard->status === ProductionJobCardStatus::Completed) {
            return ['label' => __('QC Passed'), 'variant' => 'success'];
        }

        if ($jobCard->status === ProductionJobCardStatus::QualityCheck) {
            return ['label' => __('Awaiting QC'), 'variant' => 'warning'];
        }

        if (in_array($jobCard->status, [ProductionJobCardStatus::InProduction, ProductionJobCardStatus::Rework], true)) {
            return ['label' => __('In Production'), 'variant' => 'in_production'];
        }

        if ($jobCard->status === ProductionJobCardStatus::Queued) {
            return ['label' => __('Queued'), 'variant' => 'in_production'];
        }

        if ($jobCard->planned_start_date && in_array($jobCard->status, [ProductionJobCardStatus::Draft, ProductionJobCardStatus::Queued], true)) {
            return ['label' => __('Scheduled'), 'variant' => 'info'];
        }

        $artwork = $jobCard->artworkRequest;
        if ($artwork && $artwork->status === ArtworkRequestStatus::Approved) {
            return ['label' => __('Artwork Approved'), 'variant' => 'success'];
        }

        if ($artwork && $artwork->status !== ArtworkRequestStatus::Approved) {
            return ['label' => __('Awaiting Artwork'), 'variant' => 'warning'];
        }

        return [
            'label' => str_replace('_', ' ', $jobCard->status->value),
            'variant' => 'neutral',
        ];
    }

    public function currentStageLabel(ProductionJobCard $jobCard): string
    {
        if ($jobCard->deliveryNotes->contains(fn ($n) => $n->status === DeliveryNoteStatus::Delivered)) {
            return __('Delivered');
        }

        return match ($jobCard->status) {
            ProductionJobCardStatus::ReadyForDispatch, ProductionJobCardStatus::Completed => __('Dispatch'),
            ProductionJobCardStatus::QualityCheck => __('QC'),
            ProductionJobCardStatus::InProduction, ProductionJobCardStatus::Rework => __('Production'),
            ProductionJobCardStatus::Queued => __('Queued'),
            ProductionJobCardStatus::OnHold => __('On Hold'),
            ProductionJobCardStatus::Cancelled => __('Cancelled'),
            default => $jobCard->planned_start_date
                ? __('Scheduled')
                : ($jobCard->artworkRequest?->status === ArtworkRequestStatus::Approved
                    ? __('Artwork')
                    : __('Artwork')),
        };
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function rowWorkflowActions(ProductionJobCard $jobCard, ?User $user): array
    {
        $user ??= auth()->user();
        $actions = [];

        if ($user?->can('schedule', $jobCard) && Route::has('admin.production.job-cards.schedule')) {
            $actions[] = [
                'label' => __('Schedule'),
                'type' => 'link',
                'url' => route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => 'operations']),
            ];
        }

        if ($user?->can('schedule', $jobCard) && Route::has('admin.production.job-cards.queue')) {
            $actions[] = [
                'label' => __('Queue'),
                'type' => 'link',
                'url' => route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => 'operations']),
            ];
        }

        if (
            $user?->can('complete', $jobCard)
            && $jobCard->status === ProductionJobCardStatus::InProduction
            && Route::has('admin.production.job-cards.send-to-qc')
        ) {
            $actions[] = [
                'label' => __('Send To QC'),
                'type' => 'post',
                'url' => route('admin.production.job-cards.send-to-qc', $jobCard),
            ];
        }

        if (
            $user?->can('complete', $jobCard)
            && $jobCard->status === ProductionJobCardStatus::QualityCheck
            && Route::has('admin.production.job-cards.complete')
        ) {
            $actions[] = [
                'label' => __('Mark Complete'),
                'type' => 'post',
                'url' => route('admin.production.job-cards.complete', $jobCard),
            ];
        }

        return $actions;
    }

    protected function productDescription(ProductionJobCard $jobCard): string
    {
        $items = $jobCard->salesOrder?->items ?? collect();
        if ($items->isEmpty()) {
            return str_replace('_', ' ', $jobCard->production_type->value);
        }

        return $items->take(2)->map(fn ($item) => $item->item_name ?: $item->description)->filter()->implode(', ');
    }

    protected function totalQuantity(ProductionJobCard $jobCard): string
    {
        $items = $jobCard->salesOrder?->items ?? collect();
        if ($items->isEmpty()) {
            return '—';
        }

        $sum = $items->sum(fn ($item) => (float) $item->quantity);

        return number_format($sum, 3);
    }

    protected function applyFilters(Builder $query, Request $request): void
    {
        $filters = $this->filtersFromRequest($request);
        $today = now()->toDateString();

        $this->applyStageFilter($query, $filters['stage'] ?? null);

        if (is_string($filters['status']) && ($enum = ProductionJobCardStatus::tryFrom($filters['status']))) {
            $query->where('status', $enum);
        }

        if (is_string($filters['priority']) && ($enum = ProductionPriority::tryFrom($filters['priority']))) {
            $query->where('priority', $enum);
        }

        if ($filters['customer_id']) {
            $query->where('customer_id', $filters['customer_id']);
        }

        if ($filters['sales_order_id']) {
            $query->where('sales_order_id', $filters['sales_order_id']);
        }

        if ($filters['work_center_id']) {
            $query->whereHas('queues', fn (Builder $q) => $q->where('work_center_id', $filters['work_center_id']));
        }

        if (filled($filters['date_from'])) {
            $query->whereDate('planned_end_date', '>=', $filters['date_from']);
        }

        if (filled($filters['date_to'])) {
            $query->whereDate('planned_end_date', '<=', $filters['date_to']);
        }

        if ($filters['due_today']) {
            $query->whereDate('planned_end_date', $today);
        }

        if ($filters['overdue']) {
            $query->whereNotIn('status', [
                ProductionJobCardStatus::Completed,
                ProductionJobCardStatus::ReadyForDispatch,
                ProductionJobCardStatus::Cancelled,
            ])
                ->whereDate('planned_end_date', '<', $today);
        }

        if ($filters['awaiting_qc']) {
            $query->where('status', ProductionJobCardStatus::QualityCheck);
        }

        if ($filters['ready_dispatch']) {
            $query->where('status', ProductionJobCardStatus::ReadyForDispatch);
        }

        if (filled($filters['search'])) {
            $like = '%'.addcslashes($filters['search'], '%_\\').'%';
            $query->where(function (Builder $q) use ($like) {
                $q->where('job_card_number', 'like', $like)
                    ->orWhere('production_type', 'like', $like)
                    ->orWhereHas('customer', fn (Builder $customer) => $customer->where('company_name', 'like', $like))
                    ->orWhereHas('salesOrder', fn (Builder $order) => $order->where('order_number', 'like', $like))
                    ->orWhereHas('salesOrder.items', fn (Builder $items) => $items
                        ->where('item_name', 'like', $like)
                        ->orWhere('description', 'like', $like));
            });
        }
    }

    protected function applyStageFilter(Builder $query, ?string $stage): void
    {
        if (! filled($stage)) {
            return;
        }

        $terminal = [
            ProductionJobCardStatus::Completed,
            ProductionJobCardStatus::ReadyForDispatch,
            ProductionJobCardStatus::Cancelled,
        ];

        match ($stage) {
            'artwork' => $query
                ->whereNotNull('artwork_request_id')
                ->whereHas('artworkRequest', fn (Builder $q) => $q->where('status', '!=', ArtworkRequestStatus::Approved))
                ->whereNotIn('status', $terminal),
            'scheduled' => $query
                ->whereNotNull('planned_start_date')
                ->whereNotIn('status', $terminal),
            'queued' => $query->where('status', ProductionJobCardStatus::Queued),
            'production' => $query->whereIn('status', [
                ProductionJobCardStatus::InProduction,
                ProductionJobCardStatus::Rework,
            ]),
            'qc' => $query->where('status', ProductionJobCardStatus::QualityCheck),
            'dispatch' => $query->whereIn('status', [
                ProductionJobCardStatus::ReadyForDispatch,
                ProductionJobCardStatus::Completed,
            ]),
            'delivered' => $query->whereHas(
                'deliveryNotes',
                fn (Builder $q) => $q->where('status', DeliveryNoteStatus::Delivered)
            ),
            default => null,
        };
    }

    protected function applySort(Builder $query, Request $request): void
    {
        $sort = $request->query('sort', 'updated_at');
        $direction = strtolower((string) $request->query('direction', 'desc')) === 'asc' ? 'asc' : 'desc';

        $column = match ($sort) {
            'job_card_number', 'job_number' => 'job_card_number',
            'due_date' => 'planned_end_date',
            'priority' => 'priority',
            'status' => 'status',
            'customer' => null,
            default => 'updated_at',
        };

        if ($sort === 'customer') {
            $query->orderBy(
                Customer::query()
                    ->select('company_name')
                    ->whereColumn('customers.id', 'production_job_cards.customer_id')
                    ->limit(1),
                $direction
            );

            return;
        }

        $query->orderBy($column ?? 'updated_at', $direction);
    }
}
