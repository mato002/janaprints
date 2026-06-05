<?php

namespace App\Services\Assets;

use App\Enums\AssetAssignmentType;
use App\Models\Assets\AssetAssignmentHistory;
use App\Models\Assets\FixedAsset;
use App\Support\ActivityLogger;
use Illuminate\Support\Facades\DB;

class AssetAssignmentService
{
    public function assignToUser(FixedAsset $asset, int $userId, int $assignedBy, ?string $notes = null): FixedAsset
    {
        return DB::transaction(function () use ($asset, $userId, $assignedBy, $notes) {
            AssetAssignmentHistory::query()->create([
                'fixed_asset_id' => $asset->id,
                'assignment_type' => AssetAssignmentType::User,
                'assigned_to_user_id' => $userId,
                'assigned_to_branch_id' => null,
                'assigned_by' => $assignedBy,
                'assigned_at' => now(),
                'notes' => $notes,
            ]);

            $asset->update([
                'assigned_to_user_id' => $userId,
                'assigned_to_branch_id' => $asset->assigned_to_branch_id,
            ]);

            ActivityLogger::log('assigned', $asset->fresh(), $assignedBy, [
                'assignment_type' => AssetAssignmentType::User->value,
                'assigned_to_user_id' => $userId,
            ]);

            return $asset->fresh();
        });
    }

    public function assignToBranch(FixedAsset $asset, int $branchId, int $assignedBy, ?string $notes = null): FixedAsset
    {
        return DB::transaction(function () use ($asset, $branchId, $assignedBy, $notes) {
            AssetAssignmentHistory::query()->create([
                'fixed_asset_id' => $asset->id,
                'assignment_type' => AssetAssignmentType::Branch,
                'assigned_to_user_id' => null,
                'assigned_to_branch_id' => $branchId,
                'assigned_by' => $assignedBy,
                'assigned_at' => now(),
                'notes' => $notes,
            ]);

            $asset->update([
                'branch_id' => $branchId,
                'assigned_to_branch_id' => $branchId,
            ]);

            ActivityLogger::log('assigned', $asset->fresh(), $assignedBy, [
                'assignment_type' => AssetAssignmentType::Branch->value,
                'assigned_to_branch_id' => $branchId,
            ]);

            return $asset->fresh();
        });
    }
}
