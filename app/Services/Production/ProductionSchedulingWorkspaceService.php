<?php

namespace App\Services\Production;

use App\Enums\ProductionJobCardStatus;
use App\Enums\ProductionPriority;
use App\Enums\ProductionQueueStatus;
use App\Models\Production\ProductionJobCard;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;

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
            ->latest('created_at')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();
    }

    /**
     * @return Collection<int, ProductionJobCard>
     */
    public function exportIndex(Request $request): Collection
    {
        return $this->filteredQuery($request)
            ->with($this->listRelations())
            ->latest('created_at')
            ->latest('id')
            ->get();
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
            'customer:id,public_id,company_name',
            'salesOrder:id,public_id,required_date',
            'queues.workCenter:id,public_id,name',
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
                'public_id' => $job->public_id,
                'url' => Route::has('admin.production.job-cards.show')
                    ? route('admin.production.job-cards.show', $job)
                    : null,
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
            ProductionQueueStatus::Waiting,
            ProductionQueueStatus::Assigned,
            ProductionQueueStatus::InProgress,
        ];
    }

}
