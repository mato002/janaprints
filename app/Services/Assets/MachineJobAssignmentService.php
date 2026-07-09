<?php

namespace App\Services\Assets;

use App\Models\Assets\FixedAsset;
use App\Models\Assets\MachineJobAssignment;
use App\Models\Assets\MachineProfile;
use App\Models\Production\ProductionJobCard;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MachineJobAssignmentService
{
    public function __construct(
        protected MachineTimelineService $timeline,
        protected MachineCapacityService $capacity,
        protected MachineAvailabilityService $availability,
    ) {}

    public function assignToJob(
        ProductionJobCard $jobCard,
        FixedAsset $machine,
        int $userId,
        ?string $notes = null,
    ): ProductionJobCard {
        $profile = $machine->machineProfile;

        if (! $profile) {
            throw ValidationException::withMessages([
                'machine' => __('Selected asset is not a production machine.'),
            ]);
        }

        $availability = $this->availability->evaluate($profile);

        if ($availability['state']->value === 'unavailable') {
            throw ValidationException::withMessages([
                'machine' => $availability['reason'] ?? __('Machine is unavailable.'),
            ]);
        }

        return DB::transaction(function () use ($jobCard, $machine, $profile, $userId, $notes) {
            if ($jobCard->assigned_machine_asset_id && $jobCard->assigned_machine_asset_id !== $machine->id) {
                MachineJobAssignment::query()
                    ->where('production_job_card_id', $jobCard->id)
                    ->whereNull('unassigned_at')
                    ->update(['unassigned_at' => now()]);
            }

            $jobCard->update(['assigned_machine_asset_id' => $machine->id]);

            MachineJobAssignment::query()->create([
                'fixed_asset_id' => $machine->id,
                'production_job_card_id' => $jobCard->id,
                'assigned_by' => $userId,
                'assigned_at' => now(),
                'notes' => $notes,
            ]);

            $this->timeline->record(
                $machine,
                'job_assigned',
                __('Job assigned'),
                $jobCard->job_card_number,
                $userId,
                ['production_job_card_id' => $jobCard->id],
            );

            $this->capacity->syncUtilization($profile);

            return $jobCard->fresh(['assignedMachine.machineProfile']);
        });
    }

    /**
     * @return array{
     *     machine_name: ?string,
     *     machine_status: ?string,
     *     capacity: ?array<string, mixed>,
     *     availability: ?array<string, mixed>,
     *     expected_throughput: ?float,
     *     assignment_history: \Illuminate\Support\Collection
     * }
     */
    public function unassignFromJob(ProductionJobCard $jobCard, int $userId): ProductionJobCard
    {
        return DB::transaction(function () use ($jobCard, $userId) {
            MachineJobAssignment::query()
                ->where('production_job_card_id', $jobCard->id)
                ->whereNull('unassigned_at')
                ->update(['unassigned_at' => now()]);

            $jobCard->update(['assigned_machine_asset_id' => null]);

            return $jobCard->fresh(['assignedMachine.machineProfile']);
        });
    }

    public function assignFromHttpRequest(
        ProductionJobCard $jobCard,
        ?int $machineAssetId,
        int $userId,
        ?string $notes = null,
    ): ProductionJobCard {
        if ($machineAssetId === null) {
            return $this->unassignFromJob($jobCard, $userId);
        }

        $machine = FixedAsset::query()
            ->forTenant()
            ->whereHas('machineProfile')
            ->findOrFail($machineAssetId);

        return $this->assignToJob($jobCard, $machine, $userId, $notes);
    }

    /**
     * @return array{
     *     machine_name: ?string,
     *     machine_status: ?string,
     *     capacity: ?array<string, mixed>,
     *     availability: ?array<string, mixed>,
     *     expected_throughput: ?float,
     *     assignment_history: \Illuminate\Support\Collection
     * }
     */
    public function jobMachineContext(ProductionJobCard $jobCard): array
    {
        $machine = $jobCard->assignedMachine;

        if (! $machine?->machineProfile) {
            return [
                'machine_name' => null,
                'machine_status' => null,
                'capacity' => null,
                'availability' => null,
                'expected_throughput' => null,
                'assignment_history' => $jobCard->machineAssignmentHistory()->with('assigner:id,name')->limit(10)->get(),
            ];
        }

        $profile = $machine->machineProfile;
        $capacity = $this->capacity->profileMetrics($profile);
        $availability = $this->availability->evaluate($profile);

        return [
            'machine_name' => $machine->asset_name,
            'machine_status' => $profile->production_status->label(),
            'capacity' => $capacity,
            'availability' => $availability,
            'expected_throughput' => (float) ($profile->capacity_per_hour ?: $profile->hourly_capacity),
            'assignment_history' => $jobCard->machineAssignmentHistory()->with('assigner:id,name')->limit(10)->get(),
        ];
    }
}
