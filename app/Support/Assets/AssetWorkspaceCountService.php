<?php

namespace App\Support\Assets;

use App\Models\Assets\FixedAsset;
use App\Enums\MaintenanceWorkOrderStatus;
use App\Models\Assets\MachineProfile;
use App\Models\Assets\MaintenanceWorkOrder;
use App\Enums\AssetHandoverStatus;
use App\Enums\AssetBranchTransferStatus;
use App\Models\Assets\AssetHandover;
use App\Models\Assets\AssetBranchTransfer;
use App\Models\Assets\AssetCapitalizationCandidate;
use App\Enums\CapitalizationCandidateStatus;

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
            'register' => FixedAsset::query()
                ->where('company_id', $companyId)
                ->whereNull('archived_at')
                ->count(),
            'machines' => MachineProfile::query()
                ->where('company_id', $companyId)
                ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                ->count(),
            'maintenance_open' => MaintenanceWorkOrder::query()
                ->where('company_id', $companyId)
                ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                ->whereIn('status', [
                    MaintenanceWorkOrderStatus::Open->value,
                    MaintenanceWorkOrderStatus::Assigned->value,
                    MaintenanceWorkOrderStatus::InProgress->value,
                    MaintenanceWorkOrderStatus::WaitingParts->value,
                    MaintenanceWorkOrderStatus::WaitingVendor->value,
                ])
                ->count(),
            'custody_pending' => AssetHandover::query()
                ->where('company_id', $companyId)
                ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                ->where('status', AssetHandoverStatus::PendingAcceptance)
                ->count()
                + AssetBranchTransfer::query()
                    ->where('company_id', $companyId)
                    ->when($branchId, fn ($q) => $q->whereHas('asset', fn ($a) => $a->where('branch_id', $branchId)))
                    ->whereIn('status', [
                        AssetBranchTransferStatus::PendingApproval->value,
                        AssetBranchTransferStatus::PendingAcceptance->value,
                    ])
                    ->count(),
            'custody_handovers' => AssetHandover::query()
                ->where('company_id', $companyId)
                ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                ->where('status', AssetHandoverStatus::PendingAcceptance)
                ->count(),
            'custody_transfers' => AssetBranchTransfer::query()
                ->where('company_id', $companyId)
                ->when($branchId, fn ($q) => $q->whereHas('asset', fn ($a) => $a->where('branch_id', $branchId)))
                ->whereIn('status', [
                    AssetBranchTransferStatus::PendingApproval->value,
                    AssetBranchTransferStatus::PendingAcceptance->value,
                ])
                ->count(),
            'capitalization_pending' => AssetCapitalizationCandidate::query()
                ->where('company_id', $companyId)
                ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                ->whereIn('status', [
                    CapitalizationCandidateStatus::Pending->value,
                    CapitalizationCandidateStatus::Ready->value,
                ])
                ->count(),
            default => null,
        };
    }
}
