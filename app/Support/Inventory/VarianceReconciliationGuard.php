<?php

namespace App\Support\Inventory;

use App\Models\Inventory\InventoryVarianceReasonCode;
use App\Models\Inventory\StockCount;
use App\Models\Inventory\StockCountItem;
use Illuminate\Validation\ValidationException;

class VarianceReconciliationGuard
{
    public static function assertStockCountExplained(StockCount $count): void
    {
        $count->loadMissing(['items.inventoryItem', 'items.varianceReasonCode']);

        $errors = [];

        foreach ($count->items as $index => $item) {
            $message = self::validateLine($item);

            if ($message !== null) {
                $label = $item->inventoryItem?->item_name ?? __('Item :id', ['id' => $item->inventory_item_id]);
                $errors["items.{$index}"] = "{$label}: {$message}";
            }
        }

        if ($errors !== []) {
            throw ValidationException::withMessages([
                'variance' => array_values($errors),
            ]);
        }
    }

    public static function validateLine(StockCountItem $item): ?string
    {
        if (! self::hasVariance($item)) {
            return null;
        }

        $reasonCode = $item->varianceReasonCode;

        if ($reasonCode !== null) {
            if (! $reasonCode->is_active) {
                return __('Selected variance reason code is inactive.');
            }

            if ($reasonCode->requires_comment && ! self::hasComment($item)) {
                return __('A comment is required for the selected variance reason.');
            }

            return null;
        }

        if (filled($item->reason)) {
            return null;
        }

        return __('Variance reason code or explanation is required.');
    }

    public static function resolveReasonCode(?int $reasonCodeId, int $companyId): ?InventoryVarianceReasonCode
    {
        if ($reasonCodeId === null) {
            return null;
        }

        $reasonCode = InventoryVarianceReasonCode::query()
            ->where('company_id', $companyId)
            ->find($reasonCodeId);

        if ($reasonCode === null) {
            throw ValidationException::withMessages([
                'inventory_variance_reason_code_id' => __('Variance reason code not found.'),
            ]);
        }

        if (! $reasonCode->is_active) {
            throw ValidationException::withMessages([
                'inventory_variance_reason_code_id' => __('Inactive variance reason codes cannot be used.'),
            ]);
        }

        return $reasonCode;
    }

    protected static function hasVariance(StockCountItem $item): bool
    {
        return abs((float) $item->variance_quantity) >= 0.001;
    }

    protected static function hasComment(StockCountItem $item): bool
    {
        return filled($item->notes) || filled($item->reason);
    }
}
