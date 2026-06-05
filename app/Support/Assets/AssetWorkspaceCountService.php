<?php

namespace App\Support\Assets;

use App\Enums\AssetBranchTransferStatus;
use App\Enums\AssetHandoverStatus;
use App\Enums\CapitalizationCandidateStatus;
use App\Enums\MaintenanceWorkOrderStatus;
use App\Models\Assets\AssetBranchTransfer;
use App\Models\Assets\AssetCapitalizationCandidate;
use App\Models\Assets\AssetHandover;
use App\Models\Assets\FixedAsset;
use App\Models\Assets\MachineProfile;
use App\Models\Assets\MaintenanceWorkOrder;

class AssetWorkspaceCountService
{
    public function resolve(?string $key): ?int
    {
        if ($key === null) {
            return null;
        }

        $companyId = tenant()->companyId();
        $branchId = tenant()->branchId();

        if (! $companyId) {
            return null;
        }

        return match ($key) {
            'register' => AssetSchema::count('fixed_assets', fn () => FixedAsset::query()
                ->where('company_id', $companyId)
                ->whereNull('archived_at')
                ->count()),
            'machines' => AssetSchema::count('machine_profiles', fn () => MachineProfile::query()
                ->where('company_id', $companyId)
                ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                ->count()),
            'maintenance_open' => AssetSchema::count('maintenance_work_orders', fn () => MaintenanceWorkOrder::query()
                ->where('company_id', $companyId)
                ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                ->whereIn('status', [
                    MaintenanceWorkOrderStatus::Open->value,
                    MaintenanceWorkOrderStatus::Assigned->value,
                    MaintenanceWorkOrderStatus::InProgress->value,
                    MaintenanceWorkOrderStatus::WaitingParts->value,
                    MaintenanceWorkOrderStatus::WaitingVendor->value,
                ])
                ->count()),
            'custody_pending' => $this->custodyPendingCount($companyId, $branchId),
            'custody_handovers' => AssetSchema::count('asset_handovers', fn () => AssetHandover::query()
                ->where('company_id', $companyId)
                ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                ->where('status', AssetHandoverStatus::PendingAcceptance)
                ->count()),
            'custody_transfers' => AssetSchema::count('asset_branch_transfers', fn () => AssetBranchTransfer::query()
                ->where('company_id', $companyId)
                ->when($branchId, fn ($q) => $q->whereHas('asset', fn ($a) => $a->where('branch_id', $branchId)))
                ->whereIn('status', [
                    AssetBranchTransferStatus::PendingApproval->value,
                    AssetBranchTransferStatus::PendingAcceptance->value,
                ])
                ->count()),
            'capitalization_pending' => AssetSchema::count('asset_capitalization_candidates', fn () => AssetCapitalizationCandidate::query()
                ->where('company_id', $companyId)
                ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                ->whereIn('status', [
                    CapitalizationCandidateStatus::Pending->value,
                    CapitalizationCandidateStatus::Ready->value,
                ])
                ->count()),
            default => null,
        };
    }

    protected function custodyPendingCount(int $companyId, ?int $branchId): int
    {
        $handovers = AssetSchema::count('asset_handovers', fn () => AssetHandover::query()
            ->where('company_id', $companyId)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->where('status', AssetHandoverStatus::PendingAcceptance)
            ->count());

        $transfers = AssetSchema::count('asset_branch_transfers', fn () => AssetBranchTransfer::query()
            ->where('company_id', $companyId)
            ->when($branchId, fn ($q) => $q->whereHas('asset', fn ($a) => $a->where('branch_id', $branchId)))
            ->whereIn('status', [
                AssetBranchTransferStatus::PendingApproval->value,
                AssetBranchTransferStatus::PendingAcceptance->value,
            ])
            ->count());

        return $handovers + $transfers;
    }
}
