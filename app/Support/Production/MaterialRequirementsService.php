<?php

namespace App\Support\Production;

use App\Enums\MaterialRequirementStatus;
use App\Models\Inventory\InventoryItem;
use App\Models\Inventory\ProductionMaterialConsumption;
use App\Models\Inventory\Warehouse;
use App\Models\Production\ProductionJobCard;
use App\Models\Production\ProductionMaterialIssue;
use App\Models\Production\ProductionMaterialRequirement;
use App\Models\Production\ProductBom;
use App\Models\Sales\SalesOrder;
use App\Support\Inventory\InventoryCostingService;
use App\Support\InventoryStockService;
use App\Support\ProductionMaterialConsumptionService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MaterialRequirementsService
{
    public function __construct(
        protected ProductBomService $bomService,
        protected MaterialQuantityFormulaService $formulas,
    ) {}

    /**
     * Snapshot material requirements when a job card is created (idempotent).
     */
    public function snapshotForJobCard(ProductionJobCard $jobCard, int $userId): Collection
    {
        $warehouse = $this->defaultPhysicalWarehouse($jobCard->company_id, $jobCard->branch_id);

        if ($warehouse === null) {
            return collect();
        }

        if (ProductionMaterialRequirement::query()->where('production_job_card_id', $jobCard->id)->exists()) {
            return ProductionMaterialRequirement::query()
                ->where('production_job_card_id', $jobCard->id)
                ->get();
        }

        try {
            return $this->generate($jobCard, $warehouse->id, $userId, false, true);
        } catch (ValidationException) {
            return collect();
        }
    }

    /**
     * @return Collection<int, ProductionMaterialRequirement>
     */
    public function generate(
        ProductionJobCard $jobCard,
        int $warehouseId,
        int $userId,
        bool $replaceExisting = true,
        bool $bindToStockLocation = false,
    ): Collection {
        $jobCard->loadMissing(['salesOrder.items.inventoryItem']);

        $warehouse = Warehouse::query()
            ->where('company_id', $jobCard->company_id)
            ->where('branch_id', $jobCard->branch_id)
            ->where('is_active', true)
            ->where('is_virtual', false)
            ->find($warehouseId);

        if ($warehouse === null) {
            throw ValidationException::withMessages([
                'warehouse_id' => __('Choose a physical warehouse for material requirements. Finished goods / virtual locations cannot hold raw materials.'),
            ]);
        }

        $sources = $this->resolveSources($jobCard);

        if ($sources->isEmpty()) {
            throw ValidationException::withMessages([
                'sales_order' => __('No finished products linked on the sales order. Assign inventory items to order lines before generating requirements.'),
            ]);
        }

        return DB::transaction(function () use ($jobCard, $warehouse, $userId, $replaceExisting, $sources, $bindToStockLocation) {
            if ($replaceExisting) {
                ProductionMaterialRequirement::query()
                    ->where('production_job_card_id', $jobCard->id)
                    ->where('consumed_quantity', 0)
                    ->delete();
            }

            $created = collect();

            foreach ($sources as $source) {
                $bom = $this->bomService->findActiveForFinishedItem(
                    $jobCard->company_id,
                    $jobCard->branch_id,
                    $source['finished_item_id'],
                );

                if ($bom === null) {
                    continue;
                }

                foreach ($this->bomService->requirementsForQuantity($bom, $source['quantity']) as $calc) {
                    /** @var \App\Models\Production\ProductBomLine $line */
                    $line = $calc['line'];
                    $requiredQty = (float) $calc['required_quantity'];
                    $formula = $line->quantity_formula;
                    $lineWarehouse = $warehouse;
                    if ($bindToStockLocation) {
                        $atSelected = InventoryStockService::balance((int) $line->inventory_item_id, $warehouse->id);
                        if ($atSelected <= 0) {
                            $stocked = $this->physicalWarehouseWithMostStock(
                                $jobCard->company_id,
                                $jobCard->branch_id,
                                (int) $line->inventory_item_id,
                            );
                            if ($stocked && InventoryStockService::balance((int) $line->inventory_item_id, $stocked->id) > 0) {
                                $lineWarehouse = $stocked;
                            }
                        }
                    }
                    $unitCost = InventoryCostingService::resolveIssueUnitCost(
                        $jobCard->company_id,
                        $jobCard->branch_id,
                        $line->inventory_item_id,
                        $lineWarehouse->id,
                        $requiredQty,
                    ) ?: (float) ($line->inventoryItem?->standard_cost ?? 0);

                    $existing = ProductionMaterialRequirement::query()
                        ->where('production_job_card_id', $jobCard->id)
                        ->where('inventory_item_id', $line->inventory_item_id)
                        ->where('warehouse_id', $lineWarehouse->id)
                        ->where('finished_item_id', $source['finished_item_id'])
                        ->first();

                    if ($existing !== null) {
                        $existing->update([
                            'required_quantity' => round((float) $existing->required_quantity + $requiredQty, 3),
                            'estimated_cost' => round(((float) $existing->required_quantity + $requiredQty) * $unitCost, 2),
                            'unit_cost' => $unitCost,
                            'status' => $this->resolveStatus($existing->fresh()),
                        ]);
                        $created->push($existing->fresh());
                        continue;
                    }

                    $requirement = ProductionMaterialRequirement::query()->create([
                        'company_id' => $jobCard->company_id,
                        'branch_id' => $jobCard->branch_id,
                        'production_job_card_id' => $jobCard->id,
                        'product_bom_id' => $bom->id,
                        'finished_item_id' => $source['finished_item_id'],
                        'sales_order_item_id' => $source['sales_order_item_id'],
                        'inventory_item_id' => $line->inventory_item_id,
                        'warehouse_id' => $lineWarehouse->id,
                        'job_quantity' => $source['quantity'],
                        'quantity_formula' => $formula,
                        'required_quantity' => $requiredQty,
                        'reserved_quantity' => 0,
                        'consumed_quantity' => 0,
                        'issued_quantity' => 0,
                        'waste_quantity' => 0,
                        'returned_quantity' => 0,
                        'unit_cost' => $unitCost,
                        'estimated_cost' => round($requiredQty * $unitCost, 2),
                        'status' => MaterialRequirementStatus::Planned,
                        'generated_by' => $userId,
                        'generated_at' => now(),
                    ]);

                    $requirement->update(['status' => $this->resolveStatus($requirement)]);
                    $created->push($requirement);
                }
            }

            if ($created->isEmpty()) {
                throw ValidationException::withMessages([
                    'bom' => __('No active BOM found for the linked finished products.'),
                ]);
            }

            return $created;
        });
    }

    public function reserve(ProductionMaterialRequirement $requirement, int $userId): ProductionMaterialRequirement
    {
        if ($requirement->remainingQuantity() <= 0) {
            throw ValidationException::withMessages([
                'requirement' => __('Requirement is already fulfilled.'),
            ]);
        }

        $requirement = $this->retargetToStockWarehouse($requirement);
        $toReserve = $requirement->remainingQuantity();
        $available = $this->availableStock($requirement);

        if ($toReserve > $available) {
            throw ValidationException::withMessages([
                'quantity' => __('Insufficient available stock to reserve. Available: :qty', ['qty' => $available]),
            ]);
        }

        $newReserved = round((float) $requirement->reserved_quantity + $toReserve, 3);

        $requirement->update([
            'reserved_quantity' => $newReserved,
            'status' => $newReserved >= round($requirement->remainingQuantity(), 3)
                ? MaterialRequirementStatus::Reserved
                : $this->resolveStatus($requirement->fresh()),
        ]);

        return $requirement->fresh(['inventoryItem', 'warehouse', 'finishedItem']);
    }

    /**
     * @return Collection<int, ProductionMaterialRequirement>
     */
    public function reserveAll(ProductionJobCard $jobCard, int $userId): Collection
    {
        $requirements = ProductionMaterialRequirement::query()
            ->where('production_job_card_id', $jobCard->id)
            ->whereIn('status', [
                MaterialRequirementStatus::Planned,
                MaterialRequirementStatus::Shortfall,
                MaterialRequirementStatus::Partial,
                MaterialRequirementStatus::Reserved,
            ])
            ->get();

        $reserved = collect();

        foreach ($requirements as $requirement) {
            if ($requirement->unreservedRemaining() <= 0) {
                continue;
            }

            try {
                $reserved->push($this->reserve($requirement, $userId));
            } catch (ValidationException) {
                $requirement->update(['status' => MaterialRequirementStatus::Shortfall]);
            }
        }

        return $reserved;
    }

    public function consumeFromRequirement(
        ProductionMaterialRequirement $requirement,
        int $userId,
        ?float $quantity = null,
    ): ProductionMaterialConsumption {
        return DB::transaction(function () use ($requirement, $userId, $quantity) {
            $requirement = ProductionMaterialRequirement::query()
                ->whereKey($requirement->id)
                ->lockForUpdate()
                ->firstOrFail();

            $this->linkOrphanConsumptions($requirement);
            $requirement = $this->retargetToStockWarehouse($requirement);

            $remaining = $requirement->remainingQuantity();
            $available = $this->availableStock($requirement);
            $qty = $quantity ?? min($remaining, $available);

            if ($qty <= 0) {
                throw ValidationException::withMessages([
                    'quantity' => $remaining <= 0
                        ? __('Nothing remaining to consume for this requirement.')
                        : __('No available stock to consume for this material. Receive the shortfall into a physical warehouse first.'),
                ]);
            }

            if ($qty > $remaining) {
                throw ValidationException::withMessages([
                    'quantity' => __('Quantity exceeds remaining requirement. Only :remaining :unit remain.', [
                        'remaining' => $remaining,
                        'unit' => $requirement->inventoryItem?->unitOfMeasure?->code ?? '',
                    ]),
                ]);
            }

            $item = $requirement->inventoryItem ?? InventoryItem::query()->findOrFail($requirement->inventory_item_id);
            $jobCard = $requirement->jobCard ?? ProductionJobCard::query()->findOrFail($requirement->production_job_card_id);

            return ProductionMaterialConsumptionService::consume(
                $jobCard,
                $item,
                (int) $requirement->warehouse_id,
                $qty,
                $userId,
                (float) $requirement->unit_cost > 0 ? (float) $requirement->unit_cost : null,
                $requirement->id,
            );
        });
    }

    /**
     * Consume remaining qty on every open line, capped by available warehouse stock.
     *
     * @return array{consumed: int, skipped: int}
     */
    public function consumeAll(ProductionJobCard $jobCard, int $userId): array
    {
        $requirements = ProductionMaterialRequirement::query()
            ->where('production_job_card_id', $jobCard->id)
            ->whereIn('status', [
                MaterialRequirementStatus::Planned,
                MaterialRequirementStatus::Reserved,
                MaterialRequirementStatus::Partial,
                MaterialRequirementStatus::Shortfall,
            ])
            ->orderBy('inventory_item_id')
            ->get();

        $consumed = 0;
        $skipped = 0;

        foreach ($requirements as $requirement) {
            if ($requirement->remainingQuantity() <= 0) {
                continue;
            }

            try {
                $this->consumeFromRequirement($requirement, $userId);
                $consumed++;
            } catch (ValidationException) {
                $skipped++;
            }
        }

        return [
            'consumed' => $consumed,
            'skipped' => $skipped,
        ];
    }

    public function findOpenRequirement(
        ProductionJobCard $jobCard,
        int $inventoryItemId,
        int $warehouseId,
    ): ?ProductionMaterialRequirement {
        return ProductionMaterialRequirement::query()
            ->where('production_job_card_id', $jobCard->id)
            ->where('inventory_item_id', $inventoryItemId)
            ->where('warehouse_id', $warehouseId)
            ->orderBy('id')
            ->get()
            ->first(fn (ProductionMaterialRequirement $requirement) => $requirement->remainingQuantity() > 0);
    }

    public function linkOrphanConsumptions(ProductionMaterialRequirement $requirement): void
    {
        ProductionMaterialConsumption::query()
            ->where('production_job_card_id', $requirement->production_job_card_id)
            ->where('inventory_item_id', $requirement->inventory_item_id)
            ->where('warehouse_id', $requirement->warehouse_id)
            ->whereNull('production_material_requirement_id')
            ->update(['production_material_requirement_id' => $requirement->id]);
    }

    public function recordedConsumedQuantity(ProductionMaterialRequirement $requirement): float
    {
        $this->linkOrphanConsumptions($requirement);

        return (float) ProductionMaterialConsumption::query()
            ->where('production_material_requirement_id', $requirement->id)
            ->sum('quantity');
    }

    public function syncRequirementFromConsumption(ProductionMaterialRequirement $requirement): ProductionMaterialRequirement
    {
        $this->linkOrphanConsumptions($requirement);

        $consumed = $this->recordedConsumedQuantity($requirement);

        $issued = (float) ProductionMaterialIssue::query()
            ->where('production_material_requirement_id', $requirement->id)
            ->sum('quantity');

        $wasted = (float) \App\Models\Production\ProductionWastageRecord::query()
            ->where('production_job_card_id', $requirement->production_job_card_id)
            ->where('inventory_item_id', $requirement->inventory_item_id)
            ->where('flow_type', \App\Enums\ProductionMaterialFlowType::Wasted)
            ->sum('quantity');

        $returned = (float) \App\Models\Production\ProductionWastageRecord::query()
            ->where('production_job_card_id', $requirement->production_job_card_id)
            ->where('inventory_item_id', $requirement->inventory_item_id)
            ->where('flow_type', \App\Enums\ProductionMaterialFlowType::Returned)
            ->sum('quantity');

        $reserved = min((float) $requirement->reserved_quantity, max(0, (float) $requirement->required_quantity - $consumed));

        $requirement->update([
            'consumed_quantity' => $consumed,
            'issued_quantity' => $issued,
            'waste_quantity' => $wasted,
            'returned_quantity' => $returned,
            'reserved_quantity' => $reserved,
        ]);

        $requirement = $requirement->fresh(['inventoryItem', 'warehouse']);
        $requirement->update(['status' => $this->resolveStatus($requirement)]);

        return $requirement->fresh(['inventoryItem', 'warehouse', 'finishedItem']);
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function panelRows(ProductionJobCard $jobCard): Collection
    {
        $requirements = ProductionMaterialRequirement::query()
            ->where('production_job_card_id', $jobCard->id)
            ->with(['inventoryItem.unitOfMeasure', 'finishedItem', 'warehouse'])
            ->orderBy('inventory_item_id')
            ->get();

        return $requirements->map(function (ProductionMaterialRequirement $row) {
            $stockWarehouseId = $this->stockWarehouseId($row);
            $available = $this->availableAtWarehouse($row, $stockWarehouseId);
            $remaining = $row->remainingQuantity();
            $shortfall = max(0, round($remaining - $available - (float) $row->reserved_quantity, 3));
            $stockWarehouse = $stockWarehouseId === (int) $row->warehouse_id
                ? $row->warehouse
                : $this->physicalWarehouses((int) $row->company_id, (int) $row->branch_id)
                    ->firstWhere('id', $stockWarehouseId);

            return [
                'requirement' => $row,
                'item_name' => $row->inventoryItem?->item_name,
                'sku' => $row->inventoryItem?->sku,
                'finished_product' => $row->finishedItem?->item_name,
                'unit' => $row->inventoryItem?->unitOfMeasure?->code,
                'required' => (float) $row->required_quantity,
                'issued' => (float) $row->issued_quantity,
                'consumed' => (float) $row->consumed_quantity,
                'waste' => (float) $row->waste_quantity,
                'returned' => (float) $row->returned_quantity,
                'remaining' => $remaining,
                'available' => $available,
                'reserved' => (float) $row->reserved_quantity,
                'shortfall' => $shortfall,
                'unit_cost' => (float) $row->unit_cost,
                'estimated_cost' => (float) $row->estimated_cost,
                'warehouse_name' => $row->warehouse?->name,
                'stock_warehouse_name' => $stockWarehouse?->name,
                'status' => $this->resolveStatus($row),
                'can_reserve' => $row->status->isOpen() && $row->unreservedRemaining() > 0 && $available >= $row->unreservedRemaining(),
                'can_consume' => $remaining > 0 && $available > 0,
            ];
        });
    }

    /**
     * Prefill a goods receipt from live job shortfalls. Quantity and cost stay editable.
     *
     * @return array{
     *     lines: list<array{inventory_item_id: string, quantity: string, unit_cost: string}>,
     *     warehouse_id: ?int
     * }
     */
    public function stockReceiptPrefill(ProductionJobCard $jobCard): array
    {
        $grouped = [];

        foreach ($this->panelRows($jobCard) as $row) {
            $shortfall = (float) ($row['shortfall'] ?? 0);
            if ($shortfall <= 0) {
                continue;
            }

            $requirement = $row['requirement'] ?? null;
            $itemId = (int) ($requirement?->inventory_item_id ?? 0);
            if ($itemId <= 0) {
                continue;
            }

            $unitCost = (float) ($row['unit_cost'] ?? 0);
            if ($unitCost <= 0) {
                $unitCost = (float) ($requirement?->inventoryItem?->standard_cost ?? 0);
            }

            if (! isset($grouped[$itemId])) {
                $grouped[$itemId] = [
                    'inventory_item_id' => (string) $itemId,
                    'quantity' => 0.0,
                    'unit_cost' => $unitCost,
                    'warehouse_id' => $requirement?->warehouse_id ? (int) $requirement->warehouse_id : null,
                ];
            }

            $grouped[$itemId]['quantity'] += $shortfall;
            if ((float) $grouped[$itemId]['unit_cost'] <= 0 && $unitCost > 0) {
                $grouped[$itemId]['unit_cost'] = $unitCost;
            }
        }

        $warehouseId = collect($grouped)->pluck('warehouse_id')->filter()->first();

        return [
            'lines' => collect($grouped)
                ->map(fn (array $line) => [
                    'inventory_item_id' => $line['inventory_item_id'],
                    'quantity' => $this->formatReceiptQuantity((float) $line['quantity']),
                    'unit_cost' => number_format((float) $line['unit_cost'], 2, '.', ''),
                ])
                ->values()
                ->all(),
            'warehouse_id' => $warehouseId ? (int) $warehouseId : null,
        ];
    }

    /**
     * Estimate material panel rows from the sales order BOM without persisting a job card.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function previewPanelRowsForSalesOrder(SalesOrder $salesOrder): Collection
    {
        $salesOrder->loadMissing(['items.inventoryItem.unitOfMeasure', 'inventoryItem.unitOfMeasure']);

        $warehouse = $this->defaultPhysicalWarehouse($salesOrder->company_id, $salesOrder->branch_id);

        if ($warehouse === null) {
            return collect();
        }

        $sources = $this->resolveSourcesFromSalesOrder($salesOrder);

        if ($sources->isEmpty()) {
            return collect();
        }

        /** @var array<string, array<string, mixed>> $aggregated */
        $aggregated = [];

        foreach ($sources as $source) {
            $bom = $this->bomService->findActiveForFinishedItem(
                $salesOrder->company_id,
                $salesOrder->branch_id,
                $source['finished_item_id'],
            );

            if ($bom === null) {
                continue;
            }

            foreach ($this->bomService->requirementsForQuantity($bom, $source['quantity']) as $calc) {
                /** @var \App\Models\Production\ProductBomLine $line */
                $line = $calc['line'];
                $itemId = (int) $line->inventory_item_id;
                $key = $itemId.'|'.$warehouse->id;

                if (! isset($aggregated[$key])) {
                    $aggregated[$key] = [
                        'inventory_item_id' => $itemId,
                        'warehouse_id' => $warehouse->id,
                        'item_name' => $line->inventoryItem?->item_name,
                        'sku' => $line->inventoryItem?->sku,
                        'unit' => $line->inventoryItem?->unitOfMeasure?->code,
                        'required' => 0.0,
                    ];
                }

                $aggregated[$key]['required'] += (float) $calc['required_quantity'];
            }
        }

        return collect($aggregated)->map(function (array $row) use ($salesOrder) {
            $available = $this->availableStockForPreview(
                $salesOrder->company_id,
                $salesOrder->branch_id,
                (int) $row['inventory_item_id'],
            );
            $remaining = round((float) $row['required'], 3);
            $shortfall = max(0, round($remaining - $available, 3));

            return [
                'item_name' => $row['item_name'],
                'sku' => $row['sku'],
                'unit' => $row['unit'],
                'required' => $remaining,
                'remaining' => $remaining,
                'available' => $available,
                'shortfall' => $shortfall,
            ];
        })->values();
    }

    public function availableStock(ProductionMaterialRequirement $requirement): float
    {
        return $this->availableAtWarehouse($requirement, $this->stockWarehouseId($requirement));
    }

    /**
     * Warehouse that can actually fulfil this line. Prefers the bound warehouse,
     * then any physical location that holds the material (e.g. Main Store).
     */
    public function stockWarehouseId(ProductionMaterialRequirement $requirement): int
    {
        $currentId = (int) $requirement->warehouse_id;
        $remaining = $requirement->unreservedRemaining();
        $currentQty = $this->availableAtWarehouse($requirement, $currentId);

        if ($remaining <= 0 || $currentQty >= $remaining) {
            return $currentId;
        }

        $best = $this->physicalWarehouseWithMostStock(
            (int) $requirement->company_id,
            (int) $requirement->branch_id,
            (int) $requirement->inventory_item_id,
        );

        if ($best === null) {
            return $currentId;
        }

        $bestQty = $this->availableAtWarehouse($requirement, (int) $best->id);

        return $bestQty > $currentQty ? (int) $best->id : $currentId;
    }

    protected function availableAtWarehouse(ProductionMaterialRequirement $requirement, int $warehouseId): float
    {
        $balance = InventoryStockService::balance(
            (int) $requirement->inventory_item_id,
            $warehouseId,
        );

        $otherReservations = (float) ProductionMaterialRequirement::query()
            ->where('company_id', $requirement->company_id)
            ->where('branch_id', $requirement->branch_id)
            ->where('inventory_item_id', $requirement->inventory_item_id)
            ->where('warehouse_id', $warehouseId)
            ->where('production_job_card_id', '!=', $requirement->production_job_card_id)
            ->whereIn('status', [
                MaterialRequirementStatus::Planned,
                MaterialRequirementStatus::Reserved,
                MaterialRequirementStatus::Partial,
                MaterialRequirementStatus::Shortfall,
            ])
            ->sum('reserved_quantity');

        return max(0, round($balance - $otherReservations, 3));
    }

    protected function retargetToStockWarehouse(ProductionMaterialRequirement $requirement): ProductionMaterialRequirement
    {
        $warehouseId = $this->stockWarehouseId($requirement);
        if ($warehouseId === (int) $requirement->warehouse_id) {
            return $requirement;
        }

        $requirement->update(['warehouse_id' => $warehouseId]);

        return $requirement->fresh(['inventoryItem', 'warehouse', 'finishedItem', 'jobCard']) ?? $requirement;
    }

    public function resolveStatus(ProductionMaterialRequirement $requirement): MaterialRequirementStatus
    {
        $remaining = $requirement->remainingQuantity();
        $available = $this->availableStock($requirement);

        if ($remaining <= 0) {
            return MaterialRequirementStatus::Fulfilled;
        }

        if ($this->recordedConsumedQuantity($requirement) > 0) {
            return MaterialRequirementStatus::Partial;
        }

        if ((float) $requirement->reserved_quantity >= $remaining) {
            return MaterialRequirementStatus::Reserved;
        }

        if ($remaining > $available) {
            return MaterialRequirementStatus::Shortfall;
        }

        return MaterialRequirementStatus::Planned;
    }

    /**
     * @return Collection<int, array{finished_item_id: int, quantity: float, sales_order_item_id: int|null}>
     */
    /**
     * Soft BOM-based quantity suggestions when formal requirements are not generated yet.
     *
     * @return array<int, array{quantity: float, warehouse_id: int|null}>
     */
    public function suggestQuantities(ProductionJobCard $jobCard): array
    {
        $jobCard->loadMissing(['salesOrder.items']);

        $sources = $this->resolveSources($jobCard);

        if ($sources->isEmpty()) {
            return [];
        }

        $warehouseId = $this->defaultPhysicalWarehouse($jobCard->company_id, $jobCard->branch_id)?->id;

        $suggestions = [];

        foreach ($sources as $source) {
            $bom = $this->bomService->findActiveForFinishedItem(
                $jobCard->company_id,
                $jobCard->branch_id,
                $source['finished_item_id'],
            );

            if ($bom === null) {
                continue;
            }

            foreach ($this->bomService->requirementsForQuantity($bom, $source['quantity']) as $calc) {
                $itemId = (int) $calc['line']->inventory_item_id;
                $qty = (float) $calc['required_quantity'];

                if ($itemId <= 0 || $qty <= 0) {
                    continue;
                }

                if (! isset($suggestions[$itemId])) {
                    $suggestions[$itemId] = [
                        'quantity' => 0.0,
                        'warehouse_id' => $warehouseId ? (int) $warehouseId : null,
                    ];
                }

                $suggestions[$itemId]['quantity'] = round(
                    $suggestions[$itemId]['quantity'] + $qty,
                    3,
                );
            }
        }

        return $suggestions;
    }

    /**
     * @return Collection<int, array{finished_item_id: int, quantity: float, sales_order_item_id: ?int}>
     */
    public function resolveSourcesFromSalesOrder(SalesOrder $salesOrder): Collection
    {
        $salesOrder->loadMissing(['items', 'inventoryItem']);

        $sources = collect();

        foreach ($salesOrder->items as $orderItem) {
            if ($orderItem->inventory_item_id === null) {
                continue;
            }

            $sources->push([
                'finished_item_id' => (int) $orderItem->inventory_item_id,
                'quantity' => (float) $orderItem->quantity,
                'sales_order_item_id' => $orderItem->id,
            ]);
        }

        if ($sources->isEmpty() && $salesOrder->inventory_item_id) {
            $qty = (float) ($salesOrder->items->sum('quantity') ?: 1);
            $sources->push([
                'finished_item_id' => (int) $salesOrder->inventory_item_id,
                'quantity' => $qty > 0 ? $qty : 1,
                'sales_order_item_id' => null,
            ]);
        }

        return $sources;
    }

    /**
     * Guided Materials workflow for Job 360 — surface prerequisites before generate fails.
     *
     * @return array{
     *     has_finished_product: bool,
     *     has_active_bom: bool,
     *     has_requirements: bool,
     *     can_generate: bool,
     *     current_key: string|null,
     *     blocker: string|null,
     *     finished_product_label: string|null,
     *     missing_boms: list<array{finished_item_id: int, quantity: float, sales_order_item_id: ?int, item_name: string|null, sku: string|null}>,
     *     steps: list<array{key: string, status: string, title: string, detail: string}>
     * }
     */
    public function workflowChecklist(ProductionJobCard $jobCard): array
    {
        $jobCard->loadMissing(['salesOrder.items', 'salesOrder.inventoryItem', 'inventoryItem']);

        $sources = $this->resolveSources($jobCard);
        $hasFinishedProduct = $sources->isNotEmpty();
        $hasRequirements = ProductionMaterialRequirement::query()
            ->where('production_job_card_id', $jobCard->id)
            ->exists();
        $missingBoms = $hasFinishedProduct && ! $hasRequirements
            ? $this->missingBomSources($jobCard)
            : collect();
        $hasActiveBom = $hasFinishedProduct && $missingBoms->isEmpty();
        $canGenerate = $hasFinishedProduct && $hasActiveBom && ! $hasRequirements;

        $productLabel = null;
        if ($hasFinishedProduct) {
            $first = $sources->first();
            $item = InventoryItem::query()->find($first['finished_item_id'] ?? null);
            $productLabel = $item
                ? trim(($item->sku ? $item->sku.' — ' : '').$item->item_name)
                : ($jobCard->inventoryItem?->item_name
                    ?? $jobCard->salesOrder?->inventoryItem?->item_name);
        }

        $blocker = match (true) {
            ! $hasFinishedProduct => __('No finished product linked. Assign the catalogue item this job produces before generating requirements.'),
            ! $hasActiveBom && ! $hasRequirements => __('No active BOM for the finished product. Add the bill of materials, then generate requirements.'),
            ! $hasRequirements => __('Material requirements have not been generated yet.'),
            default => null,
        };

        $currentKey = match (true) {
            ! $hasFinishedProduct => 'link_product',
            ! $hasActiveBom && ! $hasRequirements => 'bom',
            ! $hasRequirements => 'generate',
            default => null,
        };

        $steps = [
            [
                'key' => 'link_product',
                'status' => $hasFinishedProduct ? 'done' : 'current',
                'title' => __('Link finished product'),
                'detail' => $hasFinishedProduct
                    ? __('Linked: :product', ['product' => $productLabel ?? __('Catalogue item')])
                    : __('Assign the finished-good inventory item this job produces. Required before BOM and material requirements.'),
            ],
            [
                'key' => 'bom',
                'status' => match (true) {
                    $hasRequirements || $hasActiveBom => 'done',
                    ! $hasFinishedProduct => 'blocked',
                    default => 'current',
                },
                'title' => __('Add bill of materials'),
                'detail' => match (true) {
                    $hasRequirements || $hasActiveBom => __('Active BOM is available for this finished product.'),
                    ! $hasFinishedProduct => __('Complete the finished product step first.'),
                    default => __('Define component materials and quantities for the finished product.'),
                },
            ],
            [
                'key' => 'generate',
                'status' => match (true) {
                    $hasRequirements => 'done',
                    $canGenerate => 'current',
                    default => 'blocked',
                },
                'title' => __('Generate requirements'),
                'detail' => match (true) {
                    $hasRequirements => __('Material requirement lines are on this job.'),
                    $canGenerate => __('Snapshot BOM quantities onto this job, then reserve or receive stock as needed.'),
                    default => __('Available after the finished product and BOM are in place.'),
                },
            ],
            [
                'key' => 'reserve',
                'status' => match (true) {
                    ! $hasRequirements => 'blocked',
                    default => 'current',
                },
                'title' => __('Reserve / clear shortages'),
                'detail' => $hasRequirements
                    ? __('Reserve available stock or receive shortages before production consumes materials.')
                    : __('Generate requirements first.'),
            ],
        ];

        return [
            'has_finished_product' => $hasFinishedProduct,
            'has_active_bom' => $hasActiveBom,
            'has_requirements' => $hasRequirements,
            'can_generate' => $canGenerate,
            'current_key' => $currentKey,
            'blocker' => $blocker,
            'finished_product_label' => $productLabel,
            'missing_boms' => $missingBoms->values()->all(),
            'steps' => $steps,
        ];
    }

    /**
     * Finished products on this job that still need an active BOM.
     *
     * @return Collection<int, array{finished_item_id: int, quantity: float, sales_order_item_id: ?int, item_name: string|null, sku: string|null}>
     */
    public function missingBomSources(ProductionJobCard $jobCard): Collection
    {
        $sources = $this->resolveSources($jobCard);
        if ($sources->isEmpty()) {
            return collect();
        }

        $itemIds = $sources->pluck('finished_item_id')->unique()->values();
        $items = InventoryItem::query()
            ->whereIn('id', $itemIds)
            ->get(['id', 'sku', 'item_name'])
            ->keyBy('id');

        return $sources
            ->unique('finished_item_id')
            ->values()
            ->filter(fn (array $source) => $this->bomService->findActiveForFinishedItem(
                $jobCard->company_id,
                $jobCard->branch_id,
                (int) $source['finished_item_id'],
            ) === null)
            ->map(function (array $source) use ($items) {
                $item = $items->get($source['finished_item_id']);

                return [
                    ...$source,
                    'sku' => $item?->sku,
                    'item_name' => $item?->item_name,
                ];
            })
            ->values();
    }

    protected function resolveSources(ProductionJobCard $jobCard): Collection
    {
        $jobCard->loadMissing(['salesOrder.items', 'salesOrder.inventoryItem']);

        if ($jobCard->salesOrder === null) {
            return collect();
        }

        $sources = $this->resolveSourcesFromSalesOrder($jobCard->salesOrder);

        if ($sources->isEmpty() && $jobCard->inventory_item_id) {
            $qty = (float) ($jobCard->salesOrder->items->sum('quantity') ?: 1);
            $sources->push([
                'finished_item_id' => (int) $jobCard->inventory_item_id,
                'quantity' => $qty > 0 ? $qty : 1,
                'sales_order_item_id' => null,
            ]);
        }

        return $sources;
    }

    protected function availableStockForPreview(
        int $companyId,
        int $branchId,
        int $inventoryItemId,
        ?int $warehouseId = null,
    ): float {
        $warehouseIds = $warehouseId
            ? collect([$warehouseId])
            : $this->physicalWarehouses($companyId, $branchId)->pluck('id');

        if ($warehouseIds->isEmpty()) {
            return 0.0;
        }

        $balance = $warehouseIds
            ->sum(fn ($id) => InventoryStockService::balance($inventoryItemId, (int) $id));

        $otherReservations = (float) ProductionMaterialRequirement::query()
            ->where('company_id', $companyId)
            ->where('branch_id', $branchId)
            ->where('inventory_item_id', $inventoryItemId)
            ->whereIn('warehouse_id', $warehouseIds->all())
            ->whereIn('status', [
                MaterialRequirementStatus::Planned,
                MaterialRequirementStatus::Reserved,
                MaterialRequirementStatus::Partial,
                MaterialRequirementStatus::Shortfall,
            ])
            ->sum('reserved_quantity');

        return max(0, round($balance - $otherReservations, 3));
    }

    /**
     * @return Collection<int, Warehouse>
     */
    protected function physicalWarehouses(int $companyId, int $branchId): Collection
    {
        return Warehouse::query()
            ->where('company_id', $companyId)
            ->where('branch_id', $branchId)
            ->where('is_active', true)
            ->physical()
            ->orderByRaw("CASE WHEN code = 'MAIN' THEN 0 ELSE 1 END")
            ->orderBy('id')
            ->get();
    }

    protected function defaultPhysicalWarehouse(int $companyId, int $branchId): ?Warehouse
    {
        return $this->physicalWarehouses($companyId, $branchId)->first();
    }

    protected function physicalWarehouseWithMostStock(
        int $companyId,
        int $branchId,
        int $inventoryItemId,
    ): ?Warehouse {
        $warehouses = $this->physicalWarehouses($companyId, $branchId);
        $best = $warehouses->first();
        $bestQty = $best ? InventoryStockService::balance($inventoryItemId, $best->id) : -1;

        foreach ($warehouses as $warehouse) {
            $qty = InventoryStockService::balance($inventoryItemId, $warehouse->id);
            if ($qty > $bestQty) {
                $bestQty = $qty;
                $best = $warehouse;
            }
        }

        return $best;
    }

    protected function formatReceiptQuantity(float $qty): string
    {
        return rtrim(rtrim(number_format($qty, 3, '.', ''), '0'), '.') ?: '0';
    }
}
