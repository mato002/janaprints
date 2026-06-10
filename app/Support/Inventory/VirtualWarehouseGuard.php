<?php

namespace App\Support\Inventory;

use App\Enums\VirtualWarehouseRole;
use App\Models\Inventory\InventoryMovement;
use App\Models\Inventory\Warehouse;
use Illuminate\Validation\ValidationException;

class VirtualWarehouseGuard
{
    protected static bool $systemContext = false;

    /**
     * @template TReturn
     *
     * @param  callable(): TReturn  $callback
     * @return TReturn
     */
    public static function usingSystemContext(callable $callback): mixed
    {
        $previous = self::$systemContext;
        self::$systemContext = true;

        try {
            return $callback();
        } finally {
            self::$systemContext = $previous;
        }
    }

    public static function isSystemContext(): bool
    {
        return self::$systemContext;
    }

    public static function assertDirectReceiptAllowed(?Warehouse $warehouse): void
    {
        if ($warehouse === null || ! $warehouse->is_virtual) {
            return;
        }

        if (self::$systemContext) {
            return;
        }

        throw ValidationException::withMessages([
            'warehouse_id' => __('Virtual warehouses cannot receive stock directly. Use the appropriate operational workflow.'),
        ]);
    }

    public static function assertDeletable(Warehouse $warehouse): void
    {
        if (! $warehouse->is_virtual) {
            return;
        }

        if (self::hasMovements($warehouse)) {
            throw ValidationException::withMessages([
                'warehouse' => __('Virtual warehouses with movement history cannot be deleted.'),
            ]);
        }

        throw ValidationException::withMessages([
            'warehouse' => __('System virtual warehouses cannot be deleted.'),
        ]);
    }

    public static function assertVirtualRoleMutable(Warehouse $warehouse, ?VirtualWarehouseRole $newRole): void
    {
        if (! $warehouse->is_virtual) {
            return;
        }

        if ($newRole === $warehouse->virtual_role) {
            return;
        }

        if (self::hasMovements($warehouse)) {
            throw ValidationException::withMessages([
                'virtual_role' => __('Virtual warehouse role cannot be changed after movements exist.'),
            ]);
        }
    }

    public static function hasMovements(Warehouse $warehouse): bool
    {
        return InventoryMovement::query()
            ->where('warehouse_id', $warehouse->id)
            ->exists();
    }
}
