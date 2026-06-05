<?php

namespace App\Services\Production;

use App\Enums\ProductionQueueStatus;
use App\Models\Production\ProductionQueue;
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
     *     queued: int,
     *     assigned: int,
     *     in_progress: int,
     *     blocked: int
     * }
     */
    public function kpiCounts(): array
    {
        $base = ProductionQueue::query()->forTenant();

        return [
            'queued' => (clone $base)->where('status', ProductionQueueStatus::Pending)->count(),
            'assigned' => (clone $base)->where('status', ProductionQueueStatus::Assigned)->count(),
            'in_progress' => (clone $base)->where('status', ProductionQueueStatus::InProgress)->count(),
            'blocked' => (clone $base)
                ->where('status', ProductionQueueStatus::Pending)
                ->whereNull('assigned_operator_id')
                ->count(),
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

        if ($request->query('operator_id') === 'unassigned') {
            $query->whereNull('assigned_operator_id');
        } elseif ($operatorId = $request->integer('operator_id')) {
            $query->where('assigned_operator_id', $operatorId);
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
}
