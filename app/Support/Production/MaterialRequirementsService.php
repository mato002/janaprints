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
        $warehouse = Warehouse::query()
            ->where('company_id', $jobCard->company_id)
            ->where('branch_id', $jobCard->branch_id)
            ->where('is_active', true)
            ->orderBy('id')
            ->first();

        if ($warehouse === null) {
            return collect();
        }

        if (ProductionMaterialRequirement::query()->where('production_job_card_id', $jobCard->id)->exists()) {
            return ProductionMaterialRequirement::query()
                ->where('production_job_card_id', $jobCard->id)
                ->get();
        }

        try {
            return $this->generate($jobCard, $warehouse->id, $userId, false);
        } catch (ValidationException) {
            return collect();
        }
    }

    /**
     * @return Collection<int, ProductionMaterialRequirement>
     */
    public function generate(ProductionJobCard $jobCard, int $warehouseId, int $userId, bool $replaceExisting = true): Collection
    {
        $jobCard->loadMissing(['salesOrder.items.inventoryItem']);

        $warehouse = Warehouse::query()
            ->where('company_id', $jobCard->company_id)
            ->where('branch_id', $jobCard->branch_id)
            ->where('is_active', true)
            ->find($warehouseId);

        if ($warehouse === null) {
            throw ValidationException::withMessages([
                'warehouse_id' => __('Warehouse not found or inactive.'),
            ]);
        }

        $sources = $this->resolveSources($jobCard);

        if ($sources->isEmpty()) {
            throw ValidationException::withMessages([
                'sales_order' => __('No finished products linked on the sales order. Assign inventory items to order lines before generating requirements.'),
            ]);
        }

        return DB::transaction(function () use ($jobCard, $warehouse, $userId, $replaceExisting, $sources) {
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
                    $unitCost = InventoryCostingService::resolveIssueUnitCost(
                        $jobCard->company_id,
                        $jobCard->branch_id,
                        $line->inventory_item_id,
                        $warehouse->id,
                        $requiredQty,
                    ) ?: (float) ($line->inventoryItem?->standard_cost ?? 0);

                    $existing = ProductionMaterialRequirement::query()
                        ->where('production_job_card_id', $jobCard->id)
                        ->where('inventory_item_id', $line->inventory_item_id)
                        ->where('warehouse_id', $warehouse->id)
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
                        'warehouse_id' => $warehouse->id,
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

            $qty = $quantity ?? $requirement->remainingQuantity();

            if ($qty <= 0) {
                throw ValidationException::withMessages([
                    'quantity' => __('Nothing remaining to consume for this requirement.'),
                ]);
            }

            if ($qty > $requirement->remainingQuantity()) {
                throw ValidationException::withMessages([
                    'quantity' => __('Quantity exceeds remaining requirement. Only :remaining :unit remain.', [
                        'remaining' => $requirement->remainingQuantity(),
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

        return $requirements->map(function (ProductionMaterialRequirement $row) use ($jobCard) {
            $available = $this->availableStock($row);
            $remaining = $row->remainingQuantity();
            $shortfall = max(0, round($remaining - $available - (float) $row->reserved_quantity, 3));

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
                'status' => $row->status,
                'can_reserve' => $row->status->isOpen() && $row->unreservedRemaining() > 0 && $available >= $row->unreservedRemaining(),
                'can_consume' => $remaining > 0 && $available >= min($remaining, $available),
            ];
        });
    }

    /**
     * Estimate material panel rows from the sales order BOM without persisting a job card.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function previewPanelRowsForSalesOrder(SalesOrder $salesOrder): Collection
    {
        $salesOrder->loadMissing(['items.inventoryItem.unitOfMeasure', 'inventoryItem.unitOfMeasure']);

        $warehouse = Warehouse::query()
            ->where('company_id', $salesOrder->company_id)
            ->where('branch_id', $salesOrder->branch_id)
            ->where('is_active', true)
            ->orderBy('id')
            ->first();

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
                (int) $row['warehouse_id'],
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
        $balance = InventoryStockService::balance(
            $requirement->inventory_item_id,
            $requirement->warehouse_id,
        );

        $otherReservations = (float) ProductionMaterialRequirement::query()
            ->where('company_id', $requirement->company_id)
            ->where('branch_id', $requirement->branch_id)
            ->where('inventory_item_id', $requirement->inventory_item_id)
            ->where('warehouse_id', $requirement->warehouse_id)
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

        $warehouseId = Warehouse::query()
            ->where('company_id', $jobCard->company_id)
            ->where('branch_id', $jobCard->branch_id)
            ->where('is_active', true)
            ->orderByRaw("CASE WHEN code = 'MAIN' THEN 0 ELSE 1 END")
            ->orderBy('id')
            ->value('id');

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
        int $warehouseId,
    ): float {
        $balance = InventoryStockService::balance($inventoryItemId, $warehouseId);

        $otherReservations = (float) ProductionMaterialRequirement::query()
            ->where('company_id', $companyId)
            ->where('branch_id', $branchId)
            ->where('inventory_item_id', $inventoryItemId)
            ->where('warehouse_id', $warehouseId)
            ->whereIn('status', [
                MaterialRequirementStatus::Planned,
                MaterialRequirementStatus::Reserved,
                MaterialRequirementStatus::Partial,
                MaterialRequirementStatus::Shortfall,
            ])
            ->sum('reserved_quantity');

        return max(0, round($balance - $otherReservations, 3));
    }
}
