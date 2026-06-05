<?php

namespace App\Services\Assets;

use App\Models\Assets\FixedAsset;
use App\Models\Assets\MachineOperatorAssignment;
use Illuminate\Support\Facades\DB;

class MachineOperatorService
{
    public function __construct(
        protected AssetCustodyTimelineService $timeline,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function assign(FixedAsset $asset, array $data, int $userId): MachineOperatorAssignment
    {
        return DB::transaction(function () use ($asset, $data, $userId) {
            if ($data['is_primary'] ?? true) {
                MachineOperatorAssignment::query()
                    ->where('fixed_asset_id', $asset->id)
                    ->where('is_primary', true)
                    ->whereNull('end_date')
                    ->update(['end_date' => now()->toDateString()]);
            }

            $assignment = MachineOperatorAssignment::query()->create([
                'company_id' => $asset->company_id,
                'fixed_asset_id' => $asset->id,
                'employee_id' => $data['employee_id'],
                'is_primary' => $data['is_primary'] ?? true,
                'start_date' => $data['start_date'] ?? now()->toDateString(),
                'notes' => $data['notes'] ?? null,
            ]);

            $role = ($data['is_primary'] ?? true) ? __('Primary operator') : __('Backup operator');
            $this->timeline->record(
                $asset,
                'assigned',
                __(':role assigned', ['role' => $role]),
                $data['notes'] ?? null,
                $assignment,
                $userId,
                ['employee_id' => $data['employee_id']],
            );

            return $assignment->fresh(['employee']);
        });
    }

    public function end(MachineOperatorAssignment $assignment, int $userId): MachineOperatorAssignment
    {
        $assignment->update(['end_date' => now()->toDateString()]);

        return $assignment->fresh();
    }
}
