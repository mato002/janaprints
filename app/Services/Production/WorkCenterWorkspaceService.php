<?php

namespace App\Services\Production;

use App\Enums\ProductionQueueStatus;
use App\Models\Production\ProductionQueue;
use App\Models\Production\ProductionStage;
use App\Models\Production\WorkCenter;
use App\Models\User;
use App\Services\Assets\MachineAvailabilityService;
use App\Services\Assets\MachineCapacityService;
use App\Services\Assets\MachineQueueReadinessService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

class WorkCenterWorkspaceService
{
    public function __construct(
        protected MachineCapacityService $machineCapacity,
        protected MachineAvailabilityService $machineAvailability,
        protected MachineQueueReadinessService $machineQueueReadiness,
    ) {}
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
            'work_centers' => $this->paginatedIndex($request),
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
        $workCenter->loadMissing([
            'machineAsset.machineProfile',
            'machineAsset:id,asset_name,asset_number',
        ]);
        $metrics = $this->metricsForCenter($workCenter);

        return [
            'metrics' => $metrics,
            'machine' => $this->machinePanel($workCenter),
            'active_queues' => $this->activeQueuesForCenter($workCenter),
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
    public function metricsForCenters(Collection $centerIds): array
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
            ProductionQueueStatus::Waiting,
            ProductionQueueStatus::Assigned,
            ProductionQueueStatus::InProgress,
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function machinePanel(WorkCenter $workCenter): ?array
    {
        $profile = $workCenter->machineAsset?->machineProfile;

        if (! $profile) {
            return null;
        }

        $capacity = $this->machineCapacity->profileMetrics($profile);
        $availability = $this->machineAvailability->evaluate($profile);
        $queue = $this->machineQueueReadiness->readiness($profile);

        return [
            'asset_id' => $workCenter->fixed_asset_id,
            'name' => $workCenter->machineAsset?->asset_name,
            'code' => $profile->machine_code,
            'status' => $profile->production_status->label(),
            'status_variant' => $profile->production_status->badgeVariant(),
            'capacity' => $capacity,
            'availability' => $availability,
            'queue_readiness' => $queue,
            'url' => Route::has('admin.assets.machines.show')
                ? route('admin.assets.machines.show', $workCenter->fixed_asset_id)
                : null,
        ];
    }
}
