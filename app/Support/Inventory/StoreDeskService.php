<?php

namespace App\Support\Inventory;

use App\Enums\InventoryDocumentStatus;
use App\Enums\InventoryMovementType;
use App\Enums\PurchaseOrderStatus;
use App\Enums\ReorderAlertStatus;
use App\Enums\ReplenishmentRecommendation;
use App\Enums\StockIssueDestination;
use App\Models\Inventory\InventoryItem;
use App\Models\Inventory\InventoryMovement;
use App\Models\Inventory\InventoryReorderAlert;
use App\Models\Inventory\InventoryItemWarehouseReorderSetting;
use App\Models\Inventory\StockIssue;
use App\Models\Inventory\Warehouse;
use App\Models\Procurement\PurchaseOrder;
use App\Models\Procurement\PurchaseOrderItem;
use App\Models\Production\ProductionMaterialRequirement;
use App\Models\User;
use App\Support\InventoryStockService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class StoreDeskService
{
    /**
     * @return list<array<string, mixed>>
     */
    public function fastActions(?User $user): array
    {
        $from = ['from' => 'store-desk'];
        $actions = [];

        if ($user?->can('create', \App\Models\Inventory\StockReceipt::class)) {
            $actions[] = [
                'key' => 'receive',
                'label' => __('Receive goods'),
                'url' => route('admin.inventory.receipts.create', $from),
                'modal' => true,
                'primary' => true,
            ];
        }

        if ($user?->can('create', StockIssue::class)) {
            $actions[] = [
                'key' => 'issue',
                'label' => __('Issue materials'),
                'url' => route('admin.inventory.issues.create', $from),
                'modal' => true,
                'primary' => true,
            ];
        }

        if ($user?->can('create', \App\Models\Inventory\StockCount::class)) {
            $actions[] = [
                'key' => 'count',
                'label' => __('Stock count'),
                'url' => route('admin.inventory.stock-counts.create', $from),
                'modal' => true,
                'primary' => true,
            ];
        }

        if ($user?->can('inventory.transfer')) {
            $actions[] = [
                'key' => 'transfer',
                'label' => __('Transfer stock'),
                'url' => route('admin.inventory.transfers.create', $from),
                'modal' => true,
            ];
        }

        if ($user?->can('create', \App\Models\Inventory\StockAdjustment::class)) {
            $actions[] = [
                'key' => 'adjust',
                'label' => __('Adjust stock'),
                'url' => route('admin.inventory.adjustments.create', $from),
                'modal' => true,
            ];
        }

        $actions[] = [
            'key' => 'catalogue',
            'label' => __('Catalogue'),
            'url' => route('admin.store.desk.catalogue'),
            'modal' => true,
        ];

        if ($user?->can('viewAny', InventoryReorderAlert::class)) {
            $actions[] = [
                'key' => 'alerts',
                'label' => __('Reorder alerts'),
                'url' => route('admin.store.desk.reorder-alerts'),
                'modal' => true,
            ];
        }

        return $actions;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function searchItems(string $query, int $companyId, int $branchId, int $limit = 8): array
    {
        $query = trim($query);
        if ($query === '') {
            return [];
        }

        $like = '%'.strtolower($query).'%';

        return InventoryItem::query()
            ->forTenant()
            ->where('is_active', true)
            ->where(function ($inner) use ($like) {
                $inner->whereRaw('LOWER(sku) LIKE ?', [$like])
                    ->orWhereRaw('LOWER(item_name) LIKE ?', [$like]);
            })
            ->orderBy('item_name')
            ->limit($limit)
            ->get()
            ->map(fn (InventoryItem $item) => $this->presentItemLookup($item, $companyId, $branchId))
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    public function presentItemLookup(InventoryItem $item, int $companyId, int $branchId): array
    {
        $warehouse = $this->primaryWarehouse($companyId, $branchId);
        $warehouseId = $warehouse?->id;

        $available = $warehouseId
            ? InventoryStockService::balance($item->id, $warehouseId)
            : InventoryStockService::branchBalance($item->id, $companyId, $branchId);

        $reserved = (float) ProductionMaterialRequirement::query()
            ->where('company_id', $companyId)
            ->where('branch_id', $branchId)
            ->where('inventory_item_id', $item->id)
            ->when($warehouseId, fn ($q) => $q->where(function ($inner) use ($warehouseId) {
                $inner->where('warehouse_id', $warehouseId)->orWhereNull('warehouse_id');
            }))
            ->sum('reserved_quantity');

        $incoming = PurchaseOrderItem::query()
            ->where('inventory_item_id', $item->id)
            ->whereHas('purchaseOrder', function ($po) use ($companyId, $branchId) {
                $po->where('company_id', $companyId)
                    ->where('branch_id', $branchId)
                    ->whereIn('status', [
                        PurchaseOrderStatus::Approved,
                        PurchaseOrderStatus::Sent,
                        PurchaseOrderStatus::PartiallyReceived,
                    ]);
            })
            ->get(['quantity', 'quantity_received'])
            ->sum(fn (PurchaseOrderItem $line) => max(0, (float) $line->quantity - (float) $line->quantity_received));

        return [
            'id' => $item->id,
            'sku' => $item->sku,
            'name' => $item->item_name,
            'unit' => $item->unitOfMeasure?->abbreviation ?? $item->unitOfMeasure?->name,
            'available' => round($available, 2),
            'reserved' => round($reserved, 2),
            'free' => round(max(0, $available - $reserved), 2),
            'minimum' => (float) $item->reorder_level,
            'incoming' => round(max(0, $incoming), 2),
            'warehouse' => $warehouse?->name,
            'warehouse_code' => $warehouse?->code,
            'shelf' => null,
            'catalogue_url' => route('admin.store.desk.catalogue', ['search' => $item->sku]),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function catalogueRows(Collection $items, int $companyId, int $branchId): array
    {
        $warehouse = $this->primaryWarehouse($companyId, $branchId);
        $warehouseId = $warehouse?->id;

        $itemIds = $items->pluck('id');
        $balances = $warehouseId
            ? InventoryMovement::query()
                ->select('inventory_item_id', DB::raw('SUM(quantity) as balance'))
                ->where('warehouse_id', $warehouseId)
                ->whereIn('inventory_item_id', $itemIds)
                ->groupBy('inventory_item_id')
                ->pluck('balance', 'inventory_item_id')
            : InventoryStockService::branchBalancesMap($companyId, $branchId);

        $reservedMap = ProductionMaterialRequirement::query()
            ->where('company_id', $companyId)
            ->where('branch_id', $branchId)
            ->whereIn('inventory_item_id', $itemIds)
            ->when($warehouseId, fn ($q) => $q->where(function ($inner) use ($warehouseId) {
                $inner->where('warehouse_id', $warehouseId)->orWhereNull('warehouse_id');
            }))
            ->select('inventory_item_id', DB::raw('SUM(reserved_quantity) as reserved'))
            ->groupBy('inventory_item_id')
            ->pluck('reserved', 'inventory_item_id');

        return $items->map(function (InventoryItem $item) use ($balances, $reservedMap, $warehouse) {
            $available = (float) ($balances[$item->id] ?? 0);
            $reserved = (float) ($reservedMap[$item->id] ?? 0);

            return [
                'item' => $item,
                'available' => $available,
                'reserved' => $reserved,
                'warehouse' => $warehouse?->name,
                'shelf' => null,
            ];
        })->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function movementFeed(int $limit = 12): array
    {
        return InventoryMovement::query()
            ->forTenant()
            ->with(['item:id,item_name,sku', 'creator:id,name'])
            ->latest('created_at')
            ->limit($limit)
            ->get()
            ->map(function (InventoryMovement $movement) {
                $qty = (float) $movement->quantity;
                $inbound = $qty >= 0;

                return [
                    'time' => $movement->created_at?->format('H:i') ?? '—',
                    'date' => $movement->movement_date?->format('d M') ?? $movement->created_at?->format('d M'),
                    'type' => $this->movementLabel($movement->movement_type),
                    'item' => $movement->item?->item_name ?? '—',
                    'quantity' => ($inbound ? '+' : '').number_format(abs($qty), $qty == floor($qty) ? 0 : 2),
                    'inbound' => $inbound,
                    'by' => $movement->creator?->name,
                ];
            })
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function lowStockPriority(int $limit = 5): array
    {
        return InventoryReorderAlert::query()
            ->forTenant()
            ->where('status', '!=', ReorderAlertStatus::Resolved)
            ->with(['inventoryItem:id,item_name,sku,unit_of_measure_id', 'inventoryItem.unitOfMeasure:id,abbreviation,name', 'warehouse:id,name'])
            ->orderBy('current_quantity')
            ->limit($limit)
            ->get()
            ->map(function (InventoryReorderAlert $alert) {
                $qty = (float) $alert->current_quantity;
                $unit = $alert->inventoryItem?->unitOfMeasure?->abbreviation
                    ?? $alert->inventoryItem?->unitOfMeasure?->name;

                return [
                    'name' => $alert->inventoryItem?->item_name ?? __('Unknown item'),
                    'sku' => $alert->inventoryItem?->sku,
                    'remaining' => $qty,
                    'remaining_label' => number_format($qty, $qty == floor($qty) ? 0 : 2).' '.($unit ?? ''),
                    'urgent' => $qty <= 0,
                    'warehouse' => $alert->warehouse?->name,
                    'url' => route('admin.store.desk.reorder-alerts'),
                ];
            })
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function warehouseSnapshot(int $companyId, int $branchId): array
    {
        $warehouses = Warehouse::query()
            ->forTenant()
            ->physical()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        if ($warehouses->count() <= 1) {
            return [];
        }

        return $warehouses->map(function (Warehouse $warehouse) use ($companyId, $branchId) {
            $settings = InventoryItemWarehouseReorderSetting::query()
                ->where('company_id', $companyId)
                ->where('branch_id', $branchId)
                ->where('warehouse_id', $warehouse->id)
                ->where('is_active', true)
                ->where('max_level', '>', 0)
                ->get(['inventory_item_id', 'max_level']);

            if ($settings->isNotEmpty()) {
                $total = 0;
                foreach ($settings as $setting) {
                    $balance = InventoryStockService::balance((int) $setting->inventory_item_id, $warehouse->id);
                    $max = (float) $setting->max_level;
                    $total += $max > 0 ? min($balance / $max, 1) * 100 : 0;
                }
                $fillPercent = (int) round($total / max(1, $settings->count()));
            } else {
                $skusWithStock = (int) InventoryMovement::query()
                    ->where('warehouse_id', $warehouse->id)
                    ->select('inventory_item_id')
                    ->groupBy('inventory_item_id')
                    ->havingRaw('SUM(quantity) > 0')
                    ->get()
                    ->count();

                $totalSkus = max(1, InventoryItem::query()
                    ->forTenant()
                    ->where('is_active', true)
                    ->count());

                $fillPercent = (int) round(min(100, ($skusWithStock / $totalSkus) * 100));
            }

            return [
                'name' => $warehouse->name,
                'code' => $warehouse->code,
                'fill_percent' => $fillPercent,
                'url' => route('admin.inventory.warehouses.balances', $warehouse),
            ];
        })->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function receivingPipeline(?User $user, int $limit = 5): array
    {
        if (! $user?->can('viewAny', PurchaseOrder::class)) {
            return [];
        }

        return PurchaseOrder::query()
            ->forTenant()
            ->with('vendor:id,name')
            ->whereIn('status', [
                PurchaseOrderStatus::Approved,
                PurchaseOrderStatus::Sent,
                PurchaseOrderStatus::PartiallyReceived,
            ])
            ->where(function ($query) {
                $query->whereDate('expected_delivery_date', '<=', now()->addDay())
                    ->orWhereNull('expected_delivery_date');
            })
            ->orderByRaw('expected_delivery_date IS NULL')
            ->orderBy('expected_delivery_date')
            ->limit($limit)
            ->get(['id', 'po_number', 'vendor_id', 'expected_delivery_date', 'status'])
            ->map(function (PurchaseOrder $order) use ($user) {
                $expected = $order->expected_delivery_date;
                $timing = match (true) {
                    $expected === null => __('No date set'),
                    $expected->isToday() => __('Expected today'),
                    $expected->isTomorrow() => __('Tomorrow'),
                    $expected->isPast() => __('Overdue'),
                    default => $expected->format('d M Y'),
                };

                return [
                    'label' => $order->po_number,
                    'supplier' => $order->vendor?->name ?? '—',
                    'timing' => $timing,
                    'overdue' => $expected?->isPast() && ! $expected->isToday(),
                    'url' => $user?->can('procurement.orders.receive')
                        ? route('admin.procurement.orders.receive.create', [$order, 'from' => 'store-desk'])
                        : route('admin.procurement.orders.show', $order),
                    'modal' => false,
                ];
            })
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function issuePipeline(int $limit = 5): array
    {
        return StockIssue::query()
            ->forTenant()
            ->with(['warehouse:id,name', 'items.inventoryItem:id,item_name'])
            ->where('status', InventoryDocumentStatus::Draft)
            ->where('destination', '!=', StockIssueDestination::Transfer)
            ->latest('issue_date')
            ->limit($limit)
            ->get()
            ->map(function (StockIssue $issue) {
                $firstItem = $issue->items->first()?->inventoryItem?->item_name ?? __('Materials');
                $status = match ($issue->destination) {
                    StockIssueDestination::Production => __('Approved'),
                    default => __('Waiting'),
                };

                $jobRef = $this->extractJobReference($issue->notes);

                return [
                    'label' => $jobRef ?: $issue->issue_number,
                    'item' => $firstItem,
                    'status' => $status,
                    'warehouse' => $issue->warehouse?->name,
                    'url' => route('admin.inventory.issues.show', [$issue, 'from' => 'store-desk']),
                    'modal' => true,
                ];
            })
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function reorderRecommendations(int $limit = 5): array
    {
        return InventoryReorderAlert::query()
            ->forTenant()
            ->where('status', '!=', ReorderAlertStatus::Resolved)
            ->with(['inventoryItem:id,item_name,sku'])
            ->orderBy('current_quantity')
            ->limit($limit)
            ->get()
            ->map(function (InventoryReorderAlert $alert) {
                $qty = (float) $alert->current_quantity;
                $reorder = (float) $alert->reorder_level;

                $urgency = match (true) {
                    $qty <= 0 => __('Order today'),
                    $qty <= $reorder => __('Order tomorrow'),
                    default => __('Monitor'),
                };

                $action = $alert->replenishment_action === ReplenishmentRecommendation::Transfer
                    ? __('Transfer')
                    : __('Purchase');

                return [
                    'name' => $alert->inventoryItem?->item_name ?? __('Unknown item'),
                    'urgency' => $urgency,
                    'action' => $action,
                    'recommended_qty' => (float) $alert->recommended_quantity,
                    'url' => route('admin.store.desk.reorder-alerts'),
                ];
            })
            ->all();
    }

    protected function primaryWarehouse(int $companyId, int $branchId): ?Warehouse
    {
        return Warehouse::query()
            ->where('company_id', $companyId)
            ->where('branch_id', $branchId)
            ->physical()
            ->where('is_active', true)
            ->orderBy('name')
            ->first();
    }

    protected function movementLabel(?InventoryMovementType $type): string
    {
        return match ($type) {
            InventoryMovementType::Receipt => __('Received'),
            InventoryMovementType::Issue => __('Issued'),
            InventoryMovementType::Adjustment => __('Adjustment'),
            InventoryMovementType::TransferIn => __('Transfer in'),
            InventoryMovementType::TransferOut => __('Transfer out'),
            InventoryMovementType::ProductionConsumption => __('Production use'),
            InventoryMovementType::ProductionIssue => __('Production issue'),
            InventoryMovementType::ProductionReturn => __('Production return'),
            InventoryMovementType::ProductionOutput => __('Production output'),
            default => $type ? str_replace('_', ' ', ucfirst($type->value)) : __('Movement'),
        };
    }

    protected function extractJobReference(?string $notes): ?string
    {
        if ($notes === null || $notes === '') {
            return null;
        }

        if (preg_match('/\b(JP[-\s]?\d+|JOB[-\s]?\d+)\b/i', $notes, $matches)) {
            return strtoupper(str_replace(' ', '-', trim($matches[1])));
        }

        return null;
    }
}
