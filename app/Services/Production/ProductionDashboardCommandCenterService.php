<?php

namespace App\Services\Production;

use App\Enums\ArtworkRequestStatus;
use App\Enums\Dispatch\DeliveryNoteStatus;
use App\Enums\ProductionJobCardStatus;
use App\Enums\ProductionPriority;
use App\Enums\ProductionQueueStatus;
use App\Models\Assets\MachineProfile;
use App\Models\Production\ProductionJobCard;
use App\Models\Production\ProductionQueue;
use App\Models\Production\QualityCheck;
use App\Models\Production\WorkCenter;
use App\Enums\QualityCheckResult;
use App\Models\User;
use App\Services\Assets\MachineDashboardService;
use App\Services\Assets\MaintenanceDashboardService;
use App\Support\Platform\PlatformCacheService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

class ProductionDashboardCommandCenterService
{
    /**
     * @var list<array{code: string, label: string}>
     */
    private const DEPARTMENTS = [
        ['code' => 'DESIGN', 'label' => 'Design'],
        ['code' => 'PREPRESS', 'label' => 'Prepress'],
        ['code' => 'DIGITAL', 'label' => 'Digital'],
        ['code' => 'OFFSET', 'label' => 'Offset'],
        ['code' => 'LARGE_FORMAT', 'label' => 'Large Format'],
        ['code' => 'PACKAGING', 'label' => 'Packaging'],
        ['code' => 'FINISHING', 'label' => 'Finishing'],
    ];

    public function __construct(
        protected JobTimelineService $timeline,
        protected WorkCenterWorkspaceService $workCenters,
        protected MachineDashboardService $machines,
        protected MaintenanceDashboardService $maintenance,
        protected PlatformCacheService $cache,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function build(?User $user = null): array
    {
        $user ??= auth()->user();

        return [
            'as_of' => now()->format('Y-m-d H:i'),
            'snapshot' => $this->snapshot($user),
            'pipeline' => $this->pipeline($user),
            'urgent' => $this->urgentAttention($user),
            'department_capacity' => $this->departmentCapacity(),
            'machine_capacity' => $this->machineCapacity(),
            'maintenance_alerts' => $this->maintenanceAlerts(),
            'activity' => $this->timeline->recentForTenant(20),
            'quick_actions' => $this->quickActions($user),
            'queue_widgets' => $this->queueWidgets($user),
            'qc_widgets' => $this->qcWidgets($user),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function snapshot(?User $user): array
    {
        $today = now()->toDateString();
        $terminal = [
            ProductionJobCardStatus::Completed->value,
            ProductionJobCardStatus::ReadyForDispatch->value,
            ProductionJobCardStatus::Cancelled->value,
        ];

        $row = ProductionJobCard::query()
            ->forTenant()
            ->selectRaw("SUM(CASE WHEN status IN ('draft','queued') THEN 1 ELSE 0 END) as open_jobs")
            ->selectRaw("SUM(CASE WHEN status = 'in_production' OR status = 'rework' THEN 1 ELSE 0 END) as in_production")
            ->selectRaw("SUM(CASE WHEN status = 'quality_check' THEN 1 ELSE 0 END) as awaiting_qc")
            ->selectRaw("SUM(CASE WHEN status = 'ready_for_dispatch' THEN 1 ELSE 0 END) as ready_for_dispatch")
            ->selectRaw('SUM(CASE WHEN status NOT IN (?,?,?) AND planned_end_date IS NOT NULL AND planned_end_date < ? THEN 1 ELSE 0 END) as delayed_jobs', [
                ...$terminal,
                $today,
            ])
            ->selectRaw("SUM(CASE WHEN status = 'completed' AND DATE(updated_at) = ? THEN 1 ELSE 0 END) as completed_today", [$today])
            ->first();

        $definitions = [
            ['key' => 'open', 'label' => __('Open Jobs'), 'value' => (int) ($row->open_jobs ?? 0), 'icon' => 'inbox', 'route' => 'admin.production.floor', 'permission' => 'production.view'],
            ['key' => 'in_production', 'label' => __('In Production'), 'value' => (int) ($row->in_production ?? 0), 'icon' => 'cog', 'route' => 'admin.production.floor', 'permission' => 'production.view', 'query' => ['stage' => 'on_press']],
            ['key' => 'awaiting_qc', 'label' => __('Awaiting QC'), 'value' => (int) ($row->awaiting_qc ?? 0), 'icon' => 'clipboard-check', 'route' => 'admin.production.floor', 'permission' => 'production.view', 'query' => ['stage' => 'qc']],
            ['key' => 'ready_for_dispatch', 'label' => __('Ready Dispatch'), 'value' => (int) ($row->ready_for_dispatch ?? 0), 'icon' => 'truck', 'route' => 'admin.production.floor', 'permission' => 'production.view', 'query' => ['stage' => 'ready']],
            ['key' => 'delayed', 'label' => __('Delayed'), 'value' => (int) ($row->delayed_jobs ?? 0), 'icon' => 'exclamation', 'route' => 'admin.production.floor', 'permission' => 'production.view', 'query' => ['overdue' => '1']],
            ['key' => 'completed_today', 'label' => __('Completed Today'), 'value' => (int) ($row->completed_today ?? 0), 'icon' => 'check-circle', 'route' => 'admin.production.floor', 'permission' => 'production.view', 'query' => ['stage' => 'out']],
        ];

        return collect($definitions)
            ->map(fn (array $card) => $this->presentKpiCard($card, $user))
            ->all();
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
     * @return list<array<string, mixed>>
     */
    protected function pipeline(?User $user): array
    {
        $base = ProductionJobCard::query()->forTenant();

        $stages = [
            [
                'key' => 'artwork',
                'label' => __('Artwork'),
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
            ],
            [
                'key' => 'scheduled',
                'label' => __('Scheduled'),
                'count' => (clone $base)
                    ->whereNotNull('planned_start_date')
                    ->whereNotNull('planned_end_date')
                    ->whereIn('status', [
                        ProductionJobCardStatus::Draft,
                        ProductionJobCardStatus::Queued,
                    ])
                    ->count(),
                'route' => 'admin.production.scheduling.index',
                'permission' => 'production.scheduling.view',
            ],
            [
                'key' => 'queued',
                'label' => __('Queued'),
                'count' => (clone $base)->where('status', ProductionJobCardStatus::Queued)->count(),
                'route' => 'admin.production.queue.index',
                'permission' => 'production.queue.view',
            ],
            [
                'key' => 'production',
                'label' => __('Production'),
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
                'count' => (clone $base)->where('status', ProductionJobCardStatus::QualityCheck)->count(),
                'route' => 'admin.production.quality.index',
                'permission' => 'production.quality.view',
                'query' => ['status' => 'pending'],
            ],
            [
                'key' => 'dispatch',
                'label' => __('Dispatch'),
                'count' => (clone $base)->where('status', ProductionJobCardStatus::ReadyForDispatch)->count(),
                'route' => 'admin.dispatch.dashboard',
                'permission' => 'dispatch.view',
            ],
            [
                'key' => 'delivered',
                'label' => __('Delivered'),
                'count' => $this->dispatchTrackingAvailable()
                    ? (clone $base)
                        ->whereHas('deliveryNotes', fn (Builder $q) => $q->where('status', DeliveryNoteStatus::Delivered))
                        ->count()
                    : 0,
                'route' => 'admin.dispatch.delivery-notes.index',
                'permission' => 'dispatch.view',
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
                'url' => $url,
            ];
        })->all();
    }

    /**
     * @return array<string, mixed>
     */
    protected function urgentAttention(?User $user): array
    {
        $today = now()->toDateString();
        $activeOverdueStatuses = [
            ProductionJobCardStatus::Draft,
            ProductionJobCardStatus::Queued,
            ProductionJobCardStatus::InProduction,
            ProductionJobCardStatus::QualityCheck,
            ProductionJobCardStatus::OnHold,
            ProductionJobCardStatus::Rework,
        ];

        return [
            'overdue_jobs' => $this->urgentSection(
                title: __('Overdue Jobs'),
                jobs: $this->urgentJobQuery()
                    ->whereIn('status', $activeOverdueStatuses)
                    ->whereDate('planned_end_date', '<', $today)
                    ->orderBy('planned_end_date')
                    ->limit(5)
                    ->get(),
                viewAllRoute: 'admin.production.scheduling.index',
                viewAllPermission: 'production.scheduling.view',
                user: $user,
            ),
            'awaiting_artwork' => $this->urgentSection(
                title: __('Jobs Awaiting Artwork'),
                jobs: $this->urgentJobQuery()
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
                user: $user,
            ),
            'awaiting_qc' => $this->urgentSection(
                title: __('Jobs Awaiting QC'),
                jobs: $this->urgentJobQuery()
                    ->where('status', ProductionJobCardStatus::QualityCheck)
                    ->orderBy('planned_end_date')
                    ->limit(5)
                    ->get(),
                viewAllRoute: 'admin.production.quality.index',
                viewAllPermission: 'production.quality.view',
                user: $user,
                viewAllQuery: ['status' => 'pending'],
            ),
            'dispatch_due_today' => $this->urgentSection(
                title: __('Dispatch Due Today'),
                jobs: $this->urgentJobQuery()
                    ->whereIn('status', [
                        ProductionJobCardStatus::ReadyForDispatch,
                        ProductionJobCardStatus::Completed,
                    ])
                    ->when($this->dispatchTrackingAvailable(), function (Builder $q) use ($today) {
                        $q->where(function (Builder $inner) use ($today) {
                            $inner->whereDate('planned_end_date', $today)
                                ->orWhereHas('deliveryNotes', fn (Builder $dn) => $dn
                                    ->whereDate('delivery_date', $today)
                                    ->whereNot('status', DeliveryNoteStatus::Cancelled));
                        });
                    }, fn (Builder $q) => $q->whereDate('planned_end_date', $today))
                    ->orderBy('planned_end_date')
                    ->limit(5)
                    ->get(),
                viewAllRoute: 'admin.dispatch.dashboard',
                viewAllPermission: 'dispatch.view',
                user: $user,
            ),
            'escalated_jobs' => $this->urgentSection(
                title: __('Escalated Jobs'),
                jobs: $this->urgentJobQuery()
                    ->whereIn('priority', [ProductionPriority::Urgent, ProductionPriority::High])
                    ->whereNotIn('status', [
                        ProductionJobCardStatus::Completed,
                        ProductionJobCardStatus::ReadyForDispatch,
                        ProductionJobCardStatus::Cancelled,
                    ])
                    ->orderByRaw("CASE priority WHEN 'urgent' THEN 1 WHEN 'high' THEN 2 ELSE 3 END")
                    ->orderBy('planned_end_date')
                    ->limit(5)
                    ->get(),
                viewAllRoute: 'admin.production.job-cards.index',
                viewAllPermission: 'production.view',
                user: $user,
            ),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function departmentCapacity(): array
    {
        $centers = WorkCenter::query()
            ->forTenant()
            ->where('is_active', true)
            ->get(['id', 'public_id', 'name', 'code']);

        if ($centers->isEmpty()) {
            return collect(self::DEPARTMENTS)
                ->map(fn (array $dept) => $this->presentDepartmentRow($dept, 0, 0, 0, 0))
                ->all();
        }

        $metrics = $this->workCenters->metricsForCenters($centers->pluck('id'));
        $capacity = $this->workCenters->defaultCapacity();

        return collect(self::DEPARTMENTS)->map(function (array $dept) use ($centers, $metrics, $capacity) {
            $deptCenters = $centers->where('code', $dept['code']);
            $activeJobs = 0;
            $queueCount = 0;
            $utilization = 0;

            foreach ($deptCenters as $center) {
                $metric = $metrics[$center->id] ?? [];
                $activeJobs += (int) ($metric['active_jobs'] ?? 0);
                $queueCount += (int) ($metric['queue_count'] ?? 0);
                $utilization = max($utilization, (int) ($metric['utilization_percent'] ?? 0));
            }

            $slotCapacity = max(1, $deptCenters->count()) * $capacity;

            return $this->presentDepartmentRow($dept, $activeJobs, $queueCount, $utilization, $slotCapacity);
        })->all();
    }

    /**
     * @return array<string, mixed>
     */
    protected function presentDepartmentRow(array $dept, int $activeJobs, int $queueCount, int $utilization, int $slotCapacity): array
    {
        return [
            'key' => strtolower($dept['code']),
            'label' => __($dept['label']),
            'active_jobs' => $activeJobs,
            'queue_count' => $queueCount,
            'utilization_percent' => $utilization,
            'capacity' => $slotCapacity,
            'is_overbooked' => $utilization > 100,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function machineCapacity(): array
    {
        $companyId = (int) tenant()->companyId();
        $branchId = tenant()->branchId();
        $cacheKey = $branchId ? "{$companyId}:{$branchId}" : "{$companyId}:all";

        return $this->cache->remember('production_machine_overview', $cacheKey, function () use ($companyId, $branchId) {
            $profileQuery = MachineProfile::query()
                ->where('company_id', $companyId)
                ->with(['asset:id,public_id,asset_name,asset_number', 'workCenter:id,public_id,name,code']);

            if ($branchId) {
                $profileQuery->where('branch_id', $branchId);
            }

            $profiles = $profileQuery->orderBy('machine_code')->get();

            if ($profiles->isNotEmpty()) {
                $dashboard = $this->machines->build($companyId, $branchId);
                $metrics = $dashboard['metrics'] ?? [];

                $machines = $profiles->take(8)->map(function (MachineProfile $profile) use ($metrics) {
                    $metric = $metrics[$profile->id] ?? [];
                    $utilization = (int) round($metric['current_utilization'] ?? $profile->current_utilization);
                    $status = $profile->production_status;

                    return [
                        'id' => $profile->fixed_asset_id,
                        'name' => $profile->asset?->asset_name ?? $profile->machine_code,
                        'code' => $profile->machine_code,
                        'status' => $status->label(),
                        'status_variant' => $status->badgeVariant(),
                        'utilization_percent' => $utilization,
                        'downtime_percent' => max(0, 100 - min(100, $utilization)),
                        'is_available' => $status->acceptsJobs() && $utilization < 100,
                        'capacity_alert' => $utilization >= 90,
                        'url' => ($profile->asset && Route::has('admin.assets.machines.show'))
                            ? route('admin.assets.machines.show', $profile->asset)
                            : null,
                    ];
                })->values()->all();

                return [
                    'utilization_percent' => (int) round($dashboard['utilization_percent'] ?? 0),
                    'downtime_percent' => max(0, 100 - min(100, (int) round($dashboard['utilization_percent'] ?? 0))),
                    'availability_percent' => (int) round(100 - ($dashboard['offline_machines'] + $dashboard['maintenance_holds']) / max(1, $dashboard['total_machines']) * 100),
                    'available_count' => $dashboard['available_machines'] ?? 0,
                    'running_count' => $dashboard['running_machines'] ?? 0,
                    'offline_count' => $dashboard['offline_machines'] ?? 0,
                    'maintenance_count' => $dashboard['maintenance_holds'] ?? 0,
                    'capacity_alerts' => collect($machines)->where('capacity_alert', true)->count(),
                    'machines' => $machines,
                ];
            }

            return $this->legacyWorkCenterMachineCapacity();
        }, config('platform.cache.machines_dashboard', 60));
    }

    /**
     * @return array<string, mixed>
     */
    protected function legacyWorkCenterMachineCapacity(): array
    {
        $centers = WorkCenter::query()
            ->forTenant()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'public_id', 'name', 'code']);

        if ($centers->isEmpty()) {
            return [
                'utilization_percent' => 0,
                'downtime_percent' => 0,
                'availability_percent' => 100,
                'available_count' => 0,
                'running_count' => 0,
                'offline_count' => 0,
                'maintenance_count' => 0,
                'capacity_alerts' => 0,
                'machines' => [],
            ];
        }

        $metrics = $this->workCenters->metricsForCenters($centers->pluck('id'));
        $utilizations = $centers->map(fn (WorkCenter $center) => (int) ($metrics[$center->id]['utilization_percent'] ?? 0));
        $avgUtilization = (int) round($utilizations->avg() ?: 0);
        $overbookedCount = $centers->filter(fn (WorkCenter $center) => (bool) ($metrics[$center->id]['is_overbooked'] ?? false))->count();
        $availabilityPercent = (int) round((($centers->count() - $overbookedCount) / max(1, $centers->count())) * 100);

        $machines = $centers->take(8)->map(function (WorkCenter $center) use ($metrics) {
            $metric = $metrics[$center->id] ?? [];
            $utilization = (int) ($metric['utilization_percent'] ?? 0);

            return [
                'id' => $center->id,
                'name' => $center->name,
                'code' => $center->code,
                'status' => null,
                'status_variant' => 'neutral',
                'utilization_percent' => $utilization,
                'downtime_percent' => max(0, 100 - min(100, $utilization)),
                'is_available' => ! ($metric['is_overbooked'] ?? false),
                'capacity_alert' => $utilization >= 90,
                'url' => (Route::has('admin.production.work-centers.show') && filled($center->getRouteKey()))
                    ? route('admin.production.work-centers.show', $center)
                    : null,
            ];
        })->values()->all();

        return [
            'utilization_percent' => $avgUtilization,
            'downtime_percent' => max(0, 100 - min(100, $avgUtilization)),
            'availability_percent' => $availabilityPercent,
            'available_count' => $centers->count() - $overbookedCount,
            'running_count' => 0,
            'offline_count' => 0,
            'maintenance_count' => 0,
            'capacity_alerts' => collect($machines)->where('capacity_alert', true)->count(),
            'machines' => $machines,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function maintenanceAlerts(): array
    {
        $companyId = (int) tenant()->companyId();
        $stats = $this->maintenance->build($companyId, tenant()->branchId());

        return [
            'critical_failures' => $stats['critical_failures'] ?? 0,
            'open_work_orders' => $stats['open_work_orders'] ?? 0,
            'downtime_hours' => $stats['downtime_hours'] ?? 0,
            'orders' => collect($stats['critical_orders'] ?? [])
                ->filter(fn ($order) => is_array($order))
                ->take(5)
                ->map(fn (array $order) => [
                    'work_order_no' => $order['work_order_no'] ?? '—',
                    'asset_name' => $order['asset_name'] ?? null,
                    'url' => ! empty($order['public_id']) && Route::has('admin.assets.maintenance.work-orders.show')
                        ? route('admin.assets.maintenance.work-orders.show', $order['public_id'])
                        : ($order['url'] ?? null),
                ])
                ->values()
                ->all(),
        ];
    }

    /**
     * @return Builder<ProductionJobCard>
     */
    protected function urgentJobQuery(): Builder
    {
        return ProductionJobCard::query()
            ->forTenant()
            ->with(['customer:id,company_name'])
            ->select([
                'id',
                'public_id',
                'job_card_number',
                'customer_id',
                'status',
                'priority',
                'planned_end_date',
            ]);
    }

    /**
     * @param  Collection<int, ProductionJobCard>  $jobs
     * @return array<string, mixed>
     */
    protected function urgentSection(
        string $title,
        Collection $jobs,
        string $viewAllRoute,
        string $viewAllPermission,
        ?User $user,
        array $viewAllQuery = [],
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
                'priority' => $job->priority?->value,
                'due_date' => $job->planned_end_date?->format('Y-m-d') ?? '—',
                'url' => ($user?->can('view', $job) && Route::has('admin.production.job-cards.show'))
                    ? route('admin.production.job-cards.show', $job)
                    : null,
            ])->all(),
            'view_all_url' => $viewAllUrl,
            'empty' => $jobs->isEmpty(),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function quickActions(?User $user): array
    {
        $definitions = [
            [
                'label' => __('Create Job Card'),
                'route' => 'admin.production.job-cards.create',
                'permission' => 'create',
                'model' => ProductionJobCard::class,
                'primary' => true,
            ],
            [
                'label' => __('Schedule Job'),
                'route' => 'admin.production.scheduling.index',
                'permission' => 'production.scheduling.view',
            ],
            [
                'label' => __('Record QC'),
                'route' => 'admin.production.quality.index',
                'permission' => 'production.quality.view',
                'query' => ['status' => 'pending'],
            ],
            [
                'label' => __('Create Delivery Note'),
                'route' => 'admin.dispatch.delivery-notes.index',
                'permission' => 'dispatch.view',
            ],
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
            ->map(function (array $item) {
                return [
                    'label' => $item['label'],
                    'url' => route($item['route'], $item['query'] ?? []),
                    'primary' => (bool) ($item['primary'] ?? false),
                ];
            })
            ->values()
            ->all();
    }

    protected function dispatchTrackingAvailable(): bool
    {
        return Schema::hasTable('delivery_notes')
            && method_exists(ProductionJobCard::class, 'deliveryNotes');
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function queueWidgets(?User $user): array
    {
        $today = now()->toDateString();
        $terminal = [
            ProductionJobCardStatus::Completed->value,
            ProductionJobCardStatus::ReadyForDispatch->value,
            ProductionJobCardStatus::Cancelled->value,
        ];

        $waitingQueues = ProductionQueue::query()
            ->forTenant()
            ->where('status', ProductionQueueStatus::Waiting)
            ->count();

        $inProgress = ProductionJobCard::query()
            ->forTenant()
            ->whereIn('status', [
                ProductionJobCardStatus::InProduction,
                ProductionJobCardStatus::Rework,
            ])
            ->count();

        $completedToday = ProductionJobCard::query()
            ->forTenant()
            ->where('status', ProductionJobCardStatus::Completed)
            ->whereDate('updated_at', $today)
            ->count();

        $overdue = ProductionJobCard::query()
            ->forTenant()
            ->whereNotIn('status', $terminal)
            ->where(function ($q) use ($today) {
                $q->whereDate('required_date', '<', $today)
                    ->orWhere(function ($inner) use ($today) {
                        $inner->whereNull('required_date')
                            ->whereNotNull('planned_end_date')
                            ->whereDate('planned_end_date', '<', $today);
                    });
            })
            ->count();

        $outsourced = ProductionJobCard::query()
            ->forTenant()
            ->where('status', ProductionJobCardStatus::Outsourced)
            ->count();

        $centers = WorkCenter::query()->forTenant()->where('is_active', true)->get(['id', 'name']);
        $metrics = $this->workCenters->metricsForCenters($centers->pluck('id'));
        $workCenterLoad = $centers->sum(fn (WorkCenter $wc) => (int) ($metrics[$wc->id]['active_jobs'] ?? 0)
            + (int) ($metrics[$wc->id]['queue_count'] ?? 0));

        $definitions = [
            ['key' => 'waiting', 'label' => __('Jobs Waiting'), 'value' => $waitingQueues, 'icon' => 'clock', 'route' => 'admin.production.queue.index', 'permission' => 'production.queue.view', 'query' => ['status' => 'waiting']],
            ['key' => 'in_progress', 'label' => __('Jobs In Progress'), 'value' => $inProgress, 'icon' => 'cog', 'route' => 'admin.production.job-cards.index', 'permission' => 'production.view', 'query' => ['status' => 'in_production']],
            ['key' => 'completed_today', 'label' => __('Jobs Completed Today'), 'value' => $completedToday, 'icon' => 'check-circle', 'route' => 'admin.production.job-cards.index', 'permission' => 'production.view', 'query' => ['status' => 'completed']],
            ['key' => 'overdue', 'label' => __('Overdue Jobs'), 'value' => $overdue, 'icon' => 'exclamation', 'route' => 'admin.production.scheduling.index', 'permission' => 'production.scheduling.view'],
            ['key' => 'work_center_load', 'label' => __('Work Center Load'), 'value' => $workCenterLoad, 'icon' => 'office-building', 'route' => 'admin.production.work-centers.index', 'permission' => 'production.work-centers.view'],
            ['key' => 'outsourced', 'label' => __('Outsourced Jobs'), 'value' => $outsourced, 'icon' => 'truck', 'route' => 'admin.production.job-cards.index', 'permission' => 'production.view', 'query' => ['status' => 'outsourced']],
        ];

        return collect($definitions)
            ->map(fn (array $card) => $this->presentKpiCard($card, $user))
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function qcWidgets(?User $user): array
    {
        $today = now()->toDateString();
        $checks = QualityCheck::query()->forTenant();

        $definitions = [
            ['key' => 'pending_inspection', 'label' => __('Pending Inspection'), 'value' => ProductionJobCard::query()->forTenant()->where('status', ProductionJobCardStatus::QualityCheck)->count(), 'icon' => 'clipboard-check', 'route' => 'admin.production.quality.index', 'permission' => 'production.quality.view', 'query' => ['status' => 'pending']],
            ['key' => 'passed_today', 'label' => __('Passed Today'), 'value' => (clone $checks)->where('result', QualityCheckResult::Passed)->whereDate('checked_at', $today)->count(), 'icon' => 'check-circle', 'route' => 'admin.production.quality.index', 'permission' => 'production.quality.view', 'query' => ['status' => 'passed', 'date' => $today]],
            ['key' => 'failed_today', 'label' => __('Failed Today'), 'value' => (clone $checks)->where('result', QualityCheckResult::Failed)->whereDate('checked_at', $today)->count(), 'icon' => 'x-circle', 'route' => 'admin.production.quality.index', 'permission' => 'production.quality.view', 'query' => ['status' => 'failed', 'date' => $today]],
            ['key' => 'awaiting_approval', 'label' => __('Awaiting Approval'), 'value' => ProductionJobCard::query()->forTenant()->where('status', ProductionJobCardStatus::AwaitingCustomerApproval)->count(), 'icon' => 'clock', 'route' => 'admin.production.job-cards.index', 'permission' => 'production.view', 'query' => ['status' => 'awaiting_customer_approval']],
            ['key' => 'rework_jobs', 'label' => __('Rework Jobs'), 'value' => ProductionJobCard::query()->forTenant()->where('status', ProductionJobCardStatus::Rework)->count(), 'icon' => 'refresh', 'route' => 'admin.production.job-cards.index', 'permission' => 'production.view', 'query' => ['status' => 'rework']],
        ];

        return collect($definitions)
            ->map(fn (array $card) => $this->presentKpiCard($card, $user))
            ->all();
    }
}
