<?php

namespace App\Services\Production;

use App\Enums\ProductionJobCardStatus;
use App\Enums\ProductionQueueStatus;
use App\Models\Production\ProductionJobCard;
use App\Models\Production\ProductionQueue;
use App\Models\Production\ProductionStage;
use App\Models\Production\WorkCenter;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

class WorkCenterWorkspaceService
{
    /**
     * @return array<string, mixed>
     */
    public function build(Request $request, ?User $user = null): array
    {
        $user ??= auth()->user();
        $filters = $this->filtersFromRequest($request);

        return [
            'as_of' => now()->format('Y-m-d H:i'),
            'filters' => $filters,
            'filter_options' => $this->filterOptions(),
            'active_filter_chips' => $this->activeFilterChips($filters),
            'has_active_filters' => $this->hasActiveFilters($filters),
            'kpis' => $this->executiveKpis(),
            'work_centers' => $this->paginatedIndex($request),
            'stages' => $this->stageMap(),
            'workload' => $this->workloadPanel(),
            'bottlenecks' => $this->bottlenecks(),
            'default_capacity' => $this->defaultCapacity(),
            'can_view_queue' => $user?->can('production.queue.view') && Route::has('admin.production.queue.index'),
            'can_view_scheduling' => $user?->can('production.scheduling.view') && Route::has('admin.production.scheduling.index'),
            'can_view_job_360' => $user?->can('production.view') && Route::has('admin.production.job-cards.show'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function buildShow(WorkCenter $workCenter, ?User $user = null): array
    {
        $user ??= auth()->user();
        $metrics = $this->metricsForCenter($workCenter);

        return [
            'metrics' => $metrics,
            'active_queues' => $this->activeQueuesForCenter($workCenter),
            'recent_completed' => $this->recentCompletedForCenter($workCenter),
            'upcoming_scheduled' => $this->upcomingScheduledForCenter($workCenter),
            'delayed_jobs' => $this->delayedJobsForCenter($workCenter),
            'awaiting_qc' => $this->awaitingQcForCenter($workCenter),
            'default_capacity' => $this->defaultCapacity(),
            'can_view_queue' => $user?->can('production.queue.view') && Route::has('admin.production.queue.index'),
            'can_view_scheduling' => $user?->can('production.scheduling.view') && Route::has('admin.production.scheduling.index'),
            'can_view_job_360' => $user?->can('production.view') && Route::has('admin.production.job-cards.show'),
            'queue_url' => ($user?->can('production.queue.view') && Route::has('admin.production.queue.index'))
                ? route('admin.production.queue.index', ['work_center_id' => $workCenter->id])
                : null,
            'scheduling_url' => ($user?->can('production.scheduling.view') && Route::has('admin.production.scheduling.index'))
                ? route('admin.production.scheduling.index', ['work_center_id' => $workCenter->id])
                : null,
        ];
    }

    /**
     * @return array{
     *     search: ?string,
     *     stage_id: ?string,
     *     status: ?string,
     *     load: ?string
     * }
     */
    public function filtersFromRequest(Request $request): array
    {
        return [
            'search' => $request->query('search'),
            'stage_id' => $request->query('stage_id'),
            'status' => $request->query('status'),
            'load' => $request->query('load'),
        ];
    }

    public function paginatedIndex(Request $request): LengthAwarePaginator
    {
        $filters = $this->filtersFromRequest($request);
        $query = $this->filteredQuery($request);

        if (filled($filters['load'] ?? null)) {
            $matchingIds = $this->centerIdsMatchingLoad($filters['load']);
            $query->whereIn('id', $matchingIds !== [] ? $matchingIds : [0]);
        }

        $paginator = $query
            ->orderBy('name')
            ->paginate(config('platform.pagination.default', 15))
            ->withQueryString();

        $metrics = $this->metricsForCenters($paginator->getCollection()->pluck('id'));
        $lastActivity = $this->lastActivityForCenters($paginator->getCollection()->pluck('id'));

        $paginator->getCollection()->transform(function (WorkCenter $center) use ($metrics, $lastActivity) {
            $center->setAttribute('workspace_metrics', $metrics[$center->id] ?? $this->emptyMetrics());
            $center->setAttribute('last_activity_at', $lastActivity[$center->id] ?? null);

            return $center;
        });

        return $paginator;
    }

    /**
     * @return array<string, mixed>
     */
    public function presentCenterRow(WorkCenter $center, ?User $user = null): array
    {
        $user ??= auth()->user();
        $metrics = $center->workspace_metrics ?? $this->metricsForCenter($center);
        $lastActivity = $center->last_activity_at ?? $this->lastActivityForCenter($center);

        return [
            'id' => $center->id,
            'name' => $center->name,
            'code' => $center->code,
            'is_active' => $center->is_active,
            'stage_name' => $metrics['stage_name'] ?? '—',
            'stage_code' => $metrics['stage_code'],
            'active_jobs' => (int) ($metrics['active_jobs'] ?? 0),
            'queue_count' => (int) ($metrics['queue_count'] ?? 0),
            'capacity' => (int) ($metrics['capacity'] ?? $this->defaultCapacity()),
            'utilization_percent' => (int) ($metrics['utilization_percent'] ?? 0),
            'is_overbooked' => (bool) ($metrics['is_overbooked'] ?? false),
            'last_activity' => $lastActivity,
            'show_url' => ($user?->can('view', $center) && Route::has('admin.production.work-centers.show'))
                ? route('admin.production.work-centers.show', $center)
                : null,
            'queue_url' => ($user?->can('production.queue.view') && Route::has('admin.production.queue.index'))
                ? route('admin.production.queue.index', ['work_center_id' => $center->id])
                : null,
            'scheduling_url' => ($user?->can('production.scheduling.view') && Route::has('admin.production.scheduling.index'))
                ? route('admin.production.scheduling.index', ['work_center_id' => $center->id])
                : null,
        ];
    }

    /**
     * @return array{
     *     capacity: int,
     *     active_jobs: int,
     *     queue_count: int,
     *     utilization_percent: int,
     *     is_overbooked: bool,
     *     stage_name: ?string,
     *     stage_code: ?string
     * }
     */
    public function metricsForCenter(WorkCenter $center): array
    {
        return $this->metricsForCenters(collect([$center->id]))[$center->id]
            ?? $this->emptyMetrics();
    }

    /**
     * @return Collection<int, ProductionQueue>
     */
    public function activeQueuesForCenter(WorkCenter $center, int $limit = 15): Collection
    {
        return ProductionQueue::query()
            ->forTenant()
            ->where('work_center_id', $center->id)
            ->whereIn('status', $this->activeQueueStatuses())
            ->with([
                'jobCard:id,job_card_number,customer_id,status,planned_end_date',
                'jobCard.customer:id,company_name',
            ])
            ->orderBy('queue_position')
            ->limit($limit)
            ->get();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function recentCompletedForCenter(WorkCenter $center, int $limit = 10): array
    {
        return ProductionQueue::query()
            ->forTenant()
            ->where('work_center_id', $center->id)
            ->where('status', ProductionQueueStatus::Completed)
            ->with(['jobCard:id,job_card_number,customer_id', 'jobCard.customer:id,company_name'])
            ->orderByDesc('updated_at')
            ->limit($limit)
            ->get()
            ->map(fn (ProductionQueue $queue) => $this->presentQueueJobRow($queue))
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function upcomingScheduledForCenter(WorkCenter $center, int $limit = 10): array
    {
        $today = now()->toDateString();

        return ProductionJobCard::query()
            ->forTenant()
            ->whereHas('queues', fn (Builder $q) => $q->where('work_center_id', $center->id))
            ->whereNotIn('status', [
                ProductionJobCardStatus::Completed,
                ProductionJobCardStatus::ReadyForDispatch,
                ProductionJobCardStatus::Cancelled,
            ])
            ->where(function (Builder $q) use ($today) {
                $q->whereDate('planned_start_date', '>=', $today)
                    ->orWhereDate('planned_end_date', '>=', $today);
            })
            ->with(['customer:id,company_name'])
            ->orderBy('planned_start_date')
            ->limit($limit)
            ->get()
            ->map(fn (ProductionJobCard $job) => $this->presentJobRow($job))
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function delayedJobsForCenter(WorkCenter $center, int $limit = 10): array
    {
        $today = now()->toDateString();

        return ProductionJobCard::query()
            ->forTenant()
            ->whereHas('queues', fn (Builder $q) => $q
                ->where('work_center_id', $center->id)
                ->whereIn('status', $this->activeQueueStatuses()))
            ->whereNotIn('status', [
                ProductionJobCardStatus::Completed,
                ProductionJobCardStatus::ReadyForDispatch,
                ProductionJobCardStatus::Cancelled,
            ])
            ->whereDate('planned_end_date', '<', $today)
            ->with(['customer:id,company_name'])
            ->orderBy('planned_end_date')
            ->limit($limit)
            ->get()
            ->map(fn (ProductionJobCard $job) => $this->presentJobRow($job))
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function awaitingQcForCenter(WorkCenter $center, int $limit = 10): array
    {
        return ProductionJobCard::query()
            ->forTenant()
            ->where('status', ProductionJobCardStatus::QualityCheck)
            ->whereHas('queues', fn (Builder $q) => $q->where('work_center_id', $center->id))
            ->with(['customer:id,company_name'])
            ->orderBy('planned_end_date')
            ->limit($limit)
            ->get()
            ->map(fn (ProductionJobCard $job) => $this->presentJobRow($job))
            ->all();
    }

    /**
     * @return array<string, string>
     */
    public function stageCodeMap(): array
    {
        return config('production.work_center_stage_codes', []);
    }

    public function stageNameForCenter(WorkCenter $center, ?Collection $stagesByCode = null): ?string
    {
        $stageCode = $this->stageCodeForCenter($center);

        if ($stageCode === null) {
            return null;
        }

        $stagesByCode ??= $this->stagesByCode();

        return $stagesByCode->get($stageCode)?->name;
    }

    public function stageCodeForCenter(WorkCenter $center): ?string
    {
        return $this->stageCodeMap()[$center->code] ?? null;
    }

    public function defaultCapacity(): int
    {
        return max(1, (int) config('production.scheduling.default_work_center_capacity', 5));
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function executiveKpis(): array
    {
        $centers = WorkCenter::query()->forTenant()->get(['id', 'name', 'is_active']);
        $centerIds = $centers->pluck('id');
        $metrics = $this->metricsForCenters($centerIds);

        $activeJobsTotal = collect($metrics)->sum('active_jobs');
        $queuedTotal = collect($metrics)->sum('queue_count');

        $mostLoaded = collect($metrics)
            ->sortByDesc('active_jobs')
            ->keys()
            ->first();

        $mostLoadedCenter = $mostLoaded
            ? $centers->firstWhere('id', $mostLoaded)?->name ?? 'N/A'
            : 'N/A';

        $activeCount = $centers->where('is_active', true)->count();
        $inactiveCount = $centers->where('is_active', false)->count();

        return [
            [
                'label' => __('Total Work Centers'),
                'value' => (string) $centers->count(),
                'icon' => 'office-building',
                'tone' => 'indigo',
            ],
            [
                'label' => __('Active Work Centers'),
                'value' => (string) $activeCount,
                'icon' => 'check-circle',
                'tone' => 'emerald',
            ],
            [
                'label' => __('Inactive Work Centers'),
                'value' => (string) $inactiveCount,
                'icon' => 'ban',
                'tone' => 'slate',
            ],
            [
                'label' => __('Active Jobs Across Centers'),
                'value' => (string) $activeJobsTotal,
                'icon' => 'cog',
                'tone' => 'indigo',
            ],
            [
                'label' => __('Queued Jobs Across Centers'),
                'value' => (string) $queuedTotal,
                'icon' => 'switch-horizontal',
                'tone' => 'amber',
            ],
            [
                'label' => __('Most Loaded Work Center'),
                'value' => $mostLoadedCenter,
                'hint' => $mostLoaded ? __(':count active jobs', ['count' => $metrics[$mostLoaded]['active_jobs'] ?? 0]) : null,
                'icon' => 'chart-bar',
                'tone' => 'amber',
            ],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function stageMap(): array
    {
        $stages = ProductionStage::query()
            ->forTenant()
            ->orderBy('sort_order')
            ->get(['id', 'name', 'code', 'sort_order', 'is_active']);

        $stageCodeMap = $this->stageCodeMap();
        $centers = WorkCenter::query()->forTenant()->get(['id', 'name', 'code']);
        $jobCounts = $this->jobCountsByStageCode();

        return $stages->map(function (ProductionStage $stage) use ($stageCodeMap, $centers, $jobCounts) {
            $linkedCenters = $centers
                ->filter(fn (WorkCenter $center) => ($stageCodeMap[$center->code] ?? null) === $stage->code)
                ->values()
                ->map(fn (WorkCenter $center) => [
                    'id' => $center->id,
                    'name' => $center->name,
                    'code' => $center->code,
                ])
                ->all();

            return [
                'id' => $stage->id,
                'name' => $stage->name,
                'code' => $stage->code,
                'sort_order' => $stage->sort_order,
                'is_active' => $stage->is_active,
                'linked_work_centers' => $linkedCenters,
                'job_count' => (int) ($jobCounts[$stage->code] ?? 0),
            ];
        })->all();
    }

    /**
     * @return array<string, int>
     */
    protected function jobCountsByStageCode(): array
    {
        $counts = [];
        $today = now()->toDateString();
        $activeStatuses = collect($this->activeQueueStatuses())->map->value->all();
        $stageCodeMap = $this->stageCodeMap();

        $counts['PENDING'] = ProductionJobCard::query()
            ->forTenant()
            ->whereIn('status', [ProductionJobCardStatus::Draft, ProductionJobCardStatus::Queued])
            ->count();

        $counts['QC'] = ProductionJobCard::query()
            ->forTenant()
            ->where('status', ProductionJobCardStatus::QualityCheck)
            ->count();

        $counts['DISPATCH'] = ProductionJobCard::query()
            ->forTenant()
            ->whereIn('status', [ProductionJobCardStatus::ReadyForDispatch, ProductionJobCardStatus::Completed])
            ->count();

        foreach (['PREPRESS', 'PRINTING', 'FINISHING'] as $stageCode) {
            $centerIds = WorkCenter::query()
                ->forTenant()
                ->whereIn('code', collect($stageCodeMap)
                    ->filter(fn (string $code) => $code === $stageCode)
                    ->keys()
                    ->all())
                ->pluck('id');

            if ($centerIds->isEmpty()) {
                $counts[$stageCode] = 0;

                continue;
            }

            $counts[$stageCode] = ProductionQueue::query()
                ->forTenant()
                ->whereIn('work_center_id', $centerIds)
                ->whereIn('status', $activeStatuses)
                ->distinct('production_job_card_id')
                ->count('production_job_card_id');
        }

        return $counts;
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function workloadPanel(): array
    {
        $centers = WorkCenter::query()
            ->forTenant()
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'is_active']);

        if ($centers->isEmpty()) {
            return [];
        }

        $centerIds = $centers->pluck('id');
        $metrics = $this->metricsForCenters($centerIds);
        $delayedCounts = $this->delayedCountsByCenter($centerIds);
        $awaitingQcCounts = $this->awaitingQcCountsByCenter($centerIds);

        return $centers->map(function (WorkCenter $center) use ($metrics, $delayedCounts, $awaitingQcCounts) {
            $metric = $metrics[$center->id] ?? $this->emptyMetrics();
            $activeJobs = (int) ($metric['active_jobs'] ?? 0);
            $queueCount = (int) ($metric['queue_count'] ?? 0);
            $utilization = (int) ($metric['utilization_percent'] ?? 0);
            $barPercent = $utilization > 0 ? min(100, $utilization) : ($queueCount > 0 ? min(100, $queueCount * 10) : 0);

            return [
                'id' => $center->id,
                'name' => $center->name,
                'code' => $center->code,
                'is_active' => $center->is_active,
                'active_jobs' => $activeJobs,
                'queue_count' => $queueCount,
                'awaiting_qc' => (int) ($awaitingQcCounts[$center->id] ?? 0),
                'delayed_jobs' => (int) ($delayedCounts[$center->id] ?? 0),
                'capacity' => (int) ($metric['capacity'] ?? $this->defaultCapacity()),
                'utilization_percent' => $utilization,
                'is_overbooked' => (bool) ($metric['is_overbooked'] ?? false),
                'bar_percent' => $barPercent,
                'show_utilization' => true,
                'url' => Route::has('admin.production.work-centers.show')
                    ? route('admin.production.work-centers.show', $center)
                    : null,
            ];
        })->sortByDesc('active_jobs')->values()->all();
    }

    /**
     * @return array<string, mixed>
     */
    protected function bottlenecks(): array
    {
        $workload = $this->workloadPanel();

        $mostCongested = collect($workload)
            ->sortByDesc('queue_count')
            ->first();

        $withQueued = collect($workload)->filter(fn (array $row) => $row['queue_count'] > 0)->values()->all();
        $withDelayed = collect($workload)->filter(fn (array $row) => $row['delayed_jobs'] > 0)->values()->all();
        $idle = collect($workload)->filter(fn (array $row) => $row['active_jobs'] === 0 && $row['queue_count'] === 0)->values()->all();

        return [
            'most_congested' => $mostCongested && ($mostCongested['queue_count'] ?? 0) > 0 ? $mostCongested : null,
            'with_queued_jobs' => $withQueued,
            'with_delayed_jobs' => $withDelayed,
            'idle_centers' => $idle,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function filterOptions(): array
    {
        return [
            'stages' => ProductionStage::query()
                ->forTenant()
                ->orderBy('sort_order')
                ->get(['id', 'name', 'code']),
            'load_options' => [
                'has_active_jobs' => __('Has active jobs'),
                'has_queued_jobs' => __('Has queued jobs'),
                'idle' => __('Idle'),
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return list<array<string, string>>
     */
    protected function activeFilterChips(array $filters): array
    {
        $chips = [];
        $indexUrl = route('admin.production.work-centers.index');

        foreach (['search', 'stage_id', 'status', 'load'] as $key) {
            if (! filled($filters[$key] ?? null)) {
                continue;
            }

            $label = match ($key) {
                'search' => __('Search').': '.$filters[$key],
                'stage_id' => __('Stage').' #'.$filters[$key],
                'status' => ucfirst((string) $filters[$key]),
                'load' => str((string) $filters[$key])->replace('_', ' ')->headline()->toString(),
                default => (string) $filters[$key],
            };

            $query = array_filter($filters, fn ($value, $filterKey) => filled($value) && $filterKey !== $key, ARRAY_FILTER_USE_BOTH);

            $chips[] = [
                'label' => $label,
                'url' => $indexUrl.'?'.http_build_query($query),
            ];
        }

        return $chips;
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    protected function hasActiveFilters(array $filters): bool
    {
        return filled($filters['search'] ?? null)
            || filled($filters['stage_id'] ?? null)
            || filled($filters['status'] ?? null)
            || filled($filters['load'] ?? null);
    }

    protected function filteredQuery(Request $request): Builder
    {
        $query = WorkCenter::query()->forTenant();
        $filters = $this->filtersFromRequest($request);

        if ($search = trim((string) ($filters['search'] ?? ''))) {
            $like = '%'.addcslashes($search, '%_\\').'%';
            $query->where(function (Builder $inner) use ($like) {
                $inner->where('name', 'like', $like)
                    ->orWhere('code', 'like', $like)
                    ->orWhere('description', 'like', $like);
            });
        }

        if (($filters['status'] ?? '') === 'active') {
            $query->where('is_active', true);
        } elseif (($filters['status'] ?? '') === 'inactive') {
            $query->where('is_active', false);
        }

        if ($stageId = (int) ($filters['stage_id'] ?? 0)) {
            $stage = ProductionStage::query()->forTenant()->find($stageId);

            if ($stage) {
                $matchingCodes = collect($this->stageCodeMap())
                    ->filter(fn (string $stageCode) => $stageCode === $stage->code)
                    ->keys()
                    ->all();

                if ($matchingCodes === []) {
                    $query->whereRaw('1 = 0');
                } else {
                    $query->whereIn('code', $matchingCodes);
                }
            }
        }

        return $query;
    }

    /**
     * @return list<int>
     */
    protected function centerIdsMatchingLoad(string $load): array
    {
        $centers = WorkCenter::query()->forTenant()->pluck('id');
        $metrics = $this->metricsForCenters($centers);

        return collect($metrics)
            ->filter(function (array $metric) use ($load) {
                return match ($load) {
                    'has_active_jobs' => ($metric['active_jobs'] ?? 0) > 0,
                    'has_queued_jobs' => ($metric['queue_count'] ?? 0) > 0,
                    'idle' => ($metric['active_jobs'] ?? 0) === 0 && ($metric['queue_count'] ?? 0) === 0,
                    default => true,
                };
            })
            ->keys()
            ->all();
    }

    /**
     * @param  Collection<int, int>  $centerIds
     * @return array<int, array{
     *     capacity: int,
     *     active_jobs: int,
     *     queue_count: int,
     *     utilization_percent: int,
     *     is_overbooked: bool,
     *     stage_name: ?string,
     *     stage_code: ?string
     * }>
     */
    protected function metricsForCenters(Collection $centerIds): array
    {
        if ($centerIds->isEmpty()) {
            return [];
        }

        $capacity = $this->defaultCapacity();
        $stagesByCode = $this->stagesByCode();
        $activeStatuses = collect($this->activeQueueStatuses())->map->value->all();

        $queueCounts = ProductionQueue::query()
            ->forTenant()
            ->select('work_center_id', DB::raw('COUNT(*) as queue_count'))
            ->whereIn('work_center_id', $centerIds)
            ->whereIn('status', $activeStatuses)
            ->groupBy('work_center_id')
            ->pluck('queue_count', 'work_center_id');

        $activeJobs = ProductionQueue::query()
            ->forTenant()
            ->select('work_center_id', DB::raw('COUNT(DISTINCT production_job_card_id) as active_jobs'))
            ->whereIn('work_center_id', $centerIds)
            ->whereIn('status', $activeStatuses)
            ->groupBy('work_center_id')
            ->pluck('active_jobs', 'work_center_id');

        $centers = WorkCenter::query()
            ->whereIn('id', $centerIds)
            ->get(['id', 'code']);

        $metrics = [];

        foreach ($centers as $center) {
            $jobs = (int) ($activeJobs[$center->id] ?? 0);
            $queues = (int) ($queueCounts[$center->id] ?? 0);
            $utilization = $capacity > 0 ? (int) round(($jobs / $capacity) * 100) : 0;
            $stageCode = $this->stageCodeForCenter($center);

            $metrics[$center->id] = [
                'capacity' => $capacity,
                'active_jobs' => $jobs,
                'queue_count' => $queues,
                'utilization_percent' => min(999, $utilization),
                'is_overbooked' => $jobs > $capacity,
                'stage_name' => $stageCode ? $stagesByCode->get($stageCode)?->name : null,
                'stage_code' => $stageCode,
            ];
        }

        return $metrics;
    }

    /**
     * @param  Collection<int, int>  $centerIds
     * @return array<int, ?string>
     */
    protected function lastActivityForCenters(Collection $centerIds): array
    {
        if ($centerIds->isEmpty()) {
            return [];
        }

        return ProductionQueue::query()
            ->forTenant()
            ->select('work_center_id', DB::raw('MAX(updated_at) as last_activity'))
            ->whereIn('work_center_id', $centerIds)
            ->groupBy('work_center_id')
            ->pluck('last_activity', 'work_center_id')
            ->map(fn ($value) => $value ? (string) $value : null)
            ->all();
    }

    protected function lastActivityForCenter(WorkCenter $center): ?string
    {
        $timestamp = ProductionQueue::query()
            ->forTenant()
            ->where('work_center_id', $center->id)
            ->max('updated_at');

        return $timestamp ? (string) $timestamp : null;
    }

    /**
     * @param  Collection<int, int>  $centerIds
     * @return array<int, int>
     */
    protected function delayedCountsByCenter(Collection $centerIds): array
    {
        $today = now()->toDateString();
        $activeStatuses = collect($this->activeQueueStatuses())->map->value->all();

        return ProductionQueue::query()
            ->forTenant()
            ->join('production_job_cards', 'production_job_cards.id', '=', 'production_queues.production_job_card_id')
            ->select('production_queues.work_center_id', DB::raw('COUNT(DISTINCT production_queues.production_job_card_id) as delayed_count'))
            ->whereIn('production_queues.work_center_id', $centerIds)
            ->whereIn('production_queues.status', $activeStatuses)
            ->whereNotIn('production_job_cards.status', [
                ProductionJobCardStatus::Completed->value,
                ProductionJobCardStatus::ReadyForDispatch->value,
                ProductionJobCardStatus::Cancelled->value,
            ])
            ->whereDate('production_job_cards.planned_end_date', '<', $today)
            ->groupBy('production_queues.work_center_id')
            ->pluck('delayed_count', 'work_center_id')
            ->map(fn ($count) => (int) $count)
            ->all();
    }

    /**
     * @param  Collection<int, int>  $centerIds
     * @return array<int, int>
     */
    protected function awaitingQcCountsByCenter(Collection $centerIds): array
    {
        return ProductionQueue::query()
            ->forTenant()
            ->join('production_job_cards', 'production_job_cards.id', '=', 'production_queues.production_job_card_id')
            ->select('production_queues.work_center_id', DB::raw('COUNT(DISTINCT production_queues.production_job_card_id) as qc_count'))
            ->whereIn('production_queues.work_center_id', $centerIds)
            ->where('production_job_cards.status', ProductionJobCardStatus::QualityCheck->value)
            ->groupBy('production_queues.work_center_id')
            ->pluck('qc_count', 'work_center_id')
            ->map(fn ($count) => (int) $count)
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    protected function presentQueueJobRow(ProductionQueue $queue): array
    {
        $job = $queue->jobCard;
        $user = auth()->user();

        return [
            'job_number' => $job?->job_card_number ?? '—',
            'customer' => $job?->customer?->company_name ?? '—',
            'queue_position' => $queue->queue_position,
            'status' => $queue->status->value,
            'updated_at' => $queue->updated_at?->format('Y-m-d H:i') ?? '—',
            'job_360_url' => ($job && $user?->can('production.view') && Route::has('admin.production.job-cards.show'))
                ? route('admin.production.job-cards.show', $job)
                : null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function presentJobRow(ProductionJobCard $job): array
    {
        $user = auth()->user();

        return [
            'job_number' => $job->job_card_number,
            'customer' => $job->customer?->company_name ?? '—',
            'status' => str_replace('_', ' ', $job->status->value),
            'planned_start' => $job->planned_start_date?->format('Y-m-d') ?? '—',
            'planned_end' => $job->planned_end_date?->format('Y-m-d') ?? '—',
            'is_delayed' => $job->isDelayed(),
            'job_360_url' => ($user?->can('production.view') && Route::has('admin.production.job-cards.show'))
                ? route('admin.production.job-cards.show', $job)
                : null,
        ];
    }

    /**
     * @return array{
     *     capacity: int,
     *     active_jobs: int,
     *     queue_count: int,
     *     utilization_percent: int,
     *     is_overbooked: bool,
     *     stage_name: ?string,
     *     stage_code: ?string
     * }
     */
    protected function emptyMetrics(): array
    {
        return [
            'capacity' => $this->defaultCapacity(),
            'active_jobs' => 0,
            'queue_count' => 0,
            'utilization_percent' => 0,
            'is_overbooked' => false,
            'stage_name' => null,
            'stage_code' => null,
        ];
    }

    /**
     * @return Collection<string, ProductionStage>
     */
    protected function stagesByCode(): Collection
    {
        return ProductionStage::query()
            ->forTenant()
            ->orderBy('sort_order')
            ->get(['id', 'name', 'code'])
            ->keyBy('code');
    }

    /**
     * @return list<ProductionQueueStatus>
     */
    protected function activeQueueStatuses(): array
    {
        return [
            ProductionQueueStatus::Pending,
            ProductionQueueStatus::Assigned,
            ProductionQueueStatus::InProgress,
        ];
    }
}
