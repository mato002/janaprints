<?php

namespace App\Services\Dispatch;

use App\Enums\Dispatch\DeliveryNoteStatus;
use App\Enums\InventoryMovementType;
use App\Enums\VirtualWarehouseRole;
use App\Models\Dispatch\DeliveryNote;
use App\Models\Dispatch\DeliveryNoteItem;
use App\Models\Inventory\InventoryMovement;
use App\Services\Inventory\VirtualWarehouseResolverService;
use App\Support\InventoryStockService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DispatchInventoryReportService
{
    public function __construct(
        protected VirtualWarehouseResolverService $virtualWarehouses,
    ) {}

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function transitInventory(int $companyId, ?int $branchId = null): Collection
    {
        $transit = $this->virtualWarehouses->inTransit($companyId);

        if ($transit === null) {
            return collect();
        }

        $rows = InventoryMovement::query()
            ->select([
                'inventory_movements.inventory_item_id',
                DB::raw('SUM(inventory_movements.quantity) as balance'),
            ])
            ->where('company_id', $companyId)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->where('warehouse_id', $transit->id)
            ->groupBy('inventory_movements.inventory_item_id')
            ->havingRaw('SUM(inventory_movements.quantity) > 0')
            ->with('item:id,sku,item_name')
            ->get();

        return $rows->map(function ($row) use ($companyId, $branchId, $transit) {
            $itemId = (int) $row->inventory_item_id;
            $balance = (float) $row->balance;

            $line = DeliveryNoteItem::query()
                ->where('inventory_item_id', $itemId)
                ->whereHas('deliveryNote', fn ($q) => $q
                    ->where('company_id', $companyId)
                    ->when($branchId, fn ($inner) => $inner->where('branch_id', $branchId))
                    ->where('status', DeliveryNoteStatus::Dispatched))
                ->with(['deliveryNote.customer:id,company_name'])
                ->latest('id')
                ->first();

            $dispatchedAt = $line?->deliveryNote?->dispatched_at;
            $daysInTransit = $dispatchedAt ? $dispatchedAt->diffInDays(now()) : 0;

            return [
                'item' => $row->item,
                'quantity' => $balance,
                'warehouse' => $transit,
                'delivery_note' => $line?->deliveryNote,
                'customer' => $line?->deliveryNote?->customer,
                'dispatched_at' => $dispatchedAt,
                'days_in_transit' => $daysInTransit,
                'aging_bucket' => $this->agingBucket($daysInTransit),
            ];
        })->sortByDesc('days_in_transit')->values();
    }

    /**
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function cogsPostings(int $companyId, ?int $branchId = null, int $perPage = 25)
    {
        return DeliveryNote::query()
            ->where('company_id', $companyId)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->where('status', DeliveryNoteStatus::Delivered)
            ->whereNotNull('posted_journal_id')
            ->with(['customer:id,company_name', 'postedJournal:id,reference,journal_number', 'items.inventoryItem:id,sku,item_name'])
            ->latest('delivered_at')
            ->paginate($perPage);
    }

    /**
     * @return array{finished_goods: float, in_transit: float, delivered_value: float}
     */
    public function ownershipSummary(int $companyId, ?int $branchId = null): array
    {
        $fg = $this->virtualWarehouses->finishedGoods($companyId);
        $transit = $this->virtualWarehouses->inTransit($companyId);

        $fgValue = $fg ? $this->warehouseValue($companyId, $branchId, $fg->id) : 0.0;
        $transitValue = $transit ? $this->warehouseValue($companyId, $branchId, $transit->id) : 0.0;

        $deliveredValue = (float) DeliveryNote::query()
            ->where('company_id', $companyId)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->where('status', DeliveryNoteStatus::Delivered)
            ->whereNotNull('posted_journal_id')
            ->join('delivery_note_items', 'delivery_note_items.delivery_note_id', '=', 'delivery_notes.id')
            ->sum(DB::raw('COALESCE(delivery_note_items.total_cost, delivery_note_items.quantity * delivery_note_items.unit_cost, 0)'));

        return [
            'finished_goods' => $fgValue,
            'in_transit' => $transitValue,
            'delivered_value' => $deliveredValue,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function inventoryImpact(DeliveryNote $note): array
    {
        $note->loadMissing(['items.inventoryItem', 'items.productionOutput', 'postedJournal']);

        $fg = $this->virtualWarehouses->finishedGoods($note->company_id);
        $transit = $this->virtualWarehouses->inTransit($note->company_id);

        $lines = $note->items->map(function ($line) use ($fg, $transit) {
            $qty = (float) $line->quantity;
            $fgBalance = $fg && $line->inventory_item_id
                ? InventoryStockService::balance((int) $line->inventory_item_id, $fg->id)
                : null;
            $transitBalance = $transit && $line->inventory_item_id
                ? InventoryStockService::balance((int) $line->inventory_item_id, $transit->id)
                : null;

            $dispatched = InventoryMovement::query()
                ->where('reference_type', DeliveryNoteItem::class)
                ->where('reference_id', $line->id)
                ->where('movement_type', InventoryMovementType::DispatchToTransit)
                ->exists();

            $delivered = InventoryMovement::query()
                ->where('reference_type', DeliveryNoteItem::class)
                ->where('reference_id', $line->id)
                ->where('movement_type', InventoryMovementType::DeliveryCogs)
                ->exists();

            return [
                'line' => $line,
                'fg_balance' => $fgBalance,
                'transit_balance' => $transitBalance,
                'dispatched' => $dispatched,
                'delivered' => $delivered,
            ];
        });

        return [
            'finished_goods_warehouse' => $fg,
            'transit_warehouse' => $transit,
            'lines' => $lines,
            'posted_journal' => $note->postedJournal,
            'total_cost' => round($note->items->sum(fn ($l) => (float) ($l->total_cost ?? 0)), 2),
        ];
    }

    protected function warehouseValue(int $companyId, ?int $branchId, int $warehouseId): float
    {
        return (float) InventoryMovement::query()
            ->where('company_id', $companyId)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->where('warehouse_id', $warehouseId)
            ->selectRaw('SUM(quantity * unit_cost) as value')
            ->value('value');
    }

    protected function agingBucket(int $days): string
    {
        return match (true) {
            $days >= 30 => '30+',
            $days >= 14 => '14-29',
            $days >= 7 => '7-13',
            default => '0-6',
        };
    }
}
