<?php

namespace App\Services\Assets;

use App\Models\Assets\FixedAsset;
use App\Models\Assets\VehicleDriverAssignment;
use Illuminate\Support\Facades\DB;

class VehicleDriverAssignmentService
{
    public function __construct(
        protected AssetCustodyTimelineService $timeline,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function assign(FixedAsset $vehicle, array $data, int $userId): VehicleDriverAssignment
    {
        return DB::transaction(function () use ($vehicle, $data, $userId) {
            VehicleDriverAssignment::query()
                ->where('vehicle_asset_id', $vehicle->id)
                ->whereNull('end_date')
                ->update(['end_date' => now()->toDateString()]);

            $assignment = VehicleDriverAssignment::query()->create([
                'company_id' => $vehicle->company_id,
                'vehicle_asset_id' => $vehicle->id,
                'employee_id' => $data['employee_id'],
                'assigned_date' => $data['assigned_date'] ?? now()->toDateString(),
                'license_number' => $data['license_number'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            app(AssetCustodyAssignmentService::class)->assignToEmployee($vehicle, [
                'assigned_to_employee_id' => $data['employee_id'],
                'assignment_reason' => __('Vehicle driver assignment'),
            ], $userId);

            $this->timeline->record(
                $vehicle,
                'assigned',
                __('Vehicle driver assigned'),
                $data['notes'] ?? null,
                $assignment,
                $userId,
                ['employee_id' => $data['employee_id']],
            );

            return $assignment->fresh(['employee']);
        });
    }
}
