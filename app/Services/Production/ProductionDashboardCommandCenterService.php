<?php

namespace App\Services\Production;

use App\Enums\ArtworkRequestStatus;
use App\Enums\Dispatch\DeliveryNoteStatus;
use App\Enums\ProductionJobCardStatus;
use App\Enums\QualityCheckResult;
use App\Models\Production\ProductionJobCard;
use App\Models\Production\ProductionQueue;
use App\Models\Production\QualityCheck;
use App\Models\Production\WorkCenter;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

class ProductionDashboardCommandCenterService
{
    public function __construct(
        protected JobTimelineService $timeline,
        protected WorkCenterWorkspaceService $workCenters,
        protected JobProductionControlService $controls,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function build(?User $user = null): array
    {
        $user ??= auth()->user();
        $today = now()->toDateString();
        $weekStart = now()->startOfWeek()->toDateString();

        return [
            'as_of' => now()->format('Y-m-d H:i'),
            'kpis' => $this->kpis($user),
            'pipeline' => $this->pipeline($user),
            'urgent' => $this->urgentAttention($user),
            'schedule' => $this->todaysSchedule(),
            'work_center_load' => $this->workCenterLoad(),
            'activity' => $this->timeline->recentForTenant(20),
            'quick_actions' => $this->quickActions($user),
            'performance' => $this->performanceSnapshot($today, $weekStart),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function kpis(?User $user): array
    {
        $today = now()->toDateString();
        $terminal = [
            ProductionJobCardStatus::Completed->value,
            ProductionJobCardStatus::ReadyForDispatch->value,
            ProductionJobCardStatus::Cancelled->value,
        ];
        $activeOverdueStatuses = [
            ProductionJobCardStatus::Draft->value,
            ProductionJobCardStatus::Queued->value,
            ProductionJobCardStatus::InProduction->value,
            ProductionJobCardStatus::QualityCheck->value,
            ProductionJobCardStatus::OnHold->value,
            ProductionJobCardStatus::Rework->value,
        ];

        $row = ProductionJobCard::query()
            ->forTenant()
            ->selectRaw("SUM(CASE WHEN status IN ('draft','queued') THEN 1 ELSE 0 END) as open_jobs")
            ->selectRaw("SUM(CASE WHEN status = 'in_production' OR status = 'rework' THEN 1 ELSE 0 END) as in_production")
            ->selectRaw("SUM(CASE WHEN status = 'quality_check' THEN 1 ELSE 0 END) as awaiting_qc")
            ->selectRaw("SUM(CASE WHEN status = 'ready_for_dispatch' THEN 1 ELSE 0 END) as ready_for_dispatch")
            ->selectRaw("SUM(CASE WHEN status = 'completed' AND DATE(updated_at) = ? THEN 1 ELSE 0 END) as completed_today", [$today])
            ->selectRaw('SUM(CASE WHEN status NOT IN (?,?,?) AND planned_end_date IS NOT NULL AND planned_end_date < ? THEN 1 ELSE 0 END) as delayed_jobs', [
                ...$terminal,
                $today,
            ])
            ->selectRaw('SUM(CASE WHEN status IN (?,?,?,?,?,?) AND planned_end_date IS NOT NULL AND planned_end_date < ? THEN 1 ELSE 0 END) as overdue_jobs', [
                ...$activeOverdueStatuses,
                $today,
            ])
            ->selectRaw("SUM(CASE WHEN status = 'on_hold' THEN 1 ELSE 0 END) as on_hold")
            ->first();

        $definitions = [
            ['key' => 'open', 'label' => __('Open Jobs'), 'value' => (int) ($row->open_jobs ?? 0), 'icon' => 'inbox', 'route' => 'admin.production.job-cards.index', 'permission' => 'production.view', 'query' => []],
            ['key' => 'in_production', 'label' => __('Jobs In Production'), 'value' => (int) ($row->in_production ?? 0), 'icon' => 'cog', 'route' => 'admin.production.job-cards.index', 'permission' => 'production.view', 'query' => ['status' => 'in_production']],
            ['key' => 'awaiting_qc', 'label' => __('Awaiting QC'), 'value' => (int) ($row->awaiting_qc ?? 0), 'icon' => 'clipboard-check', 'route' => 'admin.production.quality.index', 'permission' => 'production.quality.view', 'query' => ['status' => 'pending']],
            ['key' => 'ready_for_dispatch', 'label' => __('Ready For Dispatch'), 'value' => (int) ($row->ready_for_dispatch ?? 0), 'icon' => 'truck', 'route' => 'admin.production.job-cards.index', 'permission' => 'production.view', 'query' => ['status' => 'ready_for_dispatch']],
            ['key' => 'completed_today', 'label' => __('Completed Today'), 'value' => (int) ($row->completed_today ?? 0), 'icon' => 'check-circle', 'route' => 'admin.production.job-cards.index', 'permission' => 'production.view', 'query' => []],
            ['key' => 'delayed', 'label' => __('Delayed Jobs'), 'value' => (int) ($row->delayed_jobs ?? 0), 'icon' => 'exclamation', 'route' => 'admin.production.scheduling.index', 'permission' => 'production.scheduling.view', 'query' => []],
            ['key' => 'overdue', 'label' => __('Overdue Jobs'), 'value' => (int) ($row->overdue_jobs ?? 0), 'icon' => 'clock', 'route' => 'admin.production.scheduling.index', 'permission' => 'production.scheduling.view', 'query' => []],
            ['key' => 'on_hold', 'label' => __('On Hold Jobs'), 'value' => (int) ($row->on_hold ?? 0), 'icon' => 'pause', 'route' => 'admin.production.job-cards.index', 'permission' => 'production.view', 'query' => ['status' => 'on_hold']],
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
        if (
            $user?->can($card['permission'])
            && Route::has($card['route'])
        ) {
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
        $active = fn () => (clone $base)->whereNotIn('status', [
            ProductionJobCardStatus::Cancelled,
        ]);

        $stages = [
            [
                'key' => 'artwork',
                'label' => __('Artwork'),
                'count' => (clone $active)()
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
                'key' => 'quality',
                'label' => __('Quality Control'),
                'count' => (clone $base)->where('status', ProductionJobCardStatus::QualityCheck)->count(),
                'route' => 'admin.production.quality.index',
                'permission' => 'production.quality.view',
            ],
            [
                'key' => 'dispatch',
                'label' => __('Dispatch'),
                'count' => (clone $base)->whereIn('status', [
                    ProductionJobCardStatus::ReadyForDispatch,
                    ProductionJobCardStatus::Completed,
                ])->count(),
                'route' => 'admin.workspaces.dispatch',
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

        return [
            'delayed' => $this->urgentSection(
                title: __('Delayed Jobs'),
                jobs: $this->urgentJobQuery()
                    ->whereNotIn('status', [
                        ProductionJobCardStatus::Completed,
                        ProductionJobCardStatus::ReadyForDispatch,
                        ProductionJobCardStatus::Cancelled,
                    ])
                    ->whereDate('planned_end_date', '<', $today)
                    ->orderBy('planned_end_date')
                    ->limit(5)
                    ->get(),
                viewAllRoute: 'admin.production.scheduling.index',
                viewAllPermission: 'production.scheduling.view',
                user: $user,
            ),
            'awaiting_artwork' => $this->urgentSection(
                title: __('Awaiting Artwork'),
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
                title: __('Awaiting QC'),
                jobs: $this->urgentJobQuery()
                    ->where('status', ProductionJobCardStatus::QualityCheck)
                    ->orderBy('planned_end_date')
                    ->limit(5)
                    ->get(),
                viewAllRoute: 'admin.production.quality.index',
                viewAllPermission: 'production.quality.view',
                user: $user,
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
                viewAllRoute: 'admin.workspaces.dispatch',
                viewAllPermission: 'dispatch.view',
                user: $user,
            ),
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
    protected function urgentSection(
        string $title,
        Collection $jobs,
        string $viewAllRoute,
        string $viewAllPermission,
        ?User $user,
    ): array {
        $viewAllUrl = ($user?->can($viewAllPermission) && Route::has($viewAllRoute))
            ? route($viewAllRoute)
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

    /**
     * @return list<array<string, mixed>>
     */
    protected function todaysSchedule(): array
    {
        $today = now()->toDateString();

        return ProductionJobCard::query()
            ->forTenant()
            ->with([
                'customer:id,company_name',
                'queues' => fn ($q) => $q->with('workCenter:id,name')->orderBy('queue_position')->limit(1),
            ])
            ->where(function (Builder $q) use ($today) {
                $q->whereDate('planned_start_date', $today)
                    ->orWhereDate('planned_end_date', $today);
            })
            ->whereNot('status', ProductionJobCardStatus::Cancelled)
            ->orderBy('planned_start_date')
            ->orderBy('job_card_number')
            ->limit(20)
            ->get()
            ->map(fn (ProductionJobCard $job) => [
                'id' => $job->id,
                'job_number' => $job->job_card_number,
                'customer' => $job->customer?->company_name ?? '—',
                'work_center' => $job->queues->first()?->workCenter?->name ?? '—',
                'planned_start' => $job->planned_start_date?->format('Y-m-d') ?? '—',
                'planned_end' => $job->planned_end_date?->format('Y-m-d') ?? '—',
                'status' => str_replace('_', ' ', $job->status->value),
                'url' => Route::has('admin.production.job-cards.show')
                    ? route('admin.production.job-cards.show', $job)
                    : null,
            ])
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function workCenterLoad(): array
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
            $utilization = (int) ($metric['utilization_percent'] ?? 0);

            return [
                'id' => $center->id,
                'name' => $center->name,
                'active_jobs' => (int) ($metric['active_jobs'] ?? 0),
                'queue_count' => (int) ($metric['queue_count'] ?? 0),
                'utilization_percent' => $utilization,
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
    protected function quickActions(?User $user): array
    {
        $definitions = [
            ['label' => __('New Job Card'), 'route' => 'admin.production.job-cards.create', 'permission' => 'create', 'model' => ProductionJobCard::class, 'primary' => true],
            ['label' => __('Job Cards'), 'route' => 'admin.production.job-cards.index', 'permission' => 'production.view'],
            ['label' => __('Queue'), 'route' => 'admin.production.queue.index', 'permission' => 'production.queue.view'],
            ['label' => __('Scheduling'), 'route' => 'admin.production.scheduling.index', 'permission' => 'production.scheduling.view'],
            ['label' => __('Quality Control'), 'route' => 'admin.production.quality.index', 'permission' => 'production.quality.view'],
            ['label' => __('Work Centers'), 'route' => 'admin.production.work-centers.index', 'permission' => 'production.work-centers.view'],
            ['label' => __('Dispatch'), 'route' => 'admin.workspaces.dispatch', 'permission' => 'dispatch.view'],
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
     * @return array<string, mixed>
     */
    protected function performanceSnapshot(string $today, string $weekStart): array
    {
        $qc = QualityCheck::query()
            ->forTenant()
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("SUM(CASE WHEN result = ? THEN 1 ELSE 0 END) as passed", [QualityCheckResult::Passed->value])
            ->first();

        $totalChecks = (int) ($qc->total ?? 0);
        $passedChecks = (int) ($qc->passed ?? 0);

        $deliveredToday = $this->dispatchTrackingAvailable()
            ? ProductionJobCard::query()
                ->forTenant()
                ->whereHas('deliveryNotes', fn (Builder $q) => $q
                    ->where('status', DeliveryNoteStatus::Delivered)
                    ->whereDate('delivered_at', $today))
                ->count()
            : 0;

        return [
            'completed_today' => (int) ProductionJobCard::query()
                ->forTenant()
                ->where('status', ProductionJobCardStatus::Completed)
                ->whereDate('updated_at', $today)
                ->count(),
            'completed_week' => (int) ProductionJobCard::query()
                ->forTenant()
                ->where('status', ProductionJobCardStatus::Completed)
                ->whereDate('updated_at', '>=', $weekStart)
                ->count(),
            'qc_pass_rate' => $totalChecks > 0 ? (int) round(($passedChecks / $totalChecks) * 100) : null,
            'qc_pass_label' => $totalChecks > 0 ? "{$passedChecks}/{$totalChecks}" : '—',
            'delivered_today' => $deliveredToday,
        ];
    }

    protected function dispatchTrackingAvailable(): bool
    {
        return Schema::hasTable('delivery_notes')
            && method_exists(ProductionJobCard::class, 'deliveryNotes');
    }
}
