<?php

namespace App\Services\Production;

use App\Enums\ProductionJobCardStatus;
use App\Enums\ProductionPriority;
use App\Enums\ProductionQueueStatus;
use App\Models\Production\ProductionJobCard;
use App\Models\Production\ProductionQueue;
use App\Models\Production\WorkCenter;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class ProductionSchedulingWorkspaceService
{
    /**
     * @return array{
     *     scheduled: int,
     *     unscheduled: int,
     *     overdue: int,
     *     upcoming: int
     * }
     */
    public function kpiCounts(): array
    {
        $base = $this->schedulableQuery();
        $today = now()->toDateString();
        $upcomingUntil = now()->addDays(14)->toDateString();

        return [
            'scheduled' => (clone $base)
                ->whereNotNull('planned_start_date')
                ->whereNotNull('planned_end_date')
                ->count(),
            'unscheduled' => (clone $base)
                ->where(fn (Builder $q) => $q
                    ->whereNull('planned_start_date')
                    ->orWhereNull('planned_end_date'))
                ->count(),
            'overdue' => (clone $base)
                ->whereDate('planned_end_date', '<', $today)
                ->whereNotIn('status', $this->terminalStatuses())
                ->count(),
            'upcoming' => (clone $base)
                ->whereDate('planned_start_date', '>', $today)
                ->whereDate('planned_start_date', '<=', $upcomingUntil)
                ->count(),
        ];
    }

    public function paginatedIndex(Request $request): LengthAwarePaginator
    {
        return $this->filteredQuery($request)
            ->with($this->listRelations())
            ->orderByRaw('planned_start_date IS NULL')
            ->orderBy('planned_start_date')
            ->orderBy('job_card_number')
            ->paginate(15)
            ->withQueryString();
    }

    /**
     * @return array{
     *     month: string,
     *     label: string,
     *     prev_month: string,
     *     next_month: string,
     *     weeks: list<list<array{date: string, in_month: bool, jobs: list<array<string, mixed>>}>>
     * }
     */
    public function calendarMonth(Request $request): array
    {
        $month = $this->resolveMonth($request->query('month'));
        $start = $month->copy()->startOfMonth();
        $end = $month->copy()->endOfMonth();
        $gridStart = $start->copy()->startOfWeek(Carbon::MONDAY);
        $gridEnd = $end->copy()->endOfWeek(Carbon::MONDAY);

        $jobs = $this->filteredQuery($request)
            ->with($this->listRelations())
            ->whereNotNull('planned_start_date')
            ->where(function (Builder $q) use ($start, $end) {
                $q->whereBetween('planned_start_date', [$start->toDateString(), $end->toDateString()])
                    ->orWhereBetween('planned_end_date', [$start->toDateString(), $end->toDateString()])
                    ->orWhere(function (Builder $inner) use ($start, $end) {
                        $inner->whereDate('planned_start_date', '<=', $start)
                            ->whereDate('planned_end_date', '>=', $end);
                    });
            })
            ->orderBy('planned_start_date')
            ->get();

        $weeks = [];
        $cursor = $gridStart->copy();

        while ($cursor->lte($gridEnd)) {
            $week = [];

            for ($i = 0; $i < 7; $i++) {
                $date = $cursor->copy();
                $week[] = [
                    'date' => $date->toDateString(),
                    'label' => $date->format('j'),
                    'in_month' => $date->month === $start->month,
                    'is_today' => $date->isToday(),
                    'jobs' => $this->jobsForCalendarDay($jobs, $date),
                ];
                $cursor->addDay();
            }

            $weeks[] = $week;
        }

        return [
            'month' => $month->format('Y-m'),
            'label' => $month->format('F Y'),
            'prev_month' => $month->copy()->subMonth()->format('Y-m'),
            'next_month' => $month->copy()->addMonth()->format('Y-m'),
            'weeks' => $weeks,
        ];
    }

    /**
     * @return array{
     *     centers: list<array{
     *         id: int,
     *         name: string,
     *         code: string,
     *         assigned_jobs: int,
     *         capacity: int,
     *         utilization_percent: int,
     *         is_overbooked: bool
     *     }>,
     *     default_capacity: int,
     *     overbooked_count: int
     * }
     */
    public function workCenterLoadPanel(): array
    {
        $capacity = $this->defaultWorkCenterCapacity();
        $centers = WorkCenter::query()
            ->forTenant()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        $loads = [];

        foreach ($centers as $center) {
            $assignedJobs = ProductionQueue::query()
                ->forTenant()
                ->where('work_center_id', $center->id)
                ->whereIn('status', $this->activeQueueStatuses())
                ->distinct()
                ->count('production_job_card_id');

            $utilization = $capacity > 0
                ? (int) round(($assignedJobs / $capacity) * 100)
                : 0;

            $loads[] = [
                'id' => $center->id,
                'name' => $center->name,
                'code' => $center->code,
                'assigned_jobs' => $assignedJobs,
                'capacity' => $capacity,
                'utilization_percent' => min(999, $utilization),
                'is_overbooked' => $assignedJobs > $capacity,
            ];
        }

        usort($loads, fn (array $a, array $b) => $b['utilization_percent'] <=> $a['utilization_percent']);

        return [
            'centers' => $loads,
            'default_capacity' => $capacity,
            'overbooked_count' => collect($loads)->where('is_overbooked', true)->count(),
        ];
    }

    /**
     * @return array{
     *     late_jobs: list<array<string, mixed>>,
     *     capacity_conflicts: list<array<string, mixed>>,
     *     missing_schedule_dates: list<array<string, mixed>>,
     *     counts: array{late: int, conflicts: int, missing_dates: int, total: int}
     * }
     */
    public function schedulingWarnings(): array
    {
        $lateJobs = $this->lateJobWarnings();
        $conflicts = $this->capacityConflictWarnings();
        $missingDates = $this->missingScheduleDateWarnings();

        return [
            'late_jobs' => $lateJobs,
            'capacity_conflicts' => $conflicts,
            'missing_schedule_dates' => $missingDates,
            'counts' => [
                'late' => count($lateJobs),
                'conflicts' => count($conflicts),
                'missing_dates' => count($missingDates),
                'total' => count($lateJobs) + count($conflicts) + count($missingDates),
            ],
        ];
    }

    /**
     * @return list<string>
     */
    public function workCenterNames(ProductionJobCard $jobCard): array
    {
        return $jobCard->queues
            ->pluck('workCenter.name')
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    protected function filteredQuery(Request $request): Builder
    {
        $query = $this->schedulableQuery();
        $this->applyFilters($query, $request);

        return $query;
    }

    protected function schedulableQuery(): Builder
    {
        return ProductionJobCard::query()
            ->forTenant()
            ->where('status', '!=', ProductionJobCardStatus::Cancelled);
    }

    protected function applyFilters(Builder $query, Request $request): void
    {
        if ($status = ProductionJobCardStatus::tryFrom((string) $request->query('status', ''))) {
            $query->where('status', $status);
        }

        if ($workCenterId = $request->integer('work_center_id')) {
            $query->whereHas('queues', fn (Builder $q) => $q->where('work_center_id', $workCenterId));
        }

        if ($priority = ProductionPriority::tryFrom((string) $request->query('priority', ''))) {
            $query->where('priority', $priority);
        }

        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');
        if ($dateFrom && $dateTo) {
            $query->where(function (Builder $q) use ($dateFrom, $dateTo) {
                $q->where(function (Builder $inner) use ($dateFrom, $dateTo) {
                    $inner->whereDate('planned_start_date', '<=', $dateTo)
                        ->where(function (Builder $range) use ($dateFrom) {
                            $range->whereDate('planned_end_date', '>=', $dateFrom)
                                ->orWhereNull('planned_end_date');
                        });
                })->orWhereBetween('planned_start_date', [$dateFrom, $dateTo]);
            });
        } elseif ($dateFrom) {
            $query->where(function (Builder $q) use ($dateFrom) {
                $q->whereDate('planned_end_date', '>=', $dateFrom)
                    ->orWhereDate('planned_start_date', '>=', $dateFrom);
            });
        } elseif ($dateTo) {
            $query->where(function (Builder $q) use ($dateTo) {
                $q->whereDate('planned_start_date', '<=', $dateTo)
                    ->orWhereNull('planned_start_date');
            });
        } elseif ($date = $request->query('date')) {
            $query->where(function (Builder $q) use ($date) {
                $q->whereDate('planned_start_date', '<=', $date)
                    ->where(function (Builder $inner) use ($date) {
                        $inner->whereDate('planned_end_date', '>=', $date)
                            ->orWhereNull('planned_end_date');
                    })
                    ->orWhereDate('planned_start_date', $date);
            });
        }

        if ($search = trim((string) $request->query('search', ''))) {
            $like = '%'.addcslashes($search, '%_\\').'%';
            $query->where(function (Builder $q) use ($like) {
                $q->where('job_card_number', 'like', $like)
                    ->orWhereHas('customer', fn (Builder $c) => $c->where('company_name', 'like', $like));
            });
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function listRelations(): array
    {
        return [
            'customer:id,company_name',
            'salesOrder:id,required_date',
            'queues.workCenter:id,name',
        ];
    }

    /**
     * @param  Collection<int, ProductionJobCard>  $jobs
     * @return list<array{id: int, job_number: string, customer: string, status: string, span: string}>
     */
    protected function jobsForCalendarDay(Collection $jobs, Carbon $day): array
    {
        $date = $day->toDateString();
        $items = [];

        foreach ($jobs as $job) {
            if (! $this->jobSpansDate($job, $date)) {
                continue;
            }

            $items[] = [
                'id' => $job->id,
                'job_number' => $job->job_card_number,
                'customer' => $job->customer?->company_name ?? '—',
                'status' => $job->status->value,
                'span' => $this->calendarSpanLabel($job, $day),
            ];
        }

        return $items;
    }

    protected function jobSpansDate(ProductionJobCard $job, string $date): bool
    {
        $start = $job->planned_start_date?->toDateString();
        $end = $job->planned_end_date?->toDateString() ?? $start;

        if ($start === null) {
            return false;
        }

        return $date >= $start && $date <= ($end ?? $start);
    }

    protected function calendarSpanLabel(ProductionJobCard $job, Carbon $day): string
    {
        $start = $job->planned_start_date;
        $end = $job->planned_end_date ?? $start;

        if ($start && $day->isSameDay($start)) {
            return 'start';
        }

        if ($end && $day->isSameDay($end)) {
            return 'end';
        }

        return 'span';
    }

    protected function resolveMonth(?string $month): Carbon
    {
        if ($month && preg_match('/^\d{4}-\d{2}$/', $month)) {
            return Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        }

        return now()->startOfMonth();
    }

    /**
     * @return list<ProductionJobCardStatus>
     */
    protected function terminalStatuses(): array
    {
        return [
            ProductionJobCardStatus::Completed,
            ProductionJobCardStatus::ReadyForDispatch,
            ProductionJobCardStatus::Cancelled,
        ];
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

    protected function defaultWorkCenterCapacity(): int
    {
        return max(1, (int) config('production.scheduling.default_work_center_capacity', 5));
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function lateJobWarnings(int $limit = 12): array
    {
        $today = now()->toDateString();

        return $this->schedulableQuery()
            ->whereDate('planned_end_date', '<', $today)
            ->whereNotIn('status', $this->terminalStatuses())
            ->with(['customer:id,company_name', 'queues.workCenter:id,name'])
            ->orderBy('planned_end_date')
            ->limit($limit)
            ->get()
            ->map(fn (ProductionJobCard $job) => [
                'job_id' => $job->id,
                'job_number' => $job->job_card_number,
                'customer' => $job->customer?->company_name ?? '—',
                'due_date' => $job->planned_end_date?->format('M j, Y') ?? '—',
                'work_centers' => $this->workCenterNames($job),
                'days_late' => (int) $job->planned_end_date?->diffInDays(now()),
            ])
            ->values()
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function missingScheduleDateWarnings(int $limit = 12): array
    {
        return $this->schedulableQuery()
            ->where(fn (Builder $q) => $q
                ->whereNull('planned_start_date')
                ->orWhereNull('planned_end_date'))
            ->with(['customer:id,company_name', 'queues.workCenter:id,name'])
            ->orderBy('job_card_number')
            ->limit($limit)
            ->get()
            ->map(function (ProductionJobCard $job) {
                $missing = array_values(array_filter([
                    $job->planned_start_date === null ? __('start date') : null,
                    $job->planned_end_date === null ? __('end date') : null,
                ]));

                return [
                    'job_id' => $job->id,
                    'job_number' => $job->job_card_number,
                    'customer' => $job->customer?->company_name ?? '—',
                    'missing' => implode(', ', $missing),
                    'has_queue' => $job->queues->isNotEmpty(),
                    'work_centers' => $this->workCenterNames($job),
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function capacityConflictWarnings(int $limit = 12): array
    {
        $queues = ProductionQueue::query()
            ->forTenant()
            ->whereIn('status', $this->activeQueueStatuses())
            ->with([
                'workCenter:id,name',
                'jobCard' => fn ($q) => $q->select([
                    'id', 'job_card_number', 'customer_id', 'planned_start_date', 'planned_end_date', 'status',
                ])->with('customer:id,company_name'),
            ])
            ->whereHas('jobCard', function (Builder $q) {
                $q->whereNotNull('planned_start_date')
                    ->whereNotNull('planned_end_date')
                    ->whereNotIn('status', $this->terminalStatuses());
            })
            ->get();

        $conflicts = [];
        $seen = [];

        foreach ($queues->groupBy('work_center_id') as $centerQueues) {
            $jobs = $centerQueues
                ->map(fn (ProductionQueue $queue) => $queue->jobCard)
                ->filter()
                ->unique('id')
                ->values();

            $centerName = $centerQueues->first()?->workCenter?->name ?? __('Unknown');

            for ($i = 0; $i < $jobs->count(); $i++) {
                for ($j = $i + 1; $j < $jobs->count(); $j++) {
                    $jobA = $jobs[$i];
                    $jobB = $jobs[$j];

                    if (! $this->plannedRangesOverlap($jobA, $jobB)) {
                        continue;
                    }

                    $pairKey = $centerQueues->first()->work_center_id.'-'.min($jobA->id, $jobB->id).'-'.max($jobA->id, $jobB->id);

                    if (isset($seen[$pairKey])) {
                        continue;
                    }

                    $seen[$pairKey] = true;
                    $conflicts[] = [
                        'work_center' => $centerName,
                        'job_a_id' => $jobA->id,
                        'job_a_number' => $jobA->job_card_number,
                        'job_b_id' => $jobB->id,
                        'job_b_number' => $jobB->job_card_number,
                        'overlap_start' => max(
                            $jobA->planned_start_date->toDateString(),
                            $jobB->planned_start_date->toDateString(),
                        ),
                        'overlap_end' => min(
                            $jobA->planned_end_date->toDateString(),
                            $jobB->planned_end_date->toDateString(),
                        ),
                    ];

                    if (count($conflicts) >= $limit) {
                        return $conflicts;
                    }
                }
            }
        }

        return $conflicts;
    }

    protected function plannedRangesOverlap(ProductionJobCard $a, ProductionJobCard $b): bool
    {
        if (! $a->planned_start_date || ! $a->planned_end_date || ! $b->planned_start_date || ! $b->planned_end_date) {
            return false;
        }

        return $a->planned_start_date->toDateString() <= $b->planned_end_date->toDateString()
            && $b->planned_start_date->toDateString() <= $a->planned_end_date->toDateString();
    }
}
