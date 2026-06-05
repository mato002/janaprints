<?php

namespace App\Services\Assets;

use App\Enums\AssetHandoverStatus;
use App\Enums\AssetPhysicalCondition;
use App\Enums\DocumentType;
use App\Models\Assets\AssetHandover;
use App\Models\Assets\FixedAsset;
use App\Support\Platform\NumberGenerator;
use Illuminate\Support\Facades\DB;

class AssetHandoverService
{
    public function __construct(
        protected AssetCustodyTimelineService $timeline,
        protected AssetConditionService $conditions,
        protected AssetCustodyNotificationService $notifications,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data, int $companyId, ?int $branchId, int $userId): AssetHandover
    {
        return DB::transaction(function () use ($data, $companyId, $branchId, $userId) {
            $asset = FixedAsset::query()->where('company_id', $companyId)->findOrFail($data['fixed_asset_id']);
            $number = app(NumberGenerator::class)->generate(DocumentType::AssetHandover, $companyId);

            $handover = AssetHandover::query()->create([
                'company_id' => $companyId,
                'branch_id' => $branchId ?? $asset->branch_id,
                'fixed_asset_id' => $asset->id,
                'handover_no' => $number,
                'from_employee_id' => $data['from_employee_id'] ?? null,
                'to_employee_id' => $data['to_employee_id'] ?? null,
                'from_branch_id' => $data['from_branch_id'] ?? $asset->branch_id,
                'to_branch_id' => $data['to_branch_id'] ?? null,
                'handover_date' => $data['handover_date'] ?? now()->toDateString(),
                'condition_notes' => $data['condition_notes'] ?? null,
                'remarks' => $data['remarks'] ?? null,
                'condition' => isset($data['condition']) ? AssetPhysicalCondition::from($data['condition']) : $asset->current_condition,
                'status' => AssetHandoverStatus::Draft,
            ]);

            $this->timeline->record(
                $asset,
                'transferred',
                __('Handover :no created', ['no' => $handover->handover_no]),
                $handover->remarks,
                $handover,
                $userId,
            );

            return $handover->fresh(['asset', 'fromEmployee', 'toEmployee']);
        });
    }

    public function submit(AssetHandover $handover, int $userId): AssetHandover
    {
        $handover->update(['status' => AssetHandoverStatus::PendingAcceptance]);
        $this->notifications->notifyTransferRequest($handover, $userId);

        return $handover->fresh();
    }

    public function accept(AssetHandover $handover, int $userId): AssetHandover
    {
        return DB::transaction(function () use ($handover, $userId) {
            $handover->update([
                'status' => AssetHandoverStatus::Accepted,
                'received_date' => now()->toDateString(),
                'approved_by' => $userId,
            ]);

            if ($handover->condition) {
                $this->conditions->record($handover->asset, $handover->condition, $handover, $userId, $handover->condition_notes);
            }

            if ($handover->to_employee_id) {
                app(AssetCustodyAssignmentService::class)->assignToEmployee($handover->asset, [
                    'assigned_to_employee_id' => $handover->to_employee_id,
                    'assignment_reason' => __('Handover :no', ['no' => $handover->handover_no]),
                ], $userId);
            }

            $this->timeline->record(
                $handover->asset,
                'transferred',
                __('Handover :no accepted', ['no' => $handover->handover_no]),
                null,
                $handover,
                $userId,
            );

            $this->notifications->notifyTransferAccepted($handover, $userId);

            return $handover->fresh();
        });
    }

    public function reject(AssetHandover $handover, int $userId): AssetHandover
    {
        $handover->update(['status' => AssetHandoverStatus::Rejected]);

        return $handover->fresh();
    }

    public function close(AssetHandover $handover, int $userId): AssetHandover
    {
        $handover->update(['status' => AssetHandoverStatus::Closed]);

        return $handover->fresh();
    }
}
