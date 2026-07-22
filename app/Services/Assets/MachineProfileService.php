<?php

namespace App\Services\Assets;

use App\Enums\MachineCapacityUnit;
use App\Enums\ProductionMachineStatus;
use App\Models\Assets\FixedAsset;
use App\Models\Assets\MachineProfile;
use App\Models\Production\WorkCenter;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MachineProfileService
{
    public function __construct(
        protected AssetRegisterService $assetRegister,
        protected MachineTimelineService $timeline,
        protected MachineCapacityService $capacity,
        protected MachineStatusService $status,
    ) {}

    /**
     * Register a fixed asset and activate it as a production machine in one step.
     *
     * @param  array<string, mixed>  $data
     */
    public function registerMachine(array $data, int $companyId, ?int $branchId, int $userId): MachineProfile
    {
        return DB::transaction(function () use ($data, $companyId, $branchId, $userId) {
            $asset = $this->assetRegister->create([
                'asset_category_id' => $data['asset_category_id'],
                'asset_name' => $data['asset_name'],
                'branch_id' => $data['branch_id'] ?? $branchId,
                'serial_number' => $data['serial_number'] ?? null,
                'manufacturer' => $data['manufacturer'] ?? null,
                'model' => $data['model'] ?? null,
                'acquisition_date' => $data['acquisition_date'] ?? now()->toDateString(),
                'acquisition_cost' => $data['acquisition_cost'] ?? 0,
                'residual_value' => 0,
                'notes' => $data['notes'] ?? null,
            ], $companyId, $branchId, $userId);

            return $this->createForAsset($asset, $data, $userId);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createForAsset(FixedAsset $asset, array $data, int $userId): MachineProfile
    {
        if ($asset->machineProfile) {
            throw ValidationException::withMessages([
                'asset' => __('This asset already has a machine profile.'),
            ]);
        }

        return DB::transaction(function () use ($asset, $data, $userId) {
            $profile = MachineProfile::query()->create([
                'company_id' => $asset->company_id,
                'branch_id' => $asset->branch_id,
                'fixed_asset_id' => $asset->id,
                'machine_code' => $data['machine_code'],
                'machine_type' => $data['machine_type'],
                'manufacturer' => $data['manufacturer'] ?? $asset->manufacturer,
                'model' => $data['model'] ?? $asset->model,
                'serial_number' => $data['serial_number'] ?? $asset->serial_number,
                'production_area' => $data['production_area'] ?? null,
                'installation_date' => $data['installation_date'] ?? null,
                'capacity_unit' => $data['capacity_unit'] ?? MachineCapacityUnit::Jobs->value,
                'capacity_per_hour' => $data['capacity_per_hour'] ?? 0,
                'capacity_per_shift' => $data['capacity_per_shift'] ?? 0,
                'is_primary_production_machine' => (bool) ($data['is_primary_production_machine'] ?? false),
                'production_status' => ProductionMachineStatus::Available,
                'hourly_capacity' => $data['hourly_capacity'] ?? ($data['capacity_per_hour'] ?? 0),
                'daily_capacity' => $data['daily_capacity'] ?? 0,
                'shift_capacity' => $data['shift_capacity'] ?? ($data['capacity_per_shift'] ?? 0),
                'monthly_capacity' => $data['monthly_capacity'] ?? 0,
            ]);

            $this->timeline->record($asset, 'created', __('Machine profile created'), null, $userId);
            $this->timeline->record($asset, 'activated', __('Machine activated for production'), null, $userId);

            return $profile->fresh(['asset.category', 'workCenter']);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateProfile(MachineProfile $profile, array $data, int $userId): MachineProfile
    {
        $profile->update($data);

        if (isset($data['hourly_capacity']) || isset($data['shift_capacity']) || isset($data['daily_capacity'])) {
            $this->timeline->record(
                $profile->asset,
                'capacity_changed',
                __('Capacity updated'),
                null,
                $userId,
                array_intersect_key($data, array_flip(['hourly_capacity', 'daily_capacity', 'shift_capacity', 'monthly_capacity'])),
            );
            $this->capacity->syncUtilization($profile);
        }

        return $profile->fresh(['asset.category', 'workCenter']);
    }

    public function assignWorkCenter(MachineProfile $profile, ?int $workCenterId, int $userId): MachineProfile
    {
        return DB::transaction(function () use ($profile, $workCenterId, $userId) {
            if ($workCenterId) {
                $center = WorkCenter::query()
                    ->where('company_id', $profile->company_id)
                    ->findOrFail($workCenterId);

                if ($center->fixed_asset_id && $center->fixed_asset_id !== $profile->fixed_asset_id) {
                    throw ValidationException::withMessages([
                        'work_center_id' => __('Work center is already linked to another machine.'),
                    ]);
                }

                $center->update(['fixed_asset_id' => $profile->fixed_asset_id]);

                $this->timeline->record(
                    $profile->asset,
                    'work_center_assigned',
                    __('Assigned to work center'),
                    $center->name,
                    $userId,
                    ['work_center_id' => $center->id],
                );
            } else {
                WorkCenter::query()
                    ->where('fixed_asset_id', $profile->fixed_asset_id)
                    ->update(['fixed_asset_id' => null]);
            }

            return $profile->fresh(['asset.category', 'workCenter']);
        });
    }
}
