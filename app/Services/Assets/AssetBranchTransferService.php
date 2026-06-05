<?php

namespace App\Services\Assets;

use App\Enums\ApprovalRuleType;
use App\Enums\AssetBranchTransferStatus;
use App\Enums\AssetCustodyStatus;
use App\Enums\AssetPhysicalCondition;
use App\Enums\DocumentType;
use App\Models\Assets\AssetBranchTransfer;
use App\Models\Assets\FixedAsset;
use App\Support\Platform\ApprovalRulesService;
use App\Support\Platform\NumberGenerator;
use Illuminate\Support\Facades\DB;

class AssetBranchTransferService
{
    public function __construct(
        protected AssetCustodyTimelineService $timeline,
        protected AssetConditionService $conditions,
        protected AssetCustodyNotificationService $notifications,
        protected ApprovalRulesService $approvals,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(FixedAsset $asset, array $data, int $userId): AssetBranchTransfer
    {
        return DB::transaction(function () use ($asset, $data, $userId) {
            $number = app(NumberGenerator::class)->generate(DocumentType::AssetBranchTransfer, $asset->company_id);
            $requiresApproval = $this->approvals->requiresApproval(
                ApprovalRuleType::AssetTransferApproval,
                (float) $asset->acquisition_cost,
                null,
                $asset->company_id,
                $asset->branch_id,
            );

            $transfer = AssetBranchTransfer::query()->create([
                'company_id' => $asset->company_id,
                'fixed_asset_id' => $asset->id,
                'transfer_no' => $number,
                'from_branch_id' => $data['from_branch_id'] ?? $asset->branch_id,
                'to_branch_id' => $data['to_branch_id'],
                'transfer_reason' => $data['transfer_reason'] ?? null,
                'status' => $requiresApproval ? AssetBranchTransferStatus::PendingApproval : AssetBranchTransferStatus::PendingAcceptance,
                'requested_by' => $userId,
                'requested_at' => now(),
                'condition' => isset($data['condition']) ? AssetPhysicalCondition::from($data['condition']) : $asset->current_condition,
                'notes' => $data['notes'] ?? null,
            ]);

            $this->timeline->record(
                $asset,
                'transferred',
                __('Branch transfer :no requested', ['no' => $transfer->transfer_no]),
                $transfer->transfer_reason,
                $transfer,
                $userId,
            );

            if (! $requiresApproval) {
                $this->notifications->notifyTransferRequest($transfer, $userId);
            }

            return $transfer->fresh(['asset', 'fromBranch', 'toBranch', 'requester']);
        });
    }

    public function approve(AssetBranchTransfer $transfer, int $userId): AssetBranchTransfer
    {
        $transfer->update([
            'status' => AssetBranchTransferStatus::PendingAcceptance,
            'approved_by' => $userId,
            'approved_at' => now(),
        ]);

        $this->notifications->notifyTransferRequest($transfer, $userId);

        return $transfer->fresh();
    }

    public function accept(AssetBranchTransfer $transfer, int $userId): AssetBranchTransfer
    {
        return DB::transaction(function () use ($transfer, $userId) {
            $asset = $transfer->asset;

            $transfer->update([
                'status' => AssetBranchTransferStatus::Accepted,
                'accepted_by' => $userId,
                'accepted_at' => now(),
            ]);

            $asset->update([
                'branch_id' => $transfer->to_branch_id,
                'assigned_to_branch_id' => $transfer->to_branch_id,
                'custody_status' => AssetCustodyStatus::Transferred,
            ]);

            if ($transfer->condition) {
                $this->conditions->record($asset, $transfer->condition, $transfer, $userId, $transfer->notes);
            }

            app(AssetCustodyAssignmentService::class)->assignToBranch($asset, $transfer->to_branch_id, $userId, $transfer->transfer_reason);

            $this->timeline->record(
                $asset,
                'transferred',
                __('Branch transfer :no accepted', ['no' => $transfer->transfer_no]),
                null,
                $transfer,
                $userId,
            );

            $this->notifications->notifyTransferAccepted($transfer, $userId);

            return $transfer->fresh();
        });
    }

    public function reject(AssetBranchTransfer $transfer, int $userId): AssetBranchTransfer
    {
        $transfer->update(['status' => AssetBranchTransferStatus::Rejected]);

        return $transfer->fresh();
    }
}
