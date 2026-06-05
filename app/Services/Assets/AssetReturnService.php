<?php

namespace App\Services\Assets;

use App\Enums\AssetAssignmentStatus;
use App\Enums\AssetCustodyStatus;
use App\Enums\AssetPhysicalCondition;
use App\Enums\AssetReturnCondition;
use App\Models\Assets\AssetAssignmentHistory;
use App\Models\Assets\AssetReturn;
use App\Models\Assets\FixedAsset;
use Illuminate\Support\Facades\DB;

class AssetReturnService
{
    public function __construct(
        protected AssetCustodyTimelineService $timeline,
        protected AssetConditionService $conditions,
        protected AssetCustodyAssignmentService $assignments,
        protected AssetCustodyNotificationService $notifications,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function record(FixedAsset $asset, array $data, int $userId): AssetReturn
    {
        return DB::transaction(function () use ($asset, $data, $userId) {
            $condition = AssetReturnCondition::from($data['condition']);
            $activeAssignment = AssetAssignmentHistory::query()
                ->where('fixed_asset_id', $asset->id)
                ->where('status', AssetAssignmentStatus::Assigned)
                ->latest('assigned_at')
                ->first();

            $assetReturn = AssetReturn::query()->create([
                'company_id' => $asset->company_id,
                'branch_id' => $asset->branch_id,
                'fixed_asset_id' => $asset->id,
                'assignment_history_id' => $activeAssignment?->id,
                'return_date' => $data['return_date'] ?? now()->toDateString(),
                'condition' => $condition,
                'returned_by' => $data['returned_by'] ?? null,
                'received_by' => $userId,
                'notes' => $data['notes'] ?? null,
                'requires_review' => $condition === AssetReturnCondition::RequiresReview
                    || $condition === AssetReturnCondition::Damaged,
            ]);

            if ($activeAssignment) {
                $status = match ($condition) {
                    AssetReturnCondition::Lost => AssetAssignmentStatus::Lost,
                    AssetReturnCondition::Damaged => AssetAssignmentStatus::Damaged,
                    default => AssetAssignmentStatus::Returned,
                };
                $this->assignments->closeAssignment($activeAssignment, $status, $userId);
            }

            $custodyStatus = match ($condition) {
                AssetReturnCondition::Lost => AssetCustodyStatus::Lost,
                AssetReturnCondition::Damaged => AssetCustodyStatus::Damaged,
                default => AssetCustodyStatus::Returned,
            };

            $physicalCondition = match ($condition) {
                AssetReturnCondition::Excellent => AssetPhysicalCondition::Excellent,
                AssetReturnCondition::Good => AssetPhysicalCondition::Good,
                AssetReturnCondition::Fair => AssetPhysicalCondition::Fair,
                AssetReturnCondition::Damaged => AssetPhysicalCondition::Damaged,
                AssetReturnCondition::Lost => AssetPhysicalCondition::WrittenOff,
                AssetReturnCondition::RequiresReview => AssetPhysicalCondition::NeedsRepair,
            };

            $asset->update([
                'assigned_to_employee_id' => null,
                'assigned_to_user_id' => null,
                'custody_status' => $custodyStatus,
            ]);

            $this->conditions->record($asset, $physicalCondition, $assetReturn, $userId, $data['notes'] ?? null);
            $this->timeline->record(
                $asset,
                'returned',
                __('Asset returned'),
                $data['notes'] ?? null,
                $assetReturn,
                $userId,
                ['condition' => $condition->value],
            );

            if (in_array($condition, [AssetReturnCondition::Lost, AssetReturnCondition::Damaged], true)) {
                $this->notifications->notifyAssetConditionAlert($asset, $condition, $userId);
            }

            return $assetReturn->fresh(['asset', 'returnedByEmployee', 'receiver']);
        });
    }
}
