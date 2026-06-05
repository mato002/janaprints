<?php

namespace App\Support\Inventory;

use App\Enums\CycleCountScheduleStatus;
use App\Enums\StockCountType;
use App\Models\Inventory\CycleCountSchedule;
use App\Models\Inventory\InventoryItem;
use App\Models\Inventory\StockCount;
use App\Support\ActivityLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CycleCountService
{
    public static function createSchedule(
        int $companyId,
        int $branchId,
        int $warehouseId,
        string $frequency,
        string $nextCountDate,
        int $responsibleUserId,
        ?int $categoryId = null,
        ?string $notes = null,
    ): CycleCountSchedule {
        $schedule = CycleCountSchedule::query()->create([
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'warehouse_id' => $warehouseId,
            'inventory_category_id' => $categoryId,
            'frequency' => $frequency,
            'next_count_date' => $nextCountDate,
            'responsible_user_id' => $responsibleUserId,
            'status' => CycleCountScheduleStatus::Active,
            'notes' => $notes,
        ]);

        ActivityLogger::log('cycle_count_schedule_created', $schedule);

        return $schedule->fresh(['warehouse', 'category', 'responsibleUser']);
    }

    public static function generateCount(CycleCountSchedule $schedule, int $userId): StockCount
    {
        if ($schedule->status !== CycleCountScheduleStatus::Active) {
            throw ValidationException::withMessages([
                'status' => __('Inactive schedules cannot generate counts.'),
            ]);
        }

        return DB::transaction(function () use ($schedule, $userId) {
            $itemIds = [];

            if ($schedule->inventory_category_id) {
                $itemIds = InventoryItem::query()
                    ->where('company_id', $schedule->company_id)
                    ->where('branch_id', $schedule->branch_id)
                    ->where('inventory_category_id', $schedule->inventory_category_id)
                    ->where('is_active', true)
                    ->pluck('id')
                    ->all();
            }

            $countType = $schedule->inventory_category_id
                ? StockCountType::Partial
                : StockCountType::Full;

            $count = StockCountService::create(
                companyId: $schedule->company_id,
                branchId: $schedule->branch_id,
                warehouseId: $schedule->warehouse_id,
                countType: $countType,
                countDate: now()->toDateString(),
                userId: $userId,
                notes: __('Generated from cycle count schedule #:id', ['id' => $schedule->id]),
                itemIds: $itemIds,
                cycleCountScheduleId: $schedule->id,
            );

            $schedule->update([
                'next_count_date' => $schedule->frequency->nextDate($schedule->next_count_date),
            ]);

            ActivityLogger::log('cycle_count_generated', $schedule, $userId, [
                'stock_count_id' => $count->id,
                'count_number' => $count->count_number,
            ]);

            return $count;
        });
    }

    public static function deactivate(CycleCountSchedule $schedule, int $userId): CycleCountSchedule
    {
        $schedule->update(['status' => CycleCountScheduleStatus::Inactive]);
        ActivityLogger::log('cycle_count_schedule_deactivated', $schedule, $userId);

        return $schedule->fresh();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, CycleCountSchedule>
     */
    public static function overdueSchedules(int $companyId, ?int $branchId = null)
    {
        $query = CycleCountSchedule::query()
            ->where('company_id', $companyId)
            ->where('status', CycleCountScheduleStatus::Active)
            ->whereDate('next_count_date', '<', now()->toDateString())
            ->with(['warehouse', 'category', 'responsibleUser']);

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        return $query->orderBy('next_count_date')->get();
    }
}
