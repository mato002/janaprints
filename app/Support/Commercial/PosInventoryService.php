<?php

namespace App\Support\Commercial;

use App\Enums\InventoryMovementType;
use App\Enums\PosSaleStatus;
use App\Models\Inventory\InventoryMovement;
use App\Models\Inventory\Warehouse;
use App\Models\Pos\PosReturn;
use App\Models\Pos\PosSale;
use App\Models\Pos\PosSaleItem;
use App\Support\InventoryMovementService;
use App\Support\InventoryStockService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PosInventoryService
{
    public const REFERENCE_TYPE_POS_SALE = 'POS_SALE';

    public const REFERENCE_TYPE_POS_RETURN = 'POS_RETURN';

    public function postPaidSale(PosSale $sale, int $userId): void
    {
        if ($sale->status !== PosSaleStatus::Paid) {
            return;
        }

        if ($this->hasPostedSale($sale)) {
            return;
        }

        $sale->loadMissing('items');

        $stockLines = $this->stockLines($sale->items);

        if ($stockLines === []) {
            return;
        }

        $warehouseId = $this->resolveWarehouseId($sale->company_id, $sale->branch_id);

        DB::transaction(function () use ($sale, $userId, $warehouseId, $stockLines) {
            foreach ($stockLines as $line) {
                $this->assertCanIssue(
                    $sale->company_id,
                    $sale->branch_id,
                    $line['inventory_item_id'],
                    $warehouseId,
                    $line['quantity'],
                );

                InventoryMovementService::record([
                    'company_id' => $sale->company_id,
                    'branch_id' => $sale->branch_id,
                    'inventory_item_id' => $line['inventory_item_id'],
                    'warehouse_id' => $warehouseId,
                    'movement_type' => InventoryMovementType::Issue,
                    'quantity' => InventoryMovementService::issueQuantity($line['quantity']),
                    'reference_type' => self::REFERENCE_TYPE_POS_SALE,
                    'reference_id' => $sale->id,
                    'movement_date' => $sale->sale_date?->toDateString() ?? now()->toDateString(),
                    'created_by' => $userId,
                ]);
            }
        });
    }

    public function restoreReturn(PosReturn $return, int $userId): void
    {
        if ($this->hasPostedReturn($return)) {
            return;
        }

        $return->loadMissing(['items.saleItem', 'sale']);

        $restockLines = $this->returnStockLines($return);

        if ($restockLines === []) {
            return;
        }

        $warehouseId = $this->resolveWarehouseId($return->company_id, $return->branch_id);

        DB::transaction(function () use ($return, $userId, $warehouseId, $restockLines) {
            foreach ($restockLines as $line) {
                $qty = $line['quantity'];

                InventoryMovementService::record([
                    'company_id' => $return->company_id,
                    'branch_id' => $return->branch_id,
                    'inventory_item_id' => $line['inventory_item_id'],
                    'warehouse_id' => $warehouseId,
                    'movement_type' => InventoryMovementType::Receipt,
                    'quantity' => InventoryMovementService::receiptQuantity($qty),
                    'reference_type' => self::REFERENCE_TYPE_POS_RETURN,
                    'reference_id' => $return->id,
                    'movement_date' => now()->toDateString(),
                    'created_by' => $userId,
                ]);
            }
        });
    }

    /**
     * @return list<array{inventory_item_id: int, quantity: float}>
     */
    protected function returnStockLines(PosReturn $return): array
    {
        $lines = [];

        foreach ($return->items as $returnItem) {
            $saleItem = $returnItem->saleItem;

            if ($saleItem === null || $saleItem->inventory_item_id === null) {
                continue;
            }

            $qty = (float) $returnItem->quantity_returned;

            if ($qty <= 0) {
                continue;
            }

            $lines[] = [
                'inventory_item_id' => (int) $saleItem->inventory_item_id,
                'quantity' => $qty,
            ];
        }

        return $lines;
    }

    public function hasPostedSale(PosSale $sale): bool
    {
        return InventoryMovement::query()
            ->where('reference_type', self::REFERENCE_TYPE_POS_SALE)
            ->where('reference_id', $sale->id)
            ->exists();
    }

    public function hasPostedReturn(PosReturn $return): bool
    {
        return InventoryMovement::query()
            ->where('reference_type', self::REFERENCE_TYPE_POS_RETURN)
            ->where('reference_id', $return->id)
            ->exists();
    }

    protected function assertCanIssue(
        int $companyId,
        int $branchId,
        int $inventoryItemId,
        int $warehouseId,
        float $quantity,
    ): void {
        if (InventoryStockService::allowsNegativeStock($companyId, $branchId)) {
            return;
        }

        InventoryStockService::assertSufficientStock(
            $inventoryItemId,
            $warehouseId,
            $quantity,
            $companyId,
            $branchId,
        );
    }

    protected function resolveWarehouseId(int $companyId, int $branchId): int
    {
        $warehouse = Warehouse::query()
            ->where('company_id', $companyId)
            ->where('branch_id', $branchId)
            ->where('is_active', true)
            ->orderByRaw("CASE WHEN code = 'MAIN' THEN 0 ELSE 1 END")
            ->orderBy('id')
            ->first();

        if ($warehouse === null) {
            throw ValidationException::withMessages([
                'warehouse' => __('No active warehouse is configured for POS stock deductions.'),
            ]);
        }

        return (int) $warehouse->id;
    }

    /**
     * @param  iterable<int, PosSaleItem>  $items
     * @return list<array{inventory_item_id: int, quantity: float}>
     */
    protected function stockLines(iterable $items): array
    {
        $lines = [];

        foreach ($items as $item) {
            if ($item->inventory_item_id === null) {
                continue;
            }

            $qty = (float) $item->quantity;

            if ($qty <= 0) {
                continue;
            }

            $lines[] = [
                'inventory_item_id' => (int) $item->inventory_item_id,
                'quantity' => $qty,
            ];
        }

        return $lines;
    }
}
