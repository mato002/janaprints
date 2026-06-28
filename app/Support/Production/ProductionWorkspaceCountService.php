<?php

namespace App\Support\Production;

use App\Enums\Dispatch\DeliveryNoteStatus;
use App\Enums\ProductionJobCardStatus;
use App\Enums\ProductionQueueStatus;
use App\Models\Dispatch\DeliveryNote;
use App\Models\Production\JobCostSheet;
use App\Models\Production\ProductionJobCard;
use App\Models\Production\ProductionQueue;
use App\Models\Production\WorkCenter;
use App\Support\Platform\PlatformCacheService;
use App\Support\Reports\IntelligenceAggregateQueries;
use App\Support\Reports\IntelligenceScope;
use Illuminate\Support\Facades\Schema;

class ProductionWorkspaceCountService
{
    public function __construct(
        protected IntelligenceAggregateQueries $queries,
        protected PlatformCacheService $cache,
    ) {}

    public function resolve(?string $countKey): ?int
    {
        if ($countKey === null || $countKey === '') {
            return null;
        }

        $companyId = tenant()->companyId() ?? auth()->user()?->company_id;
        $branchId = tenant()->branchId();

        if (! $companyId) {
            return null;
        }

        $cacheKey = $branchId ? "{$companyId}:{$branchId}:{$countKey}" : "{$companyId}:all:{$countKey}";

        return $this->cache->remember(
            'production_hub_counts',
            $cacheKey,
            fn () => $this->resolveUncached($countKey),
            config('platform.cache.production_hub_counts', 60),
        );
    }

    protected function resolveUncached(string $countKey): ?int
    {
        return match ($countKey) {
            'open_jobs' => $this->openJobs(),
            'job_cards' => $this->jobCards(),
            'active_queue' => $this->activeQueue(),
            'scheduled_jobs' => $this->scheduledJobs(),
            'pending_qc' => $this->pendingQc(),
            'work_centers' => $this->workCenters(),
            'costed_jobs' => $this->costedJobs(),
            'dispatch_ready' => $this->dispatchReady(),
            'active_jobs' => $this->activeJobs(),
            'completed_period' => $this->completedInPeriod(),
            default => null,
        };
    }

    protected function openJobs(): int
    {
        return (int) ProductionJobCard::query()
            ->forTenant()
            ->whereNotIn('status', [
                ProductionJobCardStatus::Completed,
                ProductionJobCardStatus::ReadyForDispatch,
                ProductionJobCardStatus::Cancelled,
            ])
            ->count();
    }

    protected function jobCards(): int
    {
        return (int) ProductionJobCard::query()
            ->forTenant()
            ->whereNot('status', ProductionJobCardStatus::Cancelled)
            ->count();
    }

    protected function activeQueue(): int
    {
        return (int) ProductionQueue::query()
            ->forTenant()
            ->whereIn('status', [
                ProductionQueueStatus::Waiting,
                ProductionQueueStatus::Assigned,
                ProductionQueueStatus::InProgress,
            ])
            ->count();
    }

    protected function scheduledJobs(): int
    {
        return (int) ProductionJobCard::query()
            ->forTenant()
            ->whereNot('status', ProductionJobCardStatus::Cancelled)
            ->whereNotNull('planned_start_date')
            ->whereNotNull('planned_end_date')
            ->count();
    }

    protected function pendingQc(): int
    {
        return (int) ProductionJobCard::query()
            ->forTenant()
            ->where('status', ProductionJobCardStatus::QualityCheck)
            ->count();
    }

    protected function workCenters(): int
    {
        return (int) WorkCenter::query()
            ->forTenant()
            ->where('is_active', true)
            ->count();
    }

    protected function costedJobs(): ?int
    {
        if (! Schema::hasTable('job_cost_sheets')) {
            return null;
        }

        return (int) JobCostSheet::query()
            ->forTenant()
            ->distinct()
            ->count('production_job_card_id');
    }

    protected function dispatchReady(): int
    {
        $readyJobs = (int) ProductionJobCard::query()
            ->forTenant()
            ->where('status', ProductionJobCardStatus::ReadyForDispatch)
            ->count();

        if (! Schema::hasTable('delivery_notes')) {
            return $readyJobs;
        }

        $draftNotes = (int) DeliveryNote::query()
            ->forTenant()
            ->where('status', DeliveryNoteStatus::Draft)
            ->count();

        return $readyJobs + $draftNotes;
    }

    protected function activeJobs(): ?int
    {
        $scope = $this->intelligenceScope();

        if ($scope === null) {
            return null;
        }

        return $this->queries->countActiveJobs($scope);
    }

    protected function completedInPeriod(): ?int
    {
        $scope = $this->intelligenceScope();

        if ($scope === null) {
            return null;
        }

        return $this->queries->countCompletedJobsInPeriod($scope);
    }

    protected function intelligenceScope(): ?IntelligenceScope
    {
        $companyId = tenant()->companyId() ?? auth()->user()?->company_id;

        if (! $companyId) {
            return null;
        }

        return new IntelligenceScope(
            (int) $companyId,
            tenant()->branchId(),
            now()->startOfMonth()->toDateString(),
            now()->toDateString(),
        );
    }
}
