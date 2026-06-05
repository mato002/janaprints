<?php

namespace App\Services\Assets;

use App\Enums\AssetAssignmentStatus;
use App\Enums\AssetCustodyStatus;
use App\Enums\AssetHandoverStatus;
use App\Enums\AssetPhysicalCondition;
use App\Enums\AssetBranchTransferStatus;
use App\Models\Assets\AssetAssignmentHistory;
use App\Models\Assets\AssetBranchTransfer;
use App\Models\Assets\AssetHandover;
use App\Models\Assets\FixedAsset;
use App\Support\Platform\PlatformCacheService;
use Illuminate\Support\Collection;

class AssetCustodyDashboardService
{
    public function __construct(
        protected PlatformCacheService $cache,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function build(int $companyId, ?int $branchId = null): array
    {
        $cacheKey = $branchId ? "{$companyId}:{$branchId}" : "{$companyId}:all";

        return $this->cache->remember('custody_dashboard', $cacheKey, function () use ($companyId, $branchId) {
            $assetBase = FixedAsset::query()
                ->where('company_id', $companyId)
                ->whereNull('archived_at')
                ->when($branchId, fn ($q) => $q->where('branch_id', $branchId));

            return [
                'assigned_assets' => (clone $assetBase)->where('custody_status', AssetCustodyStatus::Assigned)->count(),
                'unassigned_assets' => (clone $assetBase)->where('custody_status', AssetCustodyStatus::Unassigned)->count(),
                'overdue_returns' => $this->overdueReturns($companyId, $branchId),
                'pending_handover_acceptance' => AssetHandover::query()
                    ->where('company_id', $companyId)
                    ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                    ->where('status', AssetHandoverStatus::PendingAcceptance)
                    ->count(),
                'branch_transfers' => AssetBranchTransfer::query()
                    ->where('company_id', $companyId)
                    ->when($branchId, fn ($q) => $q->whereHas('asset', fn ($a) => $a->where('branch_id', $branchId)))
                    ->whereIn('status', [
                        AssetBranchTransferStatus::PendingApproval->value,
                        AssetBranchTransferStatus::PendingAcceptance->value,
                    ])
                    ->count(),
                'lost_assets' => (clone $assetBase)->where('custody_status', AssetCustodyStatus::Lost)->count(),
                'damaged_assets' => (clone $assetBase)->where('custody_status', AssetCustodyStatus::Damaged)->count(),
                'by_department' => $this->byDepartment($companyId, $branchId),
                'by_employee' => $this->byEmployee($companyId, $branchId),
                'pending_handovers' => $this->pendingHandovers($companyId, $branchId),
                'pending_transfers' => $this->pendingTransfers($companyId, $branchId),
            ];
        }, config('platform.cache.custody_dashboard', 60));
    }

    protected function overdueReturns(int $companyId, ?int $branchId): int
    {
        return AssetAssignmentHistory::query()
            ->where('status', AssetAssignmentStatus::Assigned)
            ->whereNotNull('expected_return_date')
            ->where('expected_return_date', '<', now()->toDateString())
            ->whereHas('asset', function ($q) use ($companyId, $branchId) {
                $q->where('company_id', $companyId)
                    ->when($branchId, fn ($b) => $b->where('branch_id', $branchId));
            })
            ->count();
    }

    protected function byDepartment(int $companyId, ?int $branchId): Collection
    {
        return FixedAsset::query()
            ->where('company_id', $companyId)
            ->whereNull('archived_at')
            ->whereNotNull('assigned_to_department_id')
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->selectRaw('assigned_to_department_id as department_id, COUNT(*) as count')
            ->groupBy('assigned_to_department_id')
            ->with('assignedDepartment:id,name')
            ->get();
    }

    protected function byEmployee(int $companyId, ?int $branchId): Collection
    {
        return FixedAsset::query()
            ->where('company_id', $companyId)
            ->whereNull('archived_at')
            ->whereNotNull('assigned_to_employee_id')
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->selectRaw('assigned_to_employee_id as employee_id, COUNT(*) as count')
            ->groupBy('assigned_to_employee_id')
            ->limit(10)
            ->get();
    }

    protected function pendingHandovers(int $companyId, ?int $branchId): Collection
    {
        return AssetHandover::query()
            ->where('company_id', $companyId)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->where('status', AssetHandoverStatus::PendingAcceptance)
            ->with(['asset:id,asset_name,asset_number', 'toEmployee:id,first_name,last_name'])
            ->latest('handover_date')
            ->limit(5)
            ->get();
    }

    protected function pendingTransfers(int $companyId, ?int $branchId): Collection
    {
        return AssetBranchTransfer::query()
            ->where('company_id', $companyId)
            ->when($branchId, fn ($q) => $q->whereHas('asset', fn ($a) => $a->where('branch_id', $branchId)))
            ->whereIn('status', [
                AssetBranchTransferStatus::PendingApproval->value,
                AssetBranchTransferStatus::PendingAcceptance->value,
            ])
            ->with(['asset:id,asset_name,asset_number', 'fromBranch:id,name', 'toBranch:id,name'])
            ->latest('requested_at')
            ->limit(5)
            ->get();
    }
}
