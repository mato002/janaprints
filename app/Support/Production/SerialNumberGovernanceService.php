<?php

namespace App\Support\Production;

use App\Models\Crm\Customer;
use App\Models\Crm\CustomerProductSerialProfile;
use App\Models\Inventory\InventoryItem;
use App\Models\Production\JobCardSerialAllocation;
use App\Models\Production\JobCardSpoiledSerialRange;
use App\Models\Production\ProductionJobCard;
use App\Models\Production\SerialNumberCounter;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SerialNumberGovernanceService
{
    /**
     * @return array{prefix: string, padding_length: int}
     */
    public function resolveProfile(InventoryItem $item, ?Customer $customer): array
    {
        if ($customer) {
            $override = CustomerProductSerialProfile::query()
                ->where('customer_id', $customer->id)
                ->where('inventory_item_id', $item->id)
                ->first();

            if ($override) {
                return [
                    'prefix' => $override->serial_prefix,
                    'padding_length' => $override->serial_padding_length,
                ];
            }
        }

        return [
            'prefix' => (string) ($item->serial_prefix ?? ''),
            'padding_length' => (int) ($item->serial_padding_length ?: 6),
        ];
    }

    public function allocateForJobCard(
        ProductionJobCard $jobCard,
        InventoryItem $item,
        int $quantity,
    ): ?JobCardSerialAllocation {
        if (! $item->uses_serial_numbers || $quantity <= 0) {
            return null;
        }

        $profile = $this->resolveProfile($item, $jobCard->customer);

        return DB::transaction(function () use ($jobCard, $item, $quantity, $profile) {
            $counter = SerialNumberCounter::query()->firstOrCreate(
                [
                    'company_id' => $jobCard->company_id,
                    'branch_id' => $jobCard->branch_id,
                    'inventory_item_id' => $item->id,
                    'customer_id' => $jobCard->customer_id,
                ],
                ['last_serial_number' => 0],
            );

            $serialStart = $counter->last_serial_number + 1;
            $serialEnd = $serialStart + $quantity - 1;

            $counter->update(['last_serial_number' => $serialEnd]);

            return JobCardSerialAllocation::query()->create([
                'company_id' => $jobCard->company_id,
                'branch_id' => $jobCard->branch_id,
                'production_job_card_id' => $jobCard->id,
                'inventory_item_id' => $item->id,
                'serial_prefix' => $profile['prefix'],
                'serial_padding_length' => $profile['padding_length'],
                'serial_start' => $serialStart,
                'serial_end' => $serialEnd,
            ]);
        });
    }

    /**
     * @param  array{produced_end: int, spoiled_start?: int, spoiled_end?: int}  $payload
     */
    public function confirmProduction(
        JobCardSerialAllocation $allocation,
        array $payload,
        int $userId,
    ): JobCardSerialAllocation {
        $producedEnd = (int) $payload['produced_end'];
        $spoiledStart = isset($payload['spoiled_start']) ? (int) $payload['spoiled_start'] : null;
        $spoiledEnd = isset($payload['spoiled_end']) ? (int) $payload['spoiled_end'] : null;

        if ($producedEnd < $allocation->serial_start || $producedEnd > $allocation->serial_end) {
            throw ValidationException::withMessages([
                'produced_end' => __('Produced end must be within allocated range.'),
            ]);
        }

        return DB::transaction(function () use ($allocation, $producedEnd, $spoiledStart, $spoiledEnd, $userId) {
            $spoiledQty = 0;

            if ($spoiledStart !== null && $spoiledEnd !== null) {
                if ($spoiledStart < $allocation->serial_start || $spoiledEnd > $allocation->serial_end) {
                    throw ValidationException::withMessages([
                        'spoiled_end' => __('Spoiled range must be within allocated range.'),
                    ]);
                }

                if ($spoiledStart <= $producedEnd) {
                    throw ValidationException::withMessages([
                        'spoiled_start' => __('Spoiled serials must be after produced range.'),
                    ]);
                }

                $this->assertRangeNotSpoiled($allocation->inventory_item_id, $spoiledStart, $spoiledEnd);

                $spoiledQty = $spoiledEnd - $spoiledStart + 1;

                JobCardSpoiledSerialRange::query()->create([
                    'production_job_card_id' => $allocation->production_job_card_id,
                    'inventory_item_id' => $allocation->inventory_item_id,
                    'serial_start' => $spoiledStart,
                    'serial_end' => $spoiledEnd,
                    'quantity' => $spoiledQty,
                    'recorded_by' => $userId,
                    'recorded_at' => now(),
                ]);
            }

            $allocation->update([
                'produced_end' => $producedEnd,
                'spoiled_quantity' => $spoiledQty,
                'is_confirmed' => true,
                'confirmed_by' => $userId,
                'confirmed_at' => now(),
            ]);

            return $allocation->fresh(['spoiledRanges', 'confirmedByUser:id,name']);
        });
    }

    protected function assertRangeNotSpoiled(int $inventoryItemId, int $start, int $end): void
    {
        $overlap = JobCardSpoiledSerialRange::query()
            ->where('inventory_item_id', $inventoryItemId)
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('serial_start', [$start, $end])
                    ->orWhereBetween('serial_end', [$start, $end])
                    ->orWhere(function ($q2) use ($start, $end) {
                        $q2->where('serial_start', '<=', $start)->where('serial_end', '>=', $end);
                    });
            })
            ->exists();

        if ($overlap) {
            throw ValidationException::withMessages([
                'spoiled_start' => __('These serial numbers were previously spoiled and cannot be reused.'),
            ]);
        }
    }

    /**
     * @return array{allocated: int, spoiled: int, production_loss_quantity: int}
     */
    public function productionLossMetrics(ProductionJobCard $jobCard): array
    {
        $allocation = $jobCard->relationLoaded('serialAllocation')
            ? $jobCard->serialAllocation
            : $jobCard->serialAllocation()->first();

        $spoiled = (int) ($allocation?->spoiled_quantity ?? 0);

        return [
            'allocated' => $allocation?->allocatedQuantity() ?? 0,
            'spoiled' => $spoiled,
            'production_loss_quantity' => $spoiled,
        ];
    }
}
