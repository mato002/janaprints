<?php

namespace App\Services\Production;

use App\Enums\ArtworkRequestStatus;
use App\Enums\Dispatch\DeliveryNoteStatus;
use App\Enums\ProductionJobCardStatus;
use App\Enums\ProductionPriority;
use App\Enums\QualityCheckResult;
use App\Models\Crm\Customer;
use App\Models\Production\ProductionJobCard;
use App\Models\Production\ProductionQueue;
use App\Models\Production\QualityCheck;
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
    public function __construct(
        protected WorkCenterWorkspaceService $workCenters,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function build(Request $request, ?User $user = null): array
    {
        $user ??= auth()->user();

        return [
            'job_cards' => $this->paginatedIndex($request),
            'filters' => $this->filtersFromRequest($request),
            'filter_options' => $this->filterOptions($request),
            'active_filter_chips' => $this->activeFilterChips($request),
            'has_active_filters' => $this->hasActiveFilters($request),
            'kpis' => $this->kpis($user),
            'pipeline' => $this->pipeline($user),
            'alerts' => $this->alerts($user),
            'workload' => $this->workload(),
            'quick_actions' => $this->quickActions($user),
            'bulk_actions' => $this->bulkActions($user),
        ];
    }

    public function paginatedIndex(Request $request): LengthAwarePaginator
    {
        $query = ProductionJobCard::query()
            ->forTenant()
            ->with([
                'customer:id,company_name',
                'salesOrder:id,order_number',
                'salesOrder.items:id,sales_order_id,item_name,description,quantity',
                'artworkRequest:id,status',
                'queues' => fn ($q) => $q->with('workCenter:id,name')->orderBy('queue_position')->limit(1),
                'qualityChecks' => fn ($q) => $q->latest('checked_at')->limit(1),
                'deliveryNotes' => fn ($q) => $q->latest('id')->limit(1),
            ])
            ->withCount([
                'qualityChecks as passed_qc_count' => fn (Builder $q) => $q->where('result', QualityCheckResult::Passed),
            ]);

        $this->applyFilters($query, $request);
        $this->applySort($query, $request);

        return $query->paginate(15)->withQueryString();
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
            $chips[] = ['key' => 'work_center_id', 'label' => __('Work center').': '.$name, 'url' => $remove(['work_center_id'])];
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
    public function kpis(?User $user): array
    {
        $today = now()->toDateString();
        $terminal = [
            ProductionJobCardStatus::Completed->value,
            ProductionJobCardStatus::ReadyForDispatch->value,
            ProductionJobCardStatus::Cancelled->value,
        ];

        $row = ProductionJobCard::query()
            ->forTenant()
            ->selectRaw("SUM(CASE WHEN status NOT IN (?,?,?) THEN 1 ELSE 0 END) as open_jobs", $terminal)
            ->selectRaw("SUM(CASE WHEN status NOT IN (?,?,?) AND planned_start_date IS NOT NULL THEN 1 ELSE 0 END) as scheduled_jobs", $terminal)
            ->selectRaw("SUM(CASE WHEN status IN ('in_production','rework') THEN 1 ELSE 0 END) as in_production")
            ->selectRaw("SUM(CASE WHEN status = 'quality_check' THEN 1 ELSE 0 END) as awaiting_qc")
            ->selectRaw("SUM(CASE WHEN status = 'ready_for_dispatch' THEN 1 ELSE 0 END) as ready_for_dispatch")
            ->selectRaw('SUM(CASE WHEN status NOT IN (?,?,?) AND planned_end_date IS NOT NULL AND planned_end_date < ? THEN 1 ELSE 0 END) as delayed_jobs', [
                ...$terminal,
                $today,
            ])
            ->selectRaw("SUM(CASE WHEN status = 'completed' AND DATE(updated_at) = ? THEN 1 ELSE 0 END) as completed_today", [$today])
            ->first();

        $definitions = [
            ['key' => 'open', 'label' => __('Total Open Jobs'), 'value' => (int) ($row->open_jobs ?? 0), 'icon' => 'inbox', 'tone' => 'slate', 'route' => 'admin.production.job-cards.index', 'permission' => 'production.view', 'query' => []],
            ['key' => 'scheduled', 'label' => __('Scheduled Jobs'), 'value' => (int) ($row->scheduled_jobs ?? 0), 'icon' => 'calendar', 'tone' => 'indigo', 'route' => 'admin.production.scheduling.index', 'permission' => 'production.scheduling.view', 'query' => []],
            ['key' => 'in_production', 'label' => __('Jobs In Production'), 'value' => (int) ($row->in_production ?? 0), 'icon' => 'cog', 'tone' => 'indigo', 'route' => 'admin.production.job-cards.index', 'permission' => 'production.view', 'query' => ['status' => 'in_production']],
            ['key' => 'awaiting_qc', 'label' => __('Awaiting QC'), 'value' => (int) ($row->awaiting_qc ?? 0), 'icon' => 'clipboard-check', 'tone' => 'amber', 'route' => 'admin.production.job-cards.index', 'permission' => 'production.view', 'query' => ['awaiting_qc' => 1]],
            ['key' => 'ready_for_dispatch', 'label' => __('Ready For Dispatch'), 'value' => (int) ($row->ready_for_dispatch ?? 0), 'icon' => 'truck', 'tone' => 'emerald', 'route' => 'admin.production.job-cards.index', 'permission' => 'production.view', 'query' => ['ready_dispatch' => 1]],
            ['key' => 'delayed', 'label' => __('Delayed Jobs'), 'value' => (int) ($row->delayed_jobs ?? 0), 'icon' => 'exclamation', 'tone' => 'red', 'route' => 'admin.production.job-cards.index', 'permission' => 'production.view', 'query' => ['overdue' => 1]],
            ['key' => 'completed_today', 'label' => __('Completed Today'), 'value' => (int) ($row->completed_today ?? 0), 'icon' => 'check-circle', 'tone' => 'emerald', 'route' => 'admin.production.job-cards.index', 'permission' => 'production.view', 'query' => ['status' => 'completed']],
        ];

        return collect($definitions)
            ->map(fn (array $card) => $this->presentKpiCard($card, $user))
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function pipeline(?User $user): array
    {
        $base = ProductionJobCard::query()->forTenant();
        $today = now()->toDateString();

        $stages = [
            [
                'key' => 'artwork',
                'label' => __('Artwork'),
                'color' => 'amber',
                'count' => (clone $base)
                    ->whereNotNull('artwork_request_id')
                    ->whereHas('artworkRequest', fn (Builder $q) => $q->where('status', '!=', ArtworkRequestStatus::Approved))
                    ->whereNotIn('status', [
                        ProductionJobCardStatus::Completed,
                        ProductionJobCardStatus::ReadyForDispatch,
                        ProductionJobCardStatus::Cancelled,
                    ])
                    ->count(),
                'route' => 'admin.production.job-cards.index',
                'permission' => 'production.view',
                'query' => [],
            ],
            [
                'key' => 'scheduled',
                'label' => __('Scheduled'),
                'color' => 'indigo',
                'count' => (clone $base)
                    ->whereNotIn('status', [
                        ProductionJobCardStatus::Completed,
                        ProductionJobCardStatus::ReadyForDispatch,
                        ProductionJobCardStatus::Cancelled,
                    ])
                    ->whereNotNull('planned_start_date')
                    ->count(),
                'route' => 'admin.production.scheduling.index',
                'permission' => 'production.scheduling.view',
                'query' => [],
            ],
            [
                'key' => 'queued',
                'label' => __('Queued'),
                'color' => 'slate',
                'count' => (clone $base)->where('status', ProductionJobCardStatus::Queued)->count(),
                'route' => 'admin.production.queue.index',
                'permission' => 'production.queue.view',
                'query' => [],
            ],
            [
                'key' => 'production',
                'label' => __('Production'),
                'color' => 'indigo',
                'count' => (clone $base)->whereIn('status', [
                    ProductionJobCardStatus::InProduction,
                    ProductionJobCardStatus::Rework,
                ])->count(),
                'route' => 'admin.production.job-cards.index',
                'permission' => 'production.view',
                'query' => ['status' => 'in_production'],
            ],
            [
                'key' => 'qc',
                'label' => __('QC'),
                'color' => 'amber',
                'count' => (clone $base)->where('status', ProductionJobCardStatus::QualityCheck)->count(),
                'route' => 'admin.production.quality.index',
                'permission' => 'production.quality.view',
                'query' => [],
            ],
            [
                'key' => 'dispatch',
                'label' => __('Dispatch'),
                'color' => 'emerald',
                'count' => (clone $base)->whereIn('status', [
                    ProductionJobCardStatus::ReadyForDispatch,
                    ProductionJobCardStatus::Completed,
                ])->count(),
                'route' => 'admin.production.job-cards.index',
                'permission' => 'production.view',
                'query' => ['ready_dispatch' => 1],
            ],
            [
                'key' => 'delivered',
                'label' => __('Delivered'),
                'color' => 'emerald',
                'count' => (clone $base)
                    ->whereHas('deliveryNotes', fn (Builder $q) => $q->where('status', DeliveryNoteStatus::Delivered))
                    ->count(),
                'route' => 'admin.dispatch.delivery-notes.index',
                'permission' => 'dispatch.view',
                'query' => [],
            ],
        ];

        return collect($stages)->map(function (array $stage) use ($user) {
            $url = ($user?->can($stage['permission']) && Route::has($stage['route']))
                ? route($stage['route'], $stage['query'] ?? [])
                : null;

            return [
                'key' => $stage['key'],
                'label' => $stage['label'],
                'count' => $stage['count'],
                'color' => $stage['color'],
                'url' => $url,
            ];
        })->all();
    }

    /**
     * @return array<string, mixed>
     */
    public function alerts(?User $user): array
    {
        $today = now()->toDateString();

        return [
            'overdue' => $this->alertSection(
                title: __('Overdue Jobs'),
                jobs: $this->alertJobQuery()
                    ->whereNotIn('status', [
                        ProductionJobCardStatus::Completed,
                        ProductionJobCardStatus::ReadyForDispatch,
                        ProductionJobCardStatus::Cancelled,
                    ])
                    ->whereDate('planned_end_date', '<', $today)
                    ->orderBy('planned_end_date')
                    ->limit(5)
                    ->get(),
                viewAllRoute: 'admin.production.job-cards.index',
                viewAllPermission: 'production.view',
                viewAllQuery: ['overdue' => 1],
                user: $user,
            ),
            'awaiting_qc' => $this->alertSection(
                title: __('Awaiting QC'),
                jobs: $this->alertJobQuery()
                    ->where('status', ProductionJobCardStatus::QualityCheck)
                    ->orderBy('planned_end_date')
                    ->limit(5)
                    ->get(),
                viewAllRoute: 'admin.production.job-cards.index',
                viewAllPermission: 'production.view',
                viewAllQuery: ['awaiting_qc' => 1],
                user: $user,
            ),
            'awaiting_artwork' => $this->alertSection(
                title: __('Awaiting Artwork'),
                jobs: $this->alertJobQuery()
                    ->whereNotNull('artwork_request_id')
                    ->whereHas('artworkRequest', fn (Builder $q) => $q->where('status', '!=', ArtworkRequestStatus::Approved))
                    ->whereNotIn('status', [
                        ProductionJobCardStatus::Completed,
                        ProductionJobCardStatus::ReadyForDispatch,
                        ProductionJobCardStatus::Cancelled,
                    ])
                    ->orderBy('planned_end_date')
                    ->limit(5)
                    ->get(),
                viewAllRoute: 'admin.production.job-cards.index',
                viewAllPermission: 'production.view',
                viewAllQuery: [],
                user: $user,
            ),
            'dispatch_due_today' => $this->alertSection(
                title: __('Dispatch Due Today'),
                jobs: $this->alertJobQuery()
                    ->whereIn('status', [
                        ProductionJobCardStatus::ReadyForDispatch,
                        ProductionJobCardStatus::Completed,
                    ])
                    ->where(function (Builder $q) use ($today) {
                        $q->whereDate('planned_end_date', $today)
                            ->orWhereHas('deliveryNotes', fn (Builder $dn) => $dn
                                ->whereDate('delivery_date', $today)
                                ->whereNot('status', DeliveryNoteStatus::Cancelled));
                    })
                    ->orderBy('planned_end_date')
                    ->limit(5)
                    ->get(),
                viewAllRoute: 'admin.production.job-cards.index',
                viewAllPermission: 'production.view',
                viewAllQuery: ['due_today' => 1],
                user: $user,
            ),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function workload(): array
    {
        $centers = WorkCenter::query()
            ->forTenant()
            ->where('is_active', true)
            ->orderBy('name')
            ->limit(12)
            ->get(['id', 'name', 'code']);

        if ($centers->isEmpty()) {
            return [];
        }

        return $centers->map(function (WorkCenter $center) {
            $metric = $this->workCenters->metricsForCenter($center);

            return [
                'id' => $center->id,
                'name' => $center->name,
                'active_jobs' => (int) ($metric['active_jobs'] ?? 0),
                'queue_count' => (int) ($metric['queue_count'] ?? 0),
                'utilization_percent' => (int) ($metric['utilization_percent'] ?? 0),
                'show_utilization' => true,
                'url' => Route::has('admin.production.work-centers.show')
                    ? route('admin.production.work-centers.show', $center)
                    : null,
            ];
        })->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function quickActions(?User $user): array
    {
        $definitions = [
            ['label' => __('New Job Card'), 'route' => 'admin.production.job-cards.create', 'permission' => 'create', 'model' => ProductionJobCard::class, 'primary' => true],
            ['label' => __('Create From Sales Order'), 'route' => 'admin.production.job-cards.create', 'permission' => 'create', 'model' => ProductionJobCard::class],
            ['label' => __('Open Queue'), 'route' => 'admin.production.queue.index', 'permission' => 'production.queue.view'],
            ['label' => __('Open Scheduling'), 'route' => 'admin.production.scheduling.index', 'permission' => 'production.scheduling.view'],
            ['label' => __('Open QC'), 'route' => 'admin.production.quality.index', 'permission' => 'production.quality.view'],
            ['label' => __('Open Work Centers'), 'route' => 'admin.production.work-centers.index', 'permission' => 'production.work-centers.view'],
        ];

        return collect($definitions)
            ->filter(function (array $item) use ($user) {
                if (! Route::has($item['route'])) {
                    return false;
                }

                if (($item['permission'] ?? null) === 'create') {
                    return $user?->can('create', $item['model'] ?? ProductionJobCard::class) ?? false;
                }

                return $user?->can($item['permission']) ?? false;
            })
            ->values()
            ->all();
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

        return [
            'id' => $jobCard->id,
            'job_number' => $jobCard->job_card_number,
            'customer' => $jobCard->customer?->company_name ?? '—',
            'product_description' => $this->productDescription($jobCard),
            'sales_order_number' => $jobCard->salesOrder?->order_number ?? '—',
            'quantity' => $this->totalQuantity($jobCard),
            'due_date' => $jobCard->planned_end_date?->format('Y-m-d') ?? '—',
            'due_date_iso' => $jobCard->planned_end_date?->toDateString(),
            'work_center' => $jobCard->queues->first()?->workCenter?->name ?? '—',
            'current_stage' => $this->currentStageLabel($jobCard),
            'badge' => $badge,
            'priority' => $jobCard->priority->value,
            'last_updated' => $jobCard->updated_at?->format('Y-m-d H:i') ?? '—',
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

    /**
     * @param  array<string, mixed>  $card
     * @return array<string, mixed>
     */
    protected function presentKpiCard(array $card, ?User $user): array
    {
        $url = null;
        if ($user?->can($card['permission']) && Route::has($card['route'])) {
            $url = route($card['route'], $card['query'] ?? []);
        }

        return [
            ...$card,
            'value' => (string) $card['value'],
            'url' => $url,
            'clickable' => $url !== null,
        ];
    }

    /**
     * @return Builder<ProductionJobCard>
     */
    protected function alertJobQuery(): Builder
    {
        return ProductionJobCard::query()
            ->forTenant()
            ->with(['customer:id,company_name'])
            ->select([
                'id',
                'job_card_number',
                'customer_id',
                'status',
                'planned_end_date',
            ]);
    }

    /**
     * @param  Collection<int, ProductionJobCard>  $jobs
     * @return array<string, mixed>
     */
    protected function alertSection(
        string $title,
        Collection $jobs,
        string $viewAllRoute,
        string $viewAllPermission,
        array $viewAllQuery,
        ?User $user,
    ): array {
        $viewAllUrl = ($user?->can($viewAllPermission) && Route::has($viewAllRoute))
            ? route($viewAllRoute, $viewAllQuery)
            : null;

        return [
            'title' => $title,
            'records' => $jobs->map(fn (ProductionJobCard $job) => [
                'id' => $job->id,
                'job_number' => $job->job_card_number,
                'customer' => $job->customer?->company_name ?? '—',
                'status' => str_replace('_', ' ', $job->status->value),
                'due_date' => $job->planned_end_date?->format('Y-m-d') ?? '—',
                'url' => ($user?->can('view', $job) && Route::has('admin.production.job-cards.show'))
                    ? route('admin.production.job-cards.show', $job)
                    : null,
            ])->all(),
            'view_all_url' => $viewAllUrl,
            'empty' => $jobs->isEmpty(),
        ];
    }

    protected function applyFilters(Builder $query, Request $request): void
    {
        $filters = $this->filtersFromRequest($request);
        $today = now()->toDateString();

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
