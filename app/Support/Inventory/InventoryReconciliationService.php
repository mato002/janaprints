<?php

namespace App\Support\Inventory;

use App\Enums\InventoryReconciliationStatus;
use App\Enums\StockCountStatus;
use App\Models\Inventory\InventoryReconciliation;
use App\Models\Inventory\StockCount;
use App\Support\ActivityLogger;
use App\Support\StockAdjustmentService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InventoryReconciliationService
{
    public static function approve(InventoryReconciliation $reconciliation, int $userId): InventoryReconciliation
    {
        if (! $reconciliation->status->canApprove()) {
            throw ValidationException::withMessages([
                'status' => __('Only pending reconciliations can be approved.'),
            ]);
        }

        $count = $reconciliation->stockCount;

        if ($count->status !== StockCountStatus::Approved) {
            throw ValidationException::withMessages([
                'stock_count' => __('Stock count must be approved before reconciliation.'),
            ]);
        }

        $reconciliation->update([
            'status' => InventoryReconciliationStatus::Approved,
            'approved_by' => $userId,
            'approved_at' => now(),
        ]);

        ActivityLogger::log('inventory_reconciliation_approved', $reconciliation, $userId);

        return $reconciliation->fresh(['stockCount']);
    }

    public static function post(InventoryReconciliation $reconciliation, int $userId): StockCount
    {
        if ($reconciliation->status === InventoryReconciliationStatus::Posted) {
            throw ValidationException::withMessages([
                'status' => __('Reconciliation already posted.'),
            ]);
        }

        if ($reconciliation->stock_adjustment_id) {
            throw ValidationException::withMessages([
                'status' => __('Duplicate posting is not allowed.'),
            ]);
        }

        $count = $reconciliation->stockCount;

        if ($count->status !== StockCountStatus::Approved) {
            throw ValidationException::withMessages([
                'stock_count' => __('Stock count must be approved before posting.'),
            ]);
        }

        if ($count->stock_adjustment_id) {
            throw ValidationException::withMessages([
                'stock_count' => __('Stock count variances already posted.'),
            ]);
        }

        return DB::transaction(function () use ($reconciliation, $count, $userId) {
            if ($reconciliation->status === InventoryReconciliationStatus::Pending) {
                $reconciliation->update([
                    'status' => InventoryReconciliationStatus::Approved,
                    'approved_by' => $userId,
                    'approved_at' => now(),
                ]);
            }

            $adjustment = StockCountService::buildAdjustmentFromVariances($count, $userId);
            StockAdjustmentService::post($adjustment, $userId);

            $now = now();

            $reconciliation->update([
                'status' => InventoryReconciliationStatus::Posted,
                'posted_by' => $userId,
                'posted_at' => $now,
                'stock_adjustment_id' => $adjustment->id,
            ]);

            $count->update([
                'status' => StockCountStatus::Posted,
                'posted_by' => $userId,
                'posted_at' => $now,
                'stock_adjustment_id' => $adjustment->id,
            ]);

            $count->update(['status' => StockCountStatus::Closed]);
            $reconciliation->update(['status' => InventoryReconciliationStatus::Closed]);

            ActivityLogger::log('inventory_reconciliation_posted', $reconciliation, $userId, [
                'stock_adjustment_id' => $adjustment->id,
                'adjustment_number' => $adjustment->adjustment_number,
            ]);

            ActivityLogger::log('stock_count_posted', $count, $userId, [
                'stock_adjustment_id' => $adjustment->id,
            ]);

            return $count->fresh(['items', 'stockAdjustment', 'reconciliation']);
        });
    }
}
