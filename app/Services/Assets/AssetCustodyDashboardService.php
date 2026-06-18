<?php

namespace App\Services\Assets;

use App\Enums\AssetAssignmentStatus;
use App\Enums\AssetCustodyStatus;
use App\Enums\AssetHandoverStatus;
use App\Enums\AssetBranchTransferStatus;
use App\Models\Assets\AssetAssignmentHistory;
use App\Models\Assets\AssetBranchTransfer;
use App\Models\Assets\AssetHandover;
use App\Models\Assets\FixedAsset;
use App\Support\Platform\PlatformCacheService;

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

    protected function byDepartment(int $companyId, ?int $branchId): array
    {
        return FixedAsset::query()
            ->where('fixed_assets.company_id', $companyId)
            ->whereNull('fixed_assets.archived_at')
            ->whereNotNull('fixed_assets.assigned_to_department_id')
            ->when($branchId, fn ($q) => $q->where('fixed_assets.branch_id', $branchId))
            ->join('departments', 'departments.id', '=', 'fixed_assets.assigned_to_department_id')
            ->selectRaw('departments.id as department_id, departments.name as department_name, COUNT(*) as count')
            ->groupBy('departments.id', 'departments.name')
            ->orderByDesc('count')
            ->get()
            ->map(fn ($row) => [
                'department_id' => (int) $row->department_id,
                'department_name' => (string) $row->department_name,
                'count' => (int) $row->count,
            ])
            ->values()
            ->all();
    }

    protected function byEmployee(int $companyId, ?int $branchId): array
    {
        return FixedAsset::query()
            ->where('fixed_assets.company_id', $companyId)
            ->whereNull('fixed_assets.archived_at')
            ->whereNotNull('fixed_assets.assigned_to_employee_id')
            ->when($branchId, fn ($q) => $q->where('fixed_assets.branch_id', $branchId))
            ->join('employees', 'employees.id', '=', 'fixed_assets.assigned_to_employee_id')
            ->selectRaw('employees.id as employee_id, employees.first_name, employees.last_name, COUNT(*) as count')
            ->groupBy('employees.id', 'employees.first_name', 'employees.last_name')
            ->orderByDesc('count')
            ->limit(10)
            ->get()
            ->map(fn ($row) => [
                'employee_id' => (int) $row->employee_id,
                'employee_name' => trim("{$row->first_name} {$row->last_name}"),
                'count' => (int) $row->count,
            ])
            ->values()
            ->all();
    }

    /**
     * @return list<array{id: int, handover_no: string, asset_name: string|null}>
     */
    protected function pendingHandovers(int $companyId, ?int $branchId): array
    {
        return AssetHandover::query()
            ->where('company_id', $companyId)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->where('status', AssetHandoverStatus::PendingAcceptance)
            ->with(['asset:id,asset_name,asset_number', 'toEmployee:id,first_name,last_name'])
            ->latest('handover_date')
            ->limit(5)
            ->get()
            ->map(fn (AssetHandover $handover) => [
                'id' => $handover->id,
                'handover_no' => $handover->handover_no,
                'asset_name' => $handover->asset?->asset_name,
            ])
            ->all();
    }

    /**
     * @return list<array{id: int, transfer_no: string, asset_name: string|null}>
     */
    protected function pendingTransfers(int $companyId, ?int $branchId): array
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
            ->get()
            ->map(fn (AssetBranchTransfer $transfer) => [
                'id' => $transfer->id,
                'transfer_no' => $transfer->transfer_no,
                'asset_name' => $transfer->asset?->asset_name,
            ])
            ->all();
    }
}
