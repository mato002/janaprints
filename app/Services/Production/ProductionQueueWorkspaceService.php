<?php

namespace App\Services\Production;

use App\Enums\ProductionQueueStatus;
use App\Models\Production\ProductionJobCard;
use App\Models\Production\ProductionQueue;
use App\Models\Production\WorkCenter;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;

class ProductionQueueWorkspaceService
{
    /**
     * @return array{
     *     pending: int,
     *     assigned: int,
     *     in_progress: int,
     *     active: int
     * }
     */
    public function kpiCounts(): array
    {
        $base = ProductionQueue::query()->forTenant();

        return [
            'pending' => (clone $base)->where('status', ProductionQueueStatus::Pending)->count(),
            'assigned' => (clone $base)->where('status', ProductionQueueStatus::Assigned)->count(),
            'in_progress' => (clone $base)->where('status', ProductionQueueStatus::InProgress)->count(),
            'active' => (clone $base)->whereIn('status', $this->activeQueueStatuses())->count(),
        ];
    }

    public function paginatedIndex(Request $request): LengthAwarePaginator
    {
        return $this->filteredQuery($request)
            ->with([
                'jobCard:id,job_card_number,customer_id,status,planned_end_date,priority',
                'jobCard.customer:id,company_name',
                'workCenter:id,name,code',
                'assignedOperator:id,name',
            ])
            ->orderBy('work_center_id')
            ->orderBy('queue_position')
            ->orderBy('id')
            ->paginate(20)
            ->withQueryString();
    }

    /**
     * @return array<string, mixed>
     */
    public function bottlenecks(): array
    {
        $capacity = $this->defaultCapacity();
        $centers = WorkCenter::query()
            ->forTenant()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        $loads = $centers->map(function (WorkCenter $center) use ($capacity) {
            $queueCount = ProductionQueue::query()
                ->forTenant()
                ->where('work_center_id', $center->id)
                ->whereIn('status', $this->activeQueueStatuses())
                ->count();

            $delayedJobs = ProductionQueue::query()
                ->forTenant()
                ->where('work_center_id', $center->id)
                ->whereIn('status', $this->activeQueueStatuses())
                ->whereHas('jobCard', fn (Builder $q) => $q->whereDate('planned_end_date', '<', now()->toDateString()))
                ->distinct()
                ->count('production_job_card_id');

            return [
                'id' => $center->id,
                'name' => $center->name,
                'code' => $center->code,
                'queue_count' => $queueCount,
                'delayed_jobs' => $delayedJobs,
                'capacity' => $capacity,
                'is_overbooked' => $queueCount > $capacity,
                'active_jobs' => $queueCount,
            ];
        });

        $mostCongested = $loads->sortByDesc('queue_count')->first();

        return [
            'most_congested' => $mostCongested && $mostCongested['queue_count'] > 0 ? $mostCongested : null,
            'with_queued_jobs' => $loads->filter(fn (array $row) => $row['queue_count'] > 0)->sortByDesc('queue_count')->values()->all(),
            'with_delayed_jobs' => $loads->filter(fn (array $row) => $row['delayed_jobs'] > 0)->sortByDesc('delayed_jobs')->values()->all(),
            'idle_centers' => $loads->filter(fn (array $row) => $row['queue_count'] === 0)->values()->all(),
            'overbooked' => $loads->filter(fn (array $row) => $row['is_overbooked'])->values()->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function presentRow(ProductionQueue $queue, ?User $user = null): array
    {
        $user ??= auth()->user();
        $jobCard = $queue->jobCard;

        $job360Url = null;
        if ($user?->can('production.view') && Route::has('admin.production.job-cards.show') && $jobCard) {
            $job360Url = route('admin.production.job-cards.show', $jobCard);
        }

        return [
            'id' => $queue->id,
            'queue_position' => $queue->queue_position,
            'status' => $queue->status,
            'status_label' => $this->statusLabel($queue->status),
            'job_card_number' => $jobCard?->job_card_number ?? '—',
            'customer_name' => $jobCard?->customer?->company_name ?? '—',
            'work_center_name' => $queue->workCenter?->name ?? '—',
            'operator_name' => $queue->assignedOperator?->name ?? '—',
            'is_delayed' => $jobCard?->isDelayed() ?? false,
            'job_360_url' => $job360Url,
            'work_center_url' => ($user?->can('production.work-centers.view') && Route::has('admin.production.work-centers.show') && $queue->work_center_id)
                ? route('admin.production.work-centers.show', $queue->work_center_id)
                : null,
        ];
    }

    public function statusLabel(ProductionQueueStatus $status): string
    {
        return match ($status) {
            ProductionQueueStatus::Pending => __('Pending'),
            ProductionQueueStatus::Assigned => __('Assigned'),
            ProductionQueueStatus::InProgress => __('In progress'),
            ProductionQueueStatus::Completed => __('Completed'),
            ProductionQueueStatus::Cancelled => __('Cancelled'),
        };
    }

    protected function filteredQuery(Request $request): Builder
    {
        $query = ProductionQueue::query()->forTenant();

        if ($status = ProductionQueueStatus::tryFromFilter($request->query('status'))) {
            $query->where('status', $status);
        } elseif (filled($request->query('status')) && $request->query('status') !== 'blocked') {
            $raw = ProductionQueueStatus::tryFrom((string) $request->query('status'));
            if ($raw) {
                $query->where('status', $raw);
            }
        }

        if ($request->query('status') === 'blocked') {
            $query->where('status', ProductionQueueStatus::Pending)
                ->whereNull('assigned_operator_id');
        }

        if ($workCenterId = $request->query('work_center_id')) {
            $query->where('work_center_id', (int) $workCenterId);
        }

        if ($stageId = $request->query('stage_id')) {
            $query->whereHas('jobCard.operations', fn (Builder $q) => $q->where('production_stage_id', (int) $stageId));
        }

        if ($date = $request->query('date')) {
            $query->whereDate('updated_at', $date);
        }

        if ($search = trim((string) $request->query('search'))) {
            $like = '%'.addcslashes($search, '%_\\').'%';
            $query->whereHas('jobCard', function (Builder $q) use ($like) {
                $q->where('job_card_number', 'like', $like)
                    ->orWhereHas('customer', fn (Builder $c) => $c->where('company_name', 'like', $like));
            });
        }

        return $query;
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

    protected function defaultCapacity(): int
    {
        return max(1, (int) config('production.scheduling.default_work_center_capacity', 5));
    }
}
