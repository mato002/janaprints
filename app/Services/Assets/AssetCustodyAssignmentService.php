<?php

namespace App\Services\Assets;

use App\Enums\AssetAssignmentStatus;
use App\Enums\AssetAssignmentType;
use App\Enums\AssetCustodyStatus;
use App\Enums\AssetPhysicalCondition;
use App\Models\Assets\AssetAssignmentHistory;
use App\Models\Assets\FixedAsset;
use App\Support\ActivityLogger;
use Illuminate\Support\Facades\DB;

class AssetCustodyAssignmentService
{
    public function __construct(
        protected AssetAssignmentService $legacyAssignments,
        protected AssetCustodyTimelineService $timeline,
        protected AssetConditionService $conditions,
        protected AssetCustodyNotificationService $notifications,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function assignToEmployee(FixedAsset $asset, array $data, int $assignedBy): FixedAsset
    {
        return DB::transaction(function () use ($asset, $data, $assignedBy) {
            $condition = isset($data['condition'])
                ? AssetPhysicalCondition::from($data['condition'])
                : ($asset->current_condition ?? AssetPhysicalCondition::Good);

            $history = AssetAssignmentHistory::query()->create([
                'fixed_asset_id' => $asset->id,
                'assignment_type' => AssetAssignmentType::Employee,
                'assigned_to_employee_id' => $data['assigned_to_employee_id'],
                'assigned_to_department_id' => $data['assigned_to_department_id'] ?? null,
                'assigned_by' => $assignedBy,
                'assigned_at' => now(),
                'expected_return_date' => $data['expected_return_date'] ?? null,
                'assignment_reason' => $data['assignment_reason'] ?? null,
                'status' => AssetAssignmentStatus::Assigned,
                'condition_at_assignment' => $condition,
                'notes' => $data['notes'] ?? null,
            ]);

            $asset->update([
                'assigned_to_employee_id' => $data['assigned_to_employee_id'],
                'assigned_to_department_id' => $data['assigned_to_department_id'] ?? $asset->assigned_to_department_id,
                'custody_status' => AssetCustodyStatus::Assigned,
            ]);

            $this->conditions->record($asset, $condition, $history, $assignedBy);
            $this->timeline->record(
                $asset,
                'assigned',
                __('Asset assigned to employee'),
                $data['assignment_reason'] ?? null,
                $history,
                $assignedBy,
                ['employee_id' => $data['assigned_to_employee_id']],
            );

            ActivityLogger::log('assigned', $asset->fresh(), $assignedBy, [
                'assignment_type' => AssetAssignmentType::Employee->value,
                'assigned_to_employee_id' => $data['assigned_to_employee_id'],
            ]);

            $this->notifications->notifyNewAssignment($asset->fresh(), $assignedBy);

            return $asset->fresh(['assignedEmployee', 'assignedDepartment']);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function assignToDepartment(FixedAsset $asset, array $data, int $assignedBy): FixedAsset
    {
        return DB::transaction(function () use ($asset, $data, $assignedBy) {
            $history = AssetAssignmentHistory::query()->create([
                'fixed_asset_id' => $asset->id,
                'assignment_type' => AssetAssignmentType::Department,
                'assigned_to_department_id' => $data['assigned_to_department_id'],
                'assigned_by' => $assignedBy,
                'assigned_at' => now(),
                'assignment_reason' => $data['assignment_reason'] ?? null,
                'status' => AssetAssignmentStatus::Assigned,
                'notes' => $data['notes'] ?? null,
            ]);

            $asset->update([
                'assigned_to_department_id' => $data['assigned_to_department_id'],
                'custody_status' => AssetCustodyStatus::Assigned,
            ]);

            $this->timeline->record(
                $asset,
                'assigned',
                __('Asset assigned to department'),
                $data['assignment_reason'] ?? null,
                $history,
                $assignedBy,
                ['department_id' => $data['assigned_to_department_id']],
            );

            ActivityLogger::log('assigned', $asset->fresh(), $assignedBy, [
                'assignment_type' => AssetAssignmentType::Department->value,
                'assigned_to_department_id' => $data['assigned_to_department_id'],
            ]);

            return $asset->fresh(['assignedDepartment']);
        });
    }

    public function assignToUser(FixedAsset $asset, int $userId, int $assignedBy, ?string $notes = null): FixedAsset
    {
        $result = $this->legacyAssignments->assignToUser($asset, $userId, $assignedBy, $notes);

        AssetAssignmentHistory::query()
            ->where('fixed_asset_id', $asset->id)
            ->latest('id')
            ->first()
            ?->update(['status' => AssetAssignmentStatus::Assigned]);

        $result->update(['custody_status' => AssetCustodyStatus::Assigned]);
        $this->timeline->record($result, 'assigned', __('Asset assigned to user'), $notes, null, $assignedBy, ['user_id' => $userId]);

        return $result->fresh();
    }

    public function assignToBranch(FixedAsset $asset, int $branchId, int $assignedBy, ?string $notes = null): FixedAsset
    {
        $result = $this->legacyAssignments->assignToBranch($asset, $branchId, $assignedBy, $notes);

        AssetAssignmentHistory::query()
            ->where('fixed_asset_id', $asset->id)
            ->latest('id')
            ->first()
            ?->update(['status' => AssetAssignmentStatus::Assigned]);

        $result->update(['custody_status' => AssetCustodyStatus::Assigned]);
        $this->timeline->record($result, 'assigned', __('Asset assigned to branch'), $notes, null, $assignedBy, ['branch_id' => $branchId]);

        return $result->fresh();
    }

    public function closeAssignment(AssetAssignmentHistory $history, AssetAssignmentStatus $status, int $userId): void
    {
        $history->update([
            'status' => $status,
            'returned_at' => now(),
        ]);
    }
}
