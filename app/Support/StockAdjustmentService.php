<?php

namespace App\Support;

use App\Enums\ApprovalRuleType;
use App\Enums\InventoryMovementType;
use App\Enums\StockAdjustmentDirection;
use App\Enums\StockAdjustmentStatus;
use App\Models\Inventory\StockAdjustment;
use App\Models\User;
use App\Support\Accounting\InventoryAccountingPostingService;
use App\Support\Governance\ApprovalEnforcementEngine;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StockAdjustmentService
{
    public static function totalValue(StockAdjustment $adjustment): float
    {
        $adjustment->loadMissing('items');

        return round($adjustment->items->sum(
            fn ($line) => (float) $line->quantity * (float) $line->unit_cost
        ), 2);
    }

    public static function requiresApproval(StockAdjustment $adjustment): bool
    {
        return app(ApprovalEnforcementEngine::class)->requiresApproval(
            ApprovalRuleType::StockAdjustmentApproval,
            self::totalValue($adjustment),
            null,
            $adjustment->company_id,
            $adjustment->branch_id,
        );
    }

    public static function submit(StockAdjustment $adjustment, int $userId): StockAdjustment
    {
        if (! $adjustment->status->canSubmit()) {
            throw ValidationException::withMessages([
                'status' => __('Only draft adjustments can be submitted.'),
            ]);
        }

        if ($adjustment->items()->count() < 1) {
            throw ValidationException::withMessages([
                'items' => __('Adjustment must have at least one line.'),
            ]);
        }

        $amount = self::totalValue($adjustment);

        if (self::requiresApproval($adjustment)) {
            app(ApprovalEnforcementEngine::class)->beginApproval(
                $adjustment,
                ApprovalRuleType::StockAdjustmentApproval,
                ['amount' => $amount],
            );
        }

        $adjustment->update([
            'status' => StockAdjustmentStatus::Submitted,
            'submitted_by' => $userId,
            'submitted_at' => now(),
        ]);

        ActivityLogger::log('stock_adjustment_submitted', $adjustment, $userId);

        return $adjustment->fresh(['items', 'warehouse', 'submitter']);
    }

    public static function approve(StockAdjustment $adjustment, int $userId, ?string $reason = null): StockAdjustment
    {
        if (! $adjustment->status->canApprove()) {
            throw ValidationException::withMessages([
                'status' => __('Only submitted adjustments can be approved.'),
            ]);
        }

        $actor = User::query()->findOrFail($userId);
        $engine = app(ApprovalEnforcementEngine::class);

        $engine->recordApproval($adjustment, $actor, $reason);

        if ($engine->hasApprovedChain($adjustment->fresh())) {
            $adjustment->update([
                'status' => StockAdjustmentStatus::Approved,
                'approved_by' => $userId,
                'approved_at' => now(),
                'approval_reason' => $reason,
            ]);

            ActivityLogger::log('stock_adjustment_approved', $adjustment, $userId, ['reason' => $reason]);
        }

        return $adjustment->fresh(['items', 'warehouse', 'submitter', 'approver']);
    }

    public static function post(StockAdjustment $adjustment, int $userId): StockAdjustment
    {
        if ($adjustment->status === StockAdjustmentStatus::Posted) {
            throw ValidationException::withMessages([
                'adjustment' => __('Adjustment already posted.'),
            ]);
        }

        if ($adjustment->items()->count() < 1) {
            throw ValidationException::withMessages([
                'items' => __('Adjustment must have at least one line.'),
            ]);
        }

        if (! $adjustment->warehouse?->is_active) {
            throw ValidationException::withMessages([
                'warehouse_id' => __('Deactivated warehouses cannot be adjusted.'),
            ]);
        }

        $amount = self::totalValue($adjustment);
        $requiresApproval = self::requiresApproval($adjustment);
        $engine = app(ApprovalEnforcementEngine::class);

        if ($requiresApproval) {
            if ($adjustment->status === StockAdjustmentStatus::Draft) {
                $engine->assertChainApprovedForPosting(
                    $adjustment,
                    ApprovalRuleType::StockAdjustmentApproval,
                    ['amount' => $amount],
                );
            } else {
                $engine->assertPostingAllowed(
                    $adjustment,
                    ApprovalRuleType::StockAdjustmentApproval,
                    $adjustment->status === StockAdjustmentStatus::Approved,
                    ['amount' => $amount],
                );
            }
        } elseif (! $adjustment->status->canPost(false)) {
            throw ValidationException::withMessages([
                'status' => __('Adjustment cannot be posted in its current status.'),
            ]);
        }

        return DB::transaction(function () use ($adjustment, $userId) {
            $adjustment->load('items.inventoryItem');

            foreach ($adjustment->items as $line) {
                $qty = (float) $line->quantity;
                $signed = $line->direction === StockAdjustmentDirection::Increase
                    ? InventoryMovementService::receiptQuantity($qty)
                    : InventoryMovementService::issueQuantity($qty);

                if ($line->direction === StockAdjustmentDirection::Decrease) {
                    InventoryStockService::assertSufficientStock(
                        $line->inventory_item_id,
                        $adjustment->warehouse_id,
                        $qty,
                    );
                }

                InventoryMovementService::record([
                    'company_id' => $adjustment->company_id,
                    'branch_id' => $adjustment->branch_id,
                    'inventory_item_id' => $line->inventory_item_id,
                    'warehouse_id' => $adjustment->warehouse_id,
                    'movement_type' => InventoryMovementType::Adjustment,
                    'quantity' => $signed,
                    'unit_cost' => $line->unit_cost,
                    'reference_type' => StockAdjustment::class,
                    'reference_id' => $adjustment->id,
                    'movement_date' => $adjustment->adjustment_date,
                    'created_by' => $userId,
                ]);
            }

            $adjustment->update([
                'status' => StockAdjustmentStatus::Posted,
                'posted_at' => now(),
            ]);

            app(InventoryAccountingPostingService::class)->postStockAdjustment($adjustment, $userId);

            return $adjustment->fresh(['items', 'warehouse']);
        });
    }
}
