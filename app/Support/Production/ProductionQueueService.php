<?php

namespace App\Support\Production;

use App\Enums\ProductionJobCardStatus;
use App\Enums\ProductionQueueStatus;
use App\Models\Production\ProductionJobCard;
use App\Models\Production\ProductionQueue;
use App\Models\Production\WorkCenter;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ProductionQueueService
{
    protected static bool $bypassQueueEnforcement = false;

    public static function isBypassingQueueEnforcement(): bool
    {
        return static::$bypassQueueEnforcement;
    }

    /**
     * @template TReturn
     *
     * @param  callable(): TReturn  $callback
     * @return TReturn
     */
    public static function withoutQueueEnforcement(callable $callback): mixed
    {
        $previous = static::$bypassQueueEnforcement;
        static::$bypassQueueEnforcement = true;

        try {
            return $callback();
        } finally {
            static::$bypassQueueEnforcement = $previous;
        }
    }

    public function __construct(
        protected ProductionQueueOrderingService $ordering,
    ) {}

    public function hasActiveQueue(ProductionJobCard $jobCard): bool
    {
        return ProductionQueue::query()
            ->where('production_job_card_id', $jobCard->id)
            ->whereIn('status', $this->activeQueueStatusValues())
            ->exists();
    }

    public function enqueue(
        ProductionJobCard $jobCard,
        int $workCenterId,
        ?int $queuePosition = null,
        ?int $assignedOperatorId = null,
    ): ProductionQueue {
        $this->assertWorkCenterForJob($jobCard, $workCenterId);

        $duplicate = ProductionQueue::query()
            ->where('production_job_card_id', $jobCard->id)
            ->where('work_center_id', $workCenterId)
            ->whereIn('status', $this->activeQueueStatusValues())
            ->exists();

        if ($duplicate) {
            throw ValidationException::withMessages([
                'work_center_id' => __('Job is already queued on this work center.'),
            ]);
        }

        $workCenter = WorkCenter::query()->findOrFail($workCenterId);
        $position = $queuePosition ?? $this->nextQueuePosition($workCenter);
        $status = $assignedOperatorId !== null
            ? ProductionQueueStatus::Assigned
            : ProductionQueueStatus::Waiting;

        return DB::transaction(function () use ($jobCard, $workCenterId, $position, $assignedOperatorId, $status, $workCenter) {
            $entry = ProductionQueue::query()->create([
                'company_id' => $jobCard->company_id,
                'branch_id' => $jobCard->branch_id,
                'production_job_card_id' => $jobCard->id,
                'work_center_id' => $workCenterId,
                'queue_position' => $position,
                'assigned_operator_id' => $assignedOperatorId,
                'status' => $status,
            ]);

            $this->ordering->reorderWorkCenter($workCenter);

            if ($jobCard->status->canTransitionTo(ProductionJobCardStatus::Queued)) {
                $jobCard->fresh()->transitionTo(ProductionJobCardStatus::Queued);
            }

            return $entry->fresh(['workCenter', 'assignedOperator']);
        });
    }

    /**
     * @param  array{queue_position?: int, assigned_operator_id?: int|null, status?: ProductionQueueStatus}  $attributes
     */
    public function updateEntry(ProductionQueue $queue, array $attributes): ProductionQueue
    {
        $assignedOperatorId = array_key_exists('assigned_operator_id', $attributes)
            ? $attributes['assigned_operator_id']
            : $queue->assigned_operator_id;

        $status = $attributes['status'] ?? $queue->status;

        if (
            ! array_key_exists('status', $attributes)
            && array_key_exists('assigned_operator_id', $attributes)
        ) {
            $status = $assignedOperatorId !== null
                ? ProductionQueueStatus::Assigned
                : ProductionQueueStatus::Waiting;
        }

        $queue->update([
            'queue_position' => $attributes['queue_position'] ?? $queue->queue_position,
            'assigned_operator_id' => $assignedOperatorId,
            'status' => $status,
        ]);

        return $queue->fresh(['workCenter', 'assignedOperator']);
    }

    public function remove(ProductionQueue $queue): void
    {
        $jobCard = $queue->jobCard;

        DB::transaction(function () use ($queue, $jobCard) {
            $queue->delete();

            if ($jobCard === null) {
                return;
            }

            $jobCard->refresh();

            if (
                $jobCard->status === ProductionJobCardStatus::Queued
                && ! $this->hasActiveQueue($jobCard)
                && $jobCard->status->canTransitionTo(ProductionJobCardStatus::Draft)
            ) {
                static::withoutQueueEnforcement(fn () => $jobCard->transitionTo(ProductionJobCardStatus::Draft));
            }
        });
    }

    public function syncFromJobStatus(ProductionJobCard $jobCard, ProductionJobCardStatus $status): void
    {
        match ($status) {
            ProductionJobCardStatus::Queued => $this->assertQueuedHasActiveRecord($jobCard),
            ProductionJobCardStatus::OnHold => $this->markActiveQueues($jobCard, ProductionQueueStatus::Paused),
            ProductionJobCardStatus::InProduction => $this->markActiveQueues($jobCard, ProductionQueueStatus::InProgress),
            ProductionJobCardStatus::QualityCheck,
            ProductionJobCardStatus::Completed,
            ProductionJobCardStatus::ReadyForDispatch => $this->markActiveQueues($jobCard, ProductionQueueStatus::Completed),
            ProductionJobCardStatus::Cancelled => $this->markActiveQueues($jobCard, ProductionQueueStatus::Cancelled),
            ProductionJobCardStatus::Draft => $this->markActiveQueues($jobCard, ProductionQueueStatus::Cancelled),
            default => null,
        };
    }

    public function nextQueuePosition(WorkCenter $workCenter): int
    {
        $max = (int) ProductionQueue::query()
            ->where('work_center_id', $workCenter->id)
            ->max('queue_position');

        return $max + 1;
    }

    public function assertQueuedHasActiveRecord(ProductionJobCard $jobCard): void
    {
        if (static::isBypassingQueueEnforcement()) {
            return;
        }

        if ($this->hasActiveQueue($jobCard)) {
            return;
        }

        throw ValidationException::withMessages([
            'status' => __('Queued jobs must have an active production queue entry.'),
        ]);
    }

    protected function markActiveQueues(ProductionJobCard $jobCard, ProductionQueueStatus $status): void
    {
        ProductionQueue::query()
            ->where('production_job_card_id', $jobCard->id)
            ->whereIn('status', $this->activeQueueStatusValues())
            ->update(['status' => $status->value]);
    }

    protected function assertWorkCenterForJob(ProductionJobCard $jobCard, int $workCenterId): void
    {
        $exists = WorkCenter::query()
            ->where('id', $workCenterId)
            ->where('company_id', $jobCard->company_id)
            ->where('branch_id', $jobCard->branch_id)
            ->exists();

        if (! $exists) {
            throw ValidationException::withMessages([
                'work_center_id' => __('The selected work center is invalid for this job.'),
            ]);
        }
    }

    /**
     * @return list<string>
     */
    public function activeQueueStatusValues(): array
    {
        return array_map(
            fn (ProductionQueueStatus $status) => $status->value,
            ProductionQueueStatus::activeStatuses(),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function queueValidationRules(ProductionJobCard $jobCard): array
    {
        return [
            'work_center_id' => [
                'required',
                'integer',
                Rule::exists('work_centers', 'id')
                    ->where('company_id', $jobCard->company_id)
                    ->where('branch_id', $jobCard->branch_id),
            ],
            'queue_position' => ['nullable', 'integer', 'min:1'],
            'assigned_operator_id' => ['nullable', 'exists:users,id'],
        ];
    }
}
