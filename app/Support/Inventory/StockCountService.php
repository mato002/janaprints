<?php

namespace App\Support\Inventory;

use App\Enums\DocumentType;
use App\Enums\InventoryDocumentStatus;
use App\Enums\InventoryReconciliationStatus;
use App\Enums\StockAdjustmentDirection;
use App\Enums\StockCountStatus;
use App\Enums\StockCountType;
use App\Models\Inventory\InventoryItem;
use App\Models\Inventory\InventoryReconciliation;
use App\Models\Inventory\StockAdjustment;
use App\Models\Inventory\StockCount;
use App\Models\Inventory\StockCountItem;
use App\Support\ActivityLogger;
use App\Support\InventoryStockService;
use App\Support\Platform\NumberingService;
use App\Support\StockAdjustmentService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StockCountService
{
    /**
     * @param  list<int>  $itemIds
     */
    public static function create(
        int $companyId,
        int $branchId,
        int $warehouseId,
        StockCountType $countType,
        string $countDate,
        int $userId,
        ?string $notes = null,
        array $itemIds = [],
        ?int $cycleCountScheduleId = null,
    ): StockCount {
        return DB::transaction(function () use (
            $companyId, $branchId, $warehouseId, $countType, $countDate,
            $userId, $notes, $itemIds, $cycleCountScheduleId,
        ) {
            $count = StockCount::query()->create([
                'company_id' => $companyId,
                'branch_id' => $branchId,
                'warehouse_id' => $warehouseId,
                'cycle_count_schedule_id' => $cycleCountScheduleId,
                'count_number' => app(NumberingService::class)->next(
                    DocumentType::StockCount,
                    $companyId,
                    $branchId,
                ),
                'count_type' => $countType,
                'count_date' => $countDate,
                'status' => StockCountStatus::Draft,
                'notes' => $notes,
                'created_by' => $userId,
            ]);

            self::snapshotSystemQuantities($count, $itemIds);

            ActivityLogger::log('stock_count_created', $count, $userId);

            return $count->fresh(['items.inventoryItem', 'warehouse']);
        });
    }

    /**
     * @param  list<int>  $itemIds
     */
    public static function snapshotSystemQuantities(StockCount $count, array $itemIds = []): void
    {
        $query = InventoryItem::query()
            ->where('company_id', $count->company_id)
            ->where('branch_id', $count->branch_id)
            ->where('is_active', true);

        if ($count->count_type === StockCountType::Partial && $itemIds !== []) {
            $query->whereIn('id', $itemIds);
        }

        foreach ($query->get() as $item) {
            $balance = InventoryStockService::balanceUncached($item->id, $count->warehouse_id);
            $unitCost = (float) ($item->standard_cost ?: 0);

            if ($count->count_type === StockCountType::Full || in_array($item->id, $itemIds, true) || $balance > 0) {
                StockCountItem::query()->updateOrCreate(
                    [
                        'stock_count_id' => $count->id,
                        'inventory_item_id' => $item->id,
                    ],
                    [
                        'system_quantity' => $balance,
                        'counted_quantity' => null,
                        'variance_quantity' => 0,
                        'system_unit_cost' => $unitCost,
                        'variance_value' => 0,
                    ],
                );
            }
        }
    }

    public static function start(StockCount $count, int $userId): StockCount
    {
        if ($count->status !== StockCountStatus::Draft) {
            throw ValidationException::withMessages([
                'status' => __('Only draft counts can be started.'),
            ]);
        }

        $count->update(['status' => StockCountStatus::InProgress]);
        ActivityLogger::log('stock_count_started', $count, $userId);

        return $count->fresh();
    }

    /**
     * @param  list<array{inventory_item_id: int, counted_quantity: float, inventory_variance_reason_code_id?: int|null, reason?: string|null, notes?: string|null}>  $lines
     */
    public static function updateCountedQuantities(StockCount $count, array $lines, int $userId): StockCount
    {
        if (! $count->status->isEditable()) {
            throw ValidationException::withMessages([
                'status' => __('Count cannot be edited in its current status.'),
            ]);
        }

        foreach ($lines as $line) {
            if ((float) $line['counted_quantity'] < 0) {
                throw ValidationException::withMessages([
                    'counted_quantity' => __('Counted quantity cannot be negative.'),
                ]);
            }

            $item = $count->items()->where('inventory_item_id', $line['inventory_item_id'])->first();

            if (! $item) {
                continue;
            }

            $counted = (float) $line['counted_quantity'];
            $variance = $counted - (float) $item->system_quantity;
            $varianceValue = $variance * (float) $item->system_unit_cost;

            $reasonCodeId = array_key_exists('inventory_variance_reason_code_id', $line)
                ? ($line['inventory_variance_reason_code_id'] !== null && $line['inventory_variance_reason_code_id'] !== ''
                    ? (int) $line['inventory_variance_reason_code_id']
                    : null)
                : $item->inventory_variance_reason_code_id;

            if ($reasonCodeId !== null) {
                VarianceReconciliationGuard::resolveReasonCode($reasonCodeId, (int) $count->company_id);
            }

            $item->update([
                'counted_quantity' => $counted,
                'variance_quantity' => $variance,
                'variance_value' => $varianceValue,
                'inventory_variance_reason_code_id' => $reasonCodeId,
                'reason' => $line['reason'] ?? $item->reason,
                'notes' => $line['notes'] ?? $item->notes,
            ]);
        }

        if ($count->status === StockCountStatus::Draft) {
            $count->update(['status' => StockCountStatus::InProgress]);
        }

        return $count->fresh(['items.inventoryItem']);
    }

    public static function submit(StockCount $count, int $userId): StockCount
    {
        if (! $count->status->canSubmit()) {
            throw ValidationException::withMessages([
                'status' => __('Count cannot be submitted in its current status.'),
            ]);
        }

        $count->load('items');

        $uncounted = $count->items->filter(fn ($item) => $item->counted_quantity === null);

        if ($uncounted->isNotEmpty()) {
            throw ValidationException::withMessages([
                'items' => __('All items must have counted quantities before submission.'),
            ]);
        }

        $count->update([
            'status' => StockCountStatus::Submitted,
            'submitted_by' => $userId,
            'submitted_at' => now(),
        ]);

        ActivityLogger::log('stock_count_submitted', $count, $userId);

        return $count->fresh(['items']);
    }

    public static function approve(StockCount $count, int $userId): StockCount
    {
        if (! $count->status->canApprove()) {
            throw ValidationException::withMessages([
                'status' => __('Only submitted counts can be approved.'),
            ]);
        }

        return DB::transaction(function () use ($count, $userId) {
            $count->load(['items.inventoryItem', 'items.varianceReasonCode']);
            VarianceReconciliationGuard::assertStockCountExplained($count);

            $count->update([
                'status' => StockCountStatus::Approved,
                'approved_by' => $userId,
                'approved_at' => now(),
            ]);

            InventoryReconciliation::query()->firstOrCreate(
                ['stock_count_id' => $count->id],
                [
                    'company_id' => $count->company_id,
                    'branch_id' => $count->branch_id,
                    'reconciliation_number' => app(NumberingService::class)->next(
                        DocumentType::InventoryReconciliation,
                        $count->company_id,
                        $count->branch_id,
                    ),
                    'status' => InventoryReconciliationStatus::Pending,
                ],
            );

            ActivityLogger::log('stock_count_approved', $count, $userId);

            return $count->fresh(['reconciliation']);
        });
    }

    public static function post(StockCount $count, int $userId): StockCount
    {
        if (! $count->status->canPost()) {
            throw ValidationException::withMessages([
                'status' => __('Only approved counts can be posted.'),
            ]);
        }

        if ($count->stock_adjustment_id) {
            throw ValidationException::withMessages([
                'status' => __('Count variances have already been posted.'),
            ]);
        }

        $reconciliation = $count->reconciliation;

        if (! $reconciliation) {
            throw ValidationException::withMessages([
                'reconciliation' => __('Reconciliation record not found.'),
            ]);
        }

        return InventoryReconciliationService::post($reconciliation, $userId);
    }

    public static function cancel(StockCount $count, int $userId): StockCount
    {
        if (! $count->status->canCancel()) {
            throw ValidationException::withMessages([
                'status' => __('Count cannot be cancelled in its current status.'),
            ]);
        }

        $count->update(['status' => StockCountStatus::Cancelled]);
        ActivityLogger::log('stock_count_cancelled', $count, $userId);

        return $count->fresh();
    }

    public static function close(StockCount $count, int $userId): StockCount
    {
        if ($count->status !== StockCountStatus::Posted) {
            throw ValidationException::withMessages([
                'status' => __('Only posted counts can be closed.'),
            ]);
        }

        $count->update(['status' => StockCountStatus::Closed]);
        ActivityLogger::log('stock_count_closed', $count, $userId);

        return $count->fresh();
    }

    public static function buildAdjustmentFromVariances(StockCount $count, int $userId): StockAdjustment
    {
        $count->load('items.inventoryItem');

        $varianceLines = $count->items->filter(fn ($item) => (float) $item->variance_quantity !== 0.0);

        if ($varianceLines->isEmpty()) {
            throw ValidationException::withMessages([
                'items' => __('No variances to post.'),
            ]);
        }

        $adjustment = StockAdjustment::query()->create([
            'company_id' => $count->company_id,
            'branch_id' => $count->branch_id,
            'warehouse_id' => $count->warehouse_id,
            'adjustment_number' => app(NumberingService::class)->next(
                DocumentType::StockAdjustment,
                $count->company_id,
                $count->branch_id,
            ),
            'adjustment_date' => $count->count_date,
            'status' => InventoryDocumentStatus::Draft,
            'reason' => __('Stock count variance: :number', ['number' => $count->count_number]),
            'adjusted_by' => $userId,
        ]);

        foreach ($varianceLines as $line) {
            $variance = (float) $line->variance_quantity;
            $direction = $variance > 0
                ? StockAdjustmentDirection::Increase
                : StockAdjustmentDirection::Decrease;

            $adjustment->items()->create([
                'inventory_item_id' => $line->inventory_item_id,
                'direction' => $direction,
                'quantity' => abs($variance),
                'unit_cost' => (float) $line->system_unit_cost,
            ]);
        }

        return $adjustment;
    }
}
