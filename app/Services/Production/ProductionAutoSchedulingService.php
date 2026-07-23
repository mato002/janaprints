<?php

namespace App\Services\Production;

use App\Enums\MachineAvailabilityState;
use App\Enums\ProductionJobCardStatus;
use App\Enums\ProductionQueueStatus;
use App\Enums\ProductionType;
use App\Models\Production\ProductionJobCard;
use App\Models\Production\ProductionQueue;
use App\Models\Production\WorkCenter;
use App\Services\Assets\MachineAvailabilityService;
use App\Services\Assets\MachineJobAssignmentService;
use App\Support\Production\ProductionQueueService;
use App\Support\Production\ProductionSchedulingSettings;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProductionAutoSchedulingService
{
    public function __construct(
        protected ProductionSchedulingSettings $settings,
        protected MachineAvailabilityService $machineAvailability,
        protected MachineJobAssignmentService $machineAssignments,
        protected ProductionQueueService $queues,
    ) {}

    public function isEnabled(ProductionJobCard $jobCard): bool
    {
        return $this->settings->autoScheduleOnCreate($jobCard->company_id, $jobCard->branch_id);
    }

    public function canQueueDraftJob(ProductionJobCard $jobCard): bool
    {
        $jobCard->loadMissing(['queues', 'routeSteps']);

        if ($jobCard->queues->isNotEmpty()) {
            return true;
        }

        if ($jobCard->routeSteps->first(fn ($step) => $step->work_center_id !== null) !== null) {
            return true;
        }

        try {
            $this->resolveWorkCenter($jobCard);

            return true;
        } catch (ValidationException) {
            return false;
        }
    }

    /**
     * @return array<string, mixed>|null Null when auto-scheduling is disabled.
     */
    public function scheduleIfEnabled(ProductionJobCard $jobCard, int $userId): ?array
    {
        if (! $this->isEnabled($jobCard)) {
            return null;
        }

        return $this->trySchedule($jobCard, $userId);
    }

    /**
     * @return array{
     *     scheduled: bool,
     *     reason: ?string,
     *     work_center_id: ?int,
     *     queue_position: ?int,
     *     planned_start_date: ?string,
     *     planned_end_date: ?string,
     *     machine_assigned: bool
     * }
     */
    public function trySchedule(ProductionJobCard $jobCard, int $userId): array
    {
        try {
            return $this->schedule($jobCard, $userId);
        } catch (ValidationException $exception) {
            return [
                'scheduled' => false,
                'reason' => collect($exception->errors())->flatten()->first(),
                'work_center_id' => null,
                'queue_position' => null,
                'planned_start_date' => null,
                'planned_end_date' => null,
                'machine_assigned' => false,
            ];
        }
    }

    /**
     * @return array{
     *     scheduled: bool,
     *     reason: ?string,
     *     work_center_id: int,
     *     queue_position: int,
     *     planned_start_date: string,
     *     planned_end_date: string,
     *     machine_assigned: bool
     * }
     */
    public function schedule(ProductionJobCard $jobCard, int $userId): array
    {
        $jobCard->loadMissing(['queues', 'salesOrder', 'routeSteps']);

        if ($jobCard->queues->isEmpty()) {
            $workCenter = $this->resolveWorkCenter($jobCard);
            $this->assertCapacityAvailable($workCenter);
            $machine = $this->resolveMachine($workCenter);

            if ($machine !== null) {
                $this->assertMachineAvailable($machine);
            }

            $dates = $this->resolvePlannedDates($jobCard, $workCenter);
            $queuePosition = $this->nextQueuePosition($workCenter);

            return DB::transaction(function () use ($jobCard, $workCenter, $dates, $queuePosition, $machine, $userId) {
                $jobCard->update([
                    'planned_start_date' => $dates['planned_start_date'],
                    'planned_end_date' => $dates['planned_end_date'],
                    'estimated_duration_minutes' => $dates['estimated_duration_minutes'] ?? null,
                ]);

                $this->queues->enqueue($jobCard->fresh(), $workCenter->id, $queuePosition);

                $machineAssigned = false;

                if ($machine !== null) {
                    $this->machineAssignments->assignToJob($jobCard->fresh(), $machine, $userId);
                    $machineAssigned = true;
                }

                return [
                    'scheduled' => true,
                    'reason' => null,
                    'work_center_id' => $workCenter->id,
                    'queue_position' => $queuePosition,
                    'planned_start_date' => $dates['planned_start_date'],
                    'planned_end_date' => $dates['planned_end_date'],
                    'machine_assigned' => $machineAssigned,
                ];
            });
        }

        $firstStep = $jobCard->routeSteps->first(fn ($s) => $s->work_center_id !== null);
        $workCenter = $firstStep
            ? WorkCenter::query()->find($firstStep->work_center_id)
            : $this->resolveWorkCenter($jobCard);

        $dates = $this->resolvePlannedDates($jobCard, $workCenter);

        $jobCard->update([
            'planned_start_date' => $dates['planned_start_date'],
            'planned_end_date' => $dates['planned_end_date'],
            'estimated_duration_minutes' => $dates['estimated_duration_minutes'] ?? null,
        ]);

        $queuePosition = null;

        if (! $this->queues->hasActiveQueue($jobCard)) {
            $queuePosition = $this->nextQueuePosition($workCenter);
            $this->queues->enqueue($jobCard->fresh(), $workCenter->id, $queuePosition);
        }

        return [
            'scheduled' => true,
            'reason' => null,
            'work_center_id' => $workCenter->id,
            'queue_position' => $queuePosition,
            'planned_start_date' => $dates['planned_start_date'],
            'planned_end_date' => $dates['planned_end_date'],
            'machine_assigned' => false,
        ];
    }

    public function capacitySnapshot(WorkCenter $workCenter): array
    {
        $metrics = $this->capacityMetrics($workCenter);

        return [
            ...$metrics,
            'has_capacity' => $this->hasCapacity($workCenter, $metrics),
        ];
    }

    /**
     * @return array{
     *     capacity: int,
     *     active_jobs: int,
     *     queue_count: int,
     *     utilization_percent: int,
     *     is_overbooked: bool
     * }
     */
    public function capacityMetrics(WorkCenter $workCenter): array
    {
        $capacity = $this->settings->workCenterCapacity();
        $activeStatuses = $this->activeQueueStatuses();

        $queueCount = (int) ProductionQueue::query()
            ->where('work_center_id', $workCenter->id)
            ->whereIn('status', $activeStatuses)
            ->count();

        $activeJobs = (int) ProductionQueue::query()
            ->where('work_center_id', $workCenter->id)
            ->whereIn('status', $activeStatuses)
            ->selectRaw('count(distinct production_job_card_id) as aggregate')
            ->value('aggregate');

        $utilization = $capacity > 0 ? (int) round(($activeJobs / $capacity) * 100) : 0;

        return [
            'capacity' => $capacity,
            'active_jobs' => $activeJobs,
            'queue_count' => $queueCount,
            'utilization_percent' => min(999, $utilization),
            'is_overbooked' => $activeJobs > $capacity,
        ];
    }

    protected function resolveWorkCenter(ProductionJobCard $jobCard): WorkCenter
    {
        $recommended = app(\App\Support\Production\DepartmentQueueRoutingService::class)
            ->recommendedWorkCenter($jobCard);

        if ($recommended !== null) {
            return $recommended;
        }

        $codes = $this->workCenterCodesForType($jobCard->production_type);

        $centers = WorkCenter::query()
            ->where('company_id', $jobCard->company_id)
            ->where('branch_id', $jobCard->branch_id)
            ->where('is_active', true)
            ->whereIn('code', $codes)
            ->orderBy('name')
            ->get()
            ->sortBy(fn (WorkCenter $center) => array_search($center->code, $codes, true) ?? 99)
            ->values();

        $workCenter = $centers->first(fn (WorkCenter $center) => $this->hasCapacity($center))
            ?? $centers->first();

        if ($workCenter === null) {
            throw ValidationException::withMessages([
                'work_center' => __('No active work center is configured for this production type.'),
            ]);
        }

        return $workCenter;
    }

    /**
     * @return list<string>
     */
    protected function workCenterCodesForType(ProductionType $type): array
    {
        $map = config('production.production_type_work_center_codes', []);
        $primary = $map[$type->value] ?? $map[ProductionType::Mixed->value] ?? 'DESIGN';

        if (is_array($primary)) {
            return $primary;
        }

        return [$primary];
    }

    /**
     * @param  array<string, mixed>|null  $metrics
     */
    protected function hasCapacity(WorkCenter $workCenter, ?array $metrics = null): bool
    {
        $metrics ??= $this->capacityMetrics($workCenter);

        return (int) $metrics['active_jobs'] < (int) $metrics['capacity'];
    }

    protected function assertCapacityAvailable(WorkCenter $workCenter): void
    {
        if ($this->hasCapacity($workCenter)) {
            return;
        }

        $snapshot = $this->capacitySnapshot($workCenter);

        throw ValidationException::withMessages([
            'work_center' => __('Work center :name is at capacity (:active/:capacity active jobs).', [
                'name' => $workCenter->name,
                'active' => $snapshot['active_jobs'],
                'capacity' => $snapshot['capacity'],
            ]),
        ]);
    }

    protected function resolveMachine(WorkCenter $workCenter): ?\App\Models\Assets\FixedAsset
    {
        $workCenter->loadMissing('machineAsset.machineProfile');

        return $workCenter->machineAsset;
    }

    protected function assertMachineAvailable(\App\Models\Assets\FixedAsset $machine): void
    {
        $profile = $machine->machineProfile;

        if ($profile === null) {
            return;
        }

        $availability = $this->machineAvailability->evaluate($profile);

        if ($availability['state'] === MachineAvailabilityState::Unavailable) {
            throw ValidationException::withMessages([
                'machine' => $availability['reason'] ?? __('Machine is unavailable for scheduling.'),
            ]);
        }
    }

    /**
     * @return array{planned_start_date: string, planned_end_date: string}
     */
    protected function resolvePlannedDates(ProductionJobCard $jobCard, WorkCenter $workCenter): array
    {
        $durationDays = $this->settings->defaultJobDurationDays();
        $requiredDate = $jobCard->salesOrder?->required_date;

        $start = $jobCard->planned_start_date?->toDateString()
            ?? $this->nextAvailableStartDate($workCenter)->toDateString();

        $end = $jobCard->planned_end_date?->toDateString()
            ?? Carbon::parse($start)->addDays($durationDays - 1)->toDateString();

        if ($requiredDate !== null) {
            $required = $requiredDate->toDateString();
            if ($end > $required) {
                $end = $required;
            }
            if ($start > $required) {
                $start = $required;
            }
        }

        if ($end < $start) {
            $end = $start;
        }

        return [
            'planned_start_date' => $start,
            'planned_end_date' => $end,
            'estimated_duration_minutes' => $durationDays * 8 * 60,
        ];
    }

    protected function nextAvailableStartDate(WorkCenter $workCenter): Carbon
    {
        $today = now()->startOfDay();
        $lastEnd = ProductionJobCard::query()
            ->whereHas('queues', function ($query) use ($workCenter) {
                $query->where('work_center_id', $workCenter->id)
                    ->whereIn('status', [
                        ProductionQueueStatus::Waiting->value,
                        ProductionQueueStatus::Assigned->value,
                        ProductionQueueStatus::InProgress->value,
                    ]);
            })
            ->max('planned_end_date');

        if ($lastEnd === null) {
            return $today;
        }

        $candidate = Carbon::parse($lastEnd)->addDay()->startOfDay();

        return $candidate->greaterThan($today) ? $candidate : $today;
    }

    protected function nextQueuePosition(WorkCenter $workCenter): int
    {
        $max = (int) ProductionQueue::query()
            ->where('work_center_id', $workCenter->id)
            ->max('queue_position');

        return $max + 1;
    }

    /**
     * @return list<string>
     */
    protected function activeQueueStatuses(): array
    {
        return array_map(
            fn (ProductionQueueStatus $status) => $status->value,
            ProductionQueueStatus::activeStatuses(),
        );
    }
}
