<?php

namespace App\Support;

use App\Enums\InventoryDocumentStatus;
use App\Enums\InventoryMovementType;
use App\Enums\StockIssueDestination;
use App\Models\Inventory\StockIssue;
use App\Support\Accounting\InventoryAccountingPostingService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StockIssueService
{
    public static function post(StockIssue $issue, int $userId): StockIssue
    {
        if ($issue->status === InventoryDocumentStatus::Posted) {
            throw ValidationException::withMessages([
                'issue' => __('Issue already posted.'),
            ]);
        }

        if ($issue->items()->count() < 1) {
            throw ValidationException::withMessages([
                'items' => __('Issue must have at least one line.'),
            ]);
        }

        if ($issue->destination === StockIssueDestination::Transfer && ! $issue->to_warehouse_id) {
            throw ValidationException::withMessages([
                'to_warehouse_id' => __('Transfer requires a destination warehouse.'),
            ]);
        }

        if (! $issue->warehouse?->is_active) {
            throw ValidationException::withMessages([
                'warehouse_id' => __('Deactivated warehouses cannot issue stock.'),
            ]);
        }

        if ($issue->destination === StockIssueDestination::Transfer && ! $issue->toWarehouse?->is_active) {
            throw ValidationException::withMessages([
                'to_warehouse_id' => __('Deactivated warehouses cannot receive transfer stock.'),
            ]);
        }

        return DB::transaction(function () use ($issue, $userId) {
            $issue->load('items.inventoryItem');

            foreach ($issue->items as $line) {
                InventoryStockService::assertSufficientStock(
                    $line->inventory_item_id,
                    $issue->warehouse_id,
                    (float) $line->quantity,
                );

                InventoryMovementService::record([
                    'company_id' => $issue->company_id,
                    'branch_id' => $issue->branch_id,
                    'inventory_item_id' => $line->inventory_item_id,
                    'warehouse_id' => $issue->warehouse_id,
                    'movement_type' => $issue->destination === StockIssueDestination::Transfer
                        ? InventoryMovementType::TransferOut
                        : InventoryMovementType::Issue,
                    'quantity' => InventoryMovementService::issueQuantity((float) $line->quantity),
                    'unit_cost' => $line->unit_cost,
                    'reference_type' => StockIssue::class,
                    'reference_id' => $issue->id,
                    'movement_date' => $issue->issue_date,
                    'created_by' => $userId,
                ]);

                if ($issue->destination === StockIssueDestination::Transfer && $issue->to_warehouse_id) {
                    InventoryMovementService::record([
                        'company_id' => $issue->company_id,
                        'branch_id' => $issue->branch_id,
                        'inventory_item_id' => $line->inventory_item_id,
                        'warehouse_id' => $issue->to_warehouse_id,
                        'movement_type' => InventoryMovementType::TransferIn,
                        'quantity' => InventoryMovementService::receiptQuantity((float) $line->quantity),
                        'unit_cost' => $line->unit_cost,
                        'reference_type' => StockIssue::class,
                        'reference_id' => $issue->id,
                        'movement_date' => $issue->issue_date,
                        'created_by' => $userId,
                    ]);
                }
            }

            $issue->update([
                'status' => InventoryDocumentStatus::Posted,
                'posted_at' => now(),
            ]);

            app(InventoryAccountingPostingService::class)->postStockIssue($issue, $userId);

            return $issue->fresh(['items', 'warehouse']);
        });
    }
}
