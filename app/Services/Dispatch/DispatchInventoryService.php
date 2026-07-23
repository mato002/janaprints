<?php

namespace App\Services\Dispatch;

use App\Enums\InventoryMovementType;
use App\Enums\ProductionOutputStatus;
use App\Enums\VirtualWarehouseRole;
use App\Models\Dispatch\DeliveryNote;
use App\Models\Dispatch\DeliveryNoteItem;
use App\Models\Inventory\InventoryMovement;
use App\Models\Production\ProductionOutput;
use App\Services\Inventory\VirtualWarehouseResolverService;
use App\Support\Accounting\InventoryAccountingPostingService;
use App\Support\Inventory\VirtualWarehouseGuard;
use App\Support\InventoryMovementService;
use App\Support\InventoryStockService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DispatchInventoryService
{
    public function __construct(
        protected VirtualWarehouseResolverService $virtualWarehouses,
        protected InventoryAccountingPostingService $accounting,
    ) {}

    /**
     * @return array{eligible: bool, blockers: list<string>}
     */
    public function dispatchReadiness(DeliveryNote $note): array
    {
        $note->loadMissing(['items', 'productionJobCard']);

        $blockers = [];

        if ($note->items->isEmpty()) {
            $blockers[] = __('Delivery note must have at least one line item.');
        }

        if ($note->production_job_card_id) {
            $postedOutputs = ProductionOutput::query()
                ->where('production_job_card_id', $note->production_job_card_id)
                ->where('completion_status', ProductionOutputStatus::Posted)
                ->count();

            if ($postedOutputs === 0) {
                $blockers[] = __('Finished goods have not been posted on the linked job card.');
            }
        }

        foreach ($note->items as $line) {
            if (! $line->inventory_item_id) {
                $blockers[] = __('Line “:item” is not linked to finished goods inventory.', [
                    'item' => $line->description,
                ]);
            } elseif ((float) $line->quantity <= 0) {
                $blockers[] = __('Line “:item” must have a quantity greater than zero.', [
                    'item' => $line->description,
                ]);
            } elseif ((float) ($line->unit_cost ?? 0) <= 0) {
                $blockers[] = __('Line “:item” has no unit cost — post finished goods on the job card to populate cost.', [
                    'item' => $line->description,
                ]);
            }
        }

        return [
            'eligible' => $blockers === [],
            'blockers' => array_values(array_unique($blockers)),
        ];
    }

    public function dispatch(DeliveryNote $note, int $userId): DeliveryNote
    {
        return DB::transaction(function () use ($note, $userId) {
            $note = DeliveryNote::query()->lockForUpdate()->findOrFail($note->id);
            $note->load(['items.inventoryItem', 'items.productionOutput']);

            $this->hydrateInventoryLines($note);

            if ($note->items->isEmpty()) {
                throw ValidationException::withMessages([
                    'items' => __('Delivery note must have at least one line item.'),
                ]);
            }

            $fgWarehouse = $this->resolveVirtualWarehouse($note->company_id, VirtualWarehouseRole::FinishedGoods);
            $transitWarehouse = $this->resolveVirtualWarehouse($note->company_id, VirtualWarehouseRole::InTransit);

            VirtualWarehouseGuard::usingSystemContext(function () use ($note, $userId, $fgWarehouse, $transitWarehouse) {
                foreach ($note->items as $line) {
                    $this->postDispatchMovementsForLine($note, $line, $fgWarehouse, $transitWarehouse, $userId);
                }
            });

            return $note->fresh(['items.inventoryItem', 'items.productionOutput']);
        });
    }

    public function confirmDelivery(DeliveryNote $note, int $userId): DeliveryNote
    {
        return DB::transaction(function () use ($note, $userId) {
            $note = DeliveryNote::query()->lockForUpdate()->findOrFail($note->id);
            $note->load(['items.inventoryItem']);

            if (! $this->hasDispatchMovements($note)) {
                throw ValidationException::withMessages([
                    'status' => __('Delivery note must be dispatched to in-transit inventory before delivery confirmation.'),
                ]);
            }

            if ($this->hasDeliveryMovements($note)) {
                throw ValidationException::withMessages([
                    'status' => __('Delivery inventory has already been confirmed for this note.'),
                ]);
            }

            $transitWarehouse = $this->resolveVirtualWarehouse($note->company_id, VirtualWarehouseRole::InTransit);

            VirtualWarehouseGuard::usingSystemContext(function () use ($note, $userId, $transitWarehouse) {
                foreach ($note->items as $line) {
                    $this->postDeliveryMovementForLine($note, $line, $transitWarehouse, $userId);
                }
            });

            $this->accounting->postDeliveryCogs($note->fresh(['items']), $userId);

            return $note->fresh(['items.inventoryItem', 'postedJournal']);
        });
    }

    protected function hydrateInventoryLines(DeliveryNote $note): void
    {
        foreach ($note->items as $line) {
            if ($line->inventory_item_id && $line->unit_cost) {
                continue;
            }

            if ($line->production_output_id) {
                $output = $line->productionOutput ?? ProductionOutput::query()->find($line->production_output_id);
                if ($output) {
                    $line->update([
                        'inventory_item_id' => $line->inventory_item_id ?? $output->finished_inventory_item_id,
                        'unit_cost' => $line->unit_cost ?? $output->unit_cost,
                        'total_cost' => $line->total_cost ?? $output->total_cost,
                    ]);
                }
            }
        }

        if ($note->items->every(fn (DeliveryNoteItem $line) => $line->inventory_item_id)) {
            return;
        }

        if (! $note->production_job_card_id) {
            $this->assertAllLinesHaveInventory($note->fresh('items'));

            return;
        }

        $outputs = ProductionOutput::query()
            ->where('production_job_card_id', $note->production_job_card_id)
            ->where('completion_status', ProductionOutputStatus::Posted)
            ->get();

        foreach ($note->items as $line) {
            if ($line->inventory_item_id) {
                continue;
            }

            $output = $outputs->first();
            if ($output === null) {
                continue;
            }

            $line->update([
                'inventory_item_id' => $output->finished_inventory_item_id,
                'production_output_id' => $output->id,
                'quantity' => $line->quantity ?: $output->quantity_completed,
                'unit_cost' => $output->unit_cost,
                'total_cost' => round((float) ($line->quantity ?: $output->quantity_completed) * (float) $output->unit_cost, 2),
            ]);

            $outputs->shift();
        }

        $this->assertAllLinesHaveInventory($note->fresh('items'));
    }

    protected function assertAllLinesHaveInventory(DeliveryNote $note): void
    {
        foreach ($note->items as $line) {
            if (! $line->inventory_item_id || (float) $line->quantity <= 0) {
                throw ValidationException::withMessages([
                    'items' => __('Finished goods have not been posted — delivery lines have no inventory to ship.'),
                ]);
            }

            if ((float) ($line->unit_cost ?? 0) <= 0) {
                throw ValidationException::withMessages([
                    'items' => __('Each delivery line must have unit cost from production output before dispatch.'),
                ]);
            }
        }
    }

    protected function postDispatchMovementsForLine(
        DeliveryNote $note,
        DeliveryNoteItem $line,
        $fgWarehouse,
        $transitWarehouse,
        int $userId,
    ): void {
        if ($this->lineHasDispatchMovements($line)) {
            return;
        }

        $quantity = (float) $line->quantity;
        $unitCost = (float) $line->unit_cost;

        InventoryStockService::assertSufficientStock(
            (int) $line->inventory_item_id,
            $fgWarehouse->id,
            $quantity,
        );

        InventoryMovementService::record([
            'company_id' => $note->company_id,
            'branch_id' => $note->branch_id,
            'inventory_item_id' => $line->inventory_item_id,
            'warehouse_id' => $fgWarehouse->id,
            'movement_type' => InventoryMovementType::DispatchToTransit,
            'quantity' => InventoryMovementService::issueQuantity($quantity),
            'unit_cost' => $unitCost,
            'reference_type' => DeliveryNoteItem::class,
            'reference_id' => $line->id,
            'movement_date' => ($note->dispatched_at ?? now())->toDateString(),
            'created_by' => $userId,
        ]);

        InventoryMovementService::record([
            'company_id' => $note->company_id,
            'branch_id' => $note->branch_id,
            'inventory_item_id' => $line->inventory_item_id,
            'warehouse_id' => $transitWarehouse->id,
            'movement_type' => InventoryMovementType::DispatchToTransit,
            'quantity' => InventoryMovementService::receiptQuantity($quantity),
            'unit_cost' => $unitCost,
            'reference_type' => DeliveryNoteItem::class,
            'reference_id' => $line->id,
            'movement_date' => ($note->dispatched_at ?? now())->toDateString(),
            'created_by' => $userId,
        ]);

        $line->update([
            'total_cost' => round($quantity * $unitCost, 2),
        ]);
    }

    protected function postDeliveryMovementForLine(
        DeliveryNote $note,
        DeliveryNoteItem $line,
        $transitWarehouse,
        int $userId,
    ): void {
        if ($this->lineHasDeliveryMovement($line)) {
            return;
        }

        $quantity = (float) $line->quantity;
        $unitCost = (float) $line->unit_cost;

        InventoryStockService::assertSufficientStock(
            (int) $line->inventory_item_id,
            $transitWarehouse->id,
            $quantity,
        );

        InventoryMovementService::record([
            'company_id' => $note->company_id,
            'branch_id' => $note->branch_id,
            'inventory_item_id' => $line->inventory_item_id,
            'warehouse_id' => $transitWarehouse->id,
            'movement_type' => InventoryMovementType::DeliveryCogs,
            'quantity' => InventoryMovementService::issueQuantity($quantity),
            'unit_cost' => $unitCost,
            'reference_type' => DeliveryNoteItem::class,
            'reference_id' => $line->id,
            'movement_date' => now()->toDateString(),
            'created_by' => $userId,
        ]);
    }

    protected function resolveVirtualWarehouse(int $companyId, VirtualWarehouseRole $role)
    {
        $this->virtualWarehouses->ensureDefaults($companyId);
        $warehouse = $this->virtualWarehouses->resolveByRole($companyId, $role);

        if ($warehouse === null || ! $warehouse->is_virtual) {
            throw ValidationException::withMessages([
                'warehouse_id' => __('Virtual warehouse :role is not configured.', ['role' => $role->label()]),
            ]);
        }

        return $warehouse;
    }

    public function hasDispatchMovements(DeliveryNote $note): bool
    {
        return InventoryMovement::query()
            ->where('reference_type', DeliveryNoteItem::class)
            ->whereIn('reference_id', $note->items()->pluck('id'))
            ->where('movement_type', InventoryMovementType::DispatchToTransit)
            ->where('quantity', '>', 0)
            ->exists();
    }

    protected function hasDeliveryMovements(DeliveryNote $note): bool
    {
        return InventoryMovement::query()
            ->where('reference_type', DeliveryNoteItem::class)
            ->whereIn('reference_id', $note->items()->pluck('id'))
            ->where('movement_type', InventoryMovementType::DeliveryCogs)
            ->exists();
    }

    protected function lineHasDispatchMovements(DeliveryNoteItem $line): bool
    {
        return InventoryMovement::query()
            ->where('reference_type', DeliveryNoteItem::class)
            ->where('reference_id', $line->id)
            ->where('movement_type', InventoryMovementType::DispatchToTransit)
            ->where('quantity', '>', 0)
            ->exists();
    }

    protected function lineHasDeliveryMovement(DeliveryNoteItem $line): bool
    {
        return InventoryMovement::query()
            ->where('reference_type', DeliveryNoteItem::class)
            ->where('reference_id', $line->id)
            ->where('movement_type', InventoryMovementType::DeliveryCogs)
            ->exists();
    }
}
