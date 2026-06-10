<?php

namespace App\Support\Inventory;

use App\Enums\InventoryMovementType;
use Illuminate\Support\Collection;

class InventoryMovementAnalytics
{
    /**
     * @return list<InventoryMovementType>
     */
    public static function outboundTypes(): array
    {
        return [
            InventoryMovementType::Issue,
            InventoryMovementType::TransferOut,
            InventoryMovementType::ProductionConsumption,
            InventoryMovementType::DispatchToTransit,
            InventoryMovementType::DeliveryCogs,
        ];
    }

    /**
     * @return list<InventoryMovementType>
     */
    public static function inboundTypes(): array
    {
        return [
            InventoryMovementType::Receipt,
            InventoryMovementType::TransferIn,
            InventoryMovementType::FinishedGoodsReceipt,
            InventoryMovementType::ProductionOutput,
        ];
    }

    /**
     * @return list<string>
     */
    public static function outboundTypeValues(): array
    {
        return array_map(fn (InventoryMovementType $type) => $type->value, self::outboundTypes());
    }

    /**
     * @return list<string>
     */
    public static function inboundTypeValues(): array
    {
        return array_map(fn (InventoryMovementType $type) => $type->value, self::inboundTypes());
    }

    /**
     * Dispatch to transit creates paired movements; count only negative qty as outbound.
     */
    public static function isOutboundMovement(InventoryMovementType $type, float $quantity): bool
    {
        if ($type === InventoryMovementType::DispatchToTransit) {
            return $quantity < 0;
        }

        return in_array($type, self::outboundTypes(), true);
    }

    public static function isInboundMovement(InventoryMovementType $type, float $quantity): bool
    {
        if ($type === InventoryMovementType::DispatchToTransit) {
            return $quantity > 0;
        }

        if ($type === InventoryMovementType::Adjustment) {
            return $quantity > 0;
        }

        return in_array($type, self::inboundTypes(), true);
    }
}
