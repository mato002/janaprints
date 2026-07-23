<?php

namespace App\Services\Production;

use App\Enums\InventoryMovementType;
use App\Enums\InventoryStockRole;
use App\Enums\ProductionJobCardStatus;
use App\Enums\ProductionOutputStatus;
use App\Enums\VirtualWarehouseRole;
use App\Models\Inventory\InventoryItem;
use App\Models\Inventory\Warehouse;
use App\Models\Production\ProductBom;
use App\Models\Production\ProductionJobCard;
use App\Models\Production\ProductionMaterialRequirement;
use App\Models\Inventory\InventoryMovement;
use App\Models\Production\ProductionOutput;
use App\Services\Inventory\VirtualWarehouseResolverService;
use App\Support\Accounting\InventoryAccountingPostingService;
use App\Support\Inventory\VirtualWarehouseGuard;
use App\Support\InventoryMovementService;
use App\Support\Production\JobCostingService;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Posts production job completion into Finished Goods virtual inventory.
 *
 * Inventory lifecycle: Raw Materials (physical) → Finished Goods (virtual).
 * Accounting lifecycle: WIP → Finished Goods (via separate consumption postings).
 *
 * @see config('inventory_lifecycle')
 */
class ProductionCompletionService
{
    public const FG_RECEIPT_KEY_PREFIX = 'production_output_fg:';
    public function __construct(
        protected VirtualWarehouseResolverService $virtualWarehouses,
        protected InventoryAccountingPostingService $accounting,
    ) {}

    /**
     * @return array{
     *     eligible: bool,
     *     already_posted: bool,
     *     blockers: list<string>,
     *     blocker_codes: list<string>,
     *     suggested_finished_item_id: int|null,
     *     suggested_quantity_completed: float,
     *     suggested_unit_cost: float|null,
     *     suggested_notes: string|null,
     *     fg_warehouse: array{code: string, name: string}|null
     * }
     */
    public function eligibility(ProductionJobCard $jobCard): array
    {
        $blockers = [];
        $blockerCodes = [];
        $alreadyPosted = $this->jobHasPostedOutput($jobCard);

        if ($jobCard->status === ProductionJobCardStatus::Cancelled) {
            $this->pushBlocker($blockers, $blockerCodes, 'cancelled', __('Cancelled jobs cannot be completed to finished goods.'));
        }

        if (! in_array($jobCard->status, [
            ProductionJobCardStatus::InProduction,
            ProductionJobCardStatus::QualityCheck,
            ProductionJobCardStatus::Completed,
            ProductionJobCardStatus::ReadyForDispatch,
        ], true)) {
            $this->pushBlocker($blockers, $blockerCodes, 'status', __('Job must be in production, quality check, completed, or ready for dispatch status.'));
        }

        if ($jobCard->materialConsumptions()->count() === 0) {
            $this->pushBlocker($blockers, $blockerCodes, 'consumption', __('Record material consumption before completing to finished goods.'));
        }

        $fgWarehouse = null;
        $companyId = $jobCard->company_id;
        if ($companyId === null) {
            $this->pushBlocker($blockers, $blockerCodes, 'company_context', __('Company context is missing for this job.'));
        } else {
            $this->virtualWarehouses->ensureDefaults($companyId);
            $fgWarehouse = $this->virtualWarehouses->finishedGoods($companyId);
            if ($fgWarehouse === null) {
                $this->pushBlocker($blockers, $blockerCodes, 'fg_warehouse', __('Finished goods virtual warehouse could not be created. Ensure the company has at least one branch, then verify virtual locations.'));
            }
        }

        $suggestedQuantity = $this->resolveSuggestedQuantity($jobCard);
        $suggestedItem = $this->resolveFinishedItem($jobCard, null, false);
        if ($suggestedItem === null) {
            $this->pushBlocker($blockers, $blockerCodes, 'finished_item', __('Select a finished inventory item for this output.'));
        } elseif ($suggestedItem->stock_role !== InventoryStockRole::FinishedGood) {
            $this->pushBlocker($blockers, $blockerCodes, 'stock_role', __(':item must be set to stock role Finished Good in Inventory before posting output.', [
                'item' => $suggestedItem->sku.' — '.$suggestedItem->item_name,
            ]));
        }

        $unitCost = $this->deriveUnitCost($jobCard, $suggestedQuantity, null, false);

        return [
            'eligible' => ! $alreadyPosted && $blockers === [],
            'already_posted' => $alreadyPosted,
            'blockers' => $blockers,
            'blocker_codes' => $blockerCodes,
            'suggested_finished_item_id' => $suggestedItem?->id,
            'suggested_quantity_completed' => $suggestedQuantity,
            'suggested_unit_cost' => $unitCost,
            'suggested_notes' => $this->resolveSuggestedNotes($jobCard),
            'fg_warehouse' => $fgWarehouse ? [
                'code' => $fgWarehouse->code,
                'name' => $fgWarehouse->name,
            ] : null,
        ];
    }

    /**
     * @param  list<string>  $blockers
     * @param  list<string>  $blockerCodes
     */
    protected function pushBlocker(array &$blockers, array &$blockerCodes, string $code, string $message): void
    {
        $blockers[] = $message;
        $blockerCodes[] = $code;
    }

    /**
     * @param  array{
     *     finished_inventory_item_id?: int|null,
     *     quantity_completed: float|int|string,
     *     quantity_rejected?: float|int|string|null,
     *     unit_cost?: float|int|string|null,
     *     notes?: string|null
     * }  $data
     */
    public function post(ProductionJobCard $jobCard, array $data, int $userId, bool $allowManualCost = false): ProductionOutput
    {
        return DB::transaction(function () use ($jobCard, $data, $userId, $allowManualCost) {
            $jobCard = ProductionJobCard::query()->lockForUpdate()->findOrFail($jobCard->id);
            $jobCard->load(['materialConsumptions']);

            if ($jobCard->status === ProductionJobCardStatus::Cancelled) {
                throw ValidationException::withMessages([
                    'production_job_card_id' => __('Cancelled jobs cannot be completed to finished goods.'),
                ]);
            }

            $eligibility = $this->eligibility($jobCard);
            if (! $eligibility['eligible'] && empty($data['finished_inventory_item_id'])) {
                throw ValidationException::withMessages([
                    'production_job_card_id' => $eligibility['blockers'],
                ]);
            }

            $quantityCompleted = (float) $data['quantity_completed'];
            $quantityRejected = (float) ($data['quantity_rejected'] ?? 0);

            if ($quantityCompleted <= 0) {
                throw ValidationException::withMessages([
                    'quantity_completed' => __('Quantity completed must be greater than zero.'),
                ]);
            }

            if ($quantityRejected < 0) {
                throw ValidationException::withMessages([
                    'quantity_rejected' => __('Quantity rejected cannot be negative.'),
                ]);
            }

            $finishedItem = $this->resolveFinishedItem(
                $jobCard,
                isset($data['finished_inventory_item_id']) ? (int) $data['finished_inventory_item_id'] : null,
                true,
            );

            if ($finishedItem->stock_role !== InventoryStockRole::FinishedGood) {
                throw ValidationException::withMessages([
                    'finished_inventory_item_id' => __('Finished output item must have stock role Finished Good.'),
                ]);
            }

            $fgWarehouse = $this->resolveFinishedGoodsWarehouse($jobCard->company_id);

            $manualUnitCost = isset($data['unit_cost']) && $data['unit_cost'] !== '' && $data['unit_cost'] !== null
                ? (float) $data['unit_cost']
                : null;

            if ($manualUnitCost !== null && ! $allowManualCost) {
                throw ValidationException::withMessages([
                    'unit_cost' => __('Manual unit cost requires permission.'),
                ]);
            }

            $unitCost = $this->deriveUnitCost($jobCard, $quantityCompleted, $manualUnitCost, true);
            $totalCost = round($quantityCompleted * $unitCost, 2);

            if ($totalCost <= 0) {
                throw ValidationException::withMessages([
                    'unit_cost' => __('Total output cost must be greater than zero.'),
                ]);
            }

            $this->assertJobHasNoPostedOutput($jobCard);

            $output = ProductionOutput::query()->create([
                'company_id' => $jobCard->company_id,
                'branch_id' => $jobCard->branch_id,
                'production_job_card_id' => $jobCard->id,
                'finished_inventory_item_id' => $finishedItem->id,
                'finished_warehouse_id' => $fgWarehouse->id,
                'quantity_completed' => $quantityCompleted,
                'quantity_rejected' => $quantityRejected,
                'unit_cost' => $unitCost,
                'total_cost' => $totalCost,
                'completion_status' => ProductionOutputStatus::Draft,
                'notes' => $data['notes'] ?? null,
                'metadata' => [
                    'job_card_number' => $jobCard->job_card_number,
                ],
            ]);

            $receiptKey = self::fgReceiptKey($output->id);
            $this->assertFgReceiptMovementAvailable($receiptKey);

            $movement = VirtualWarehouseGuard::usingSystemContext(function () use ($jobCard, $finishedItem, $fgWarehouse, $quantityCompleted, $unitCost, $output, $userId, $receiptKey) {
                return InventoryMovementService::record([
                    'company_id' => $jobCard->company_id,
                    'branch_id' => $jobCard->branch_id,
                    'inventory_item_id' => $finishedItem->id,
                    'warehouse_id' => $fgWarehouse->id,
                    'movement_type' => InventoryMovementType::FinishedGoodsReceipt,
                    'quantity' => InventoryMovementService::receiptQuantity($quantityCompleted),
                    'unit_cost' => $unitCost,
                    'reference_type' => ProductionOutput::class,
                    'reference_id' => $output->id,
                    'lifecycle_receipt_key' => $receiptKey,
                    'movement_date' => now()->toDateString(),
                    'created_by' => $userId,
                ]);
            });

            $output->update(['inventory_movement_id' => $movement->id]);

            $journal = $this->accounting->postProductionCompletion($output->fresh(['jobCard']), $userId);

            try {
                $output->update([
                    'completion_status' => ProductionOutputStatus::Posted,
                    'completed_by' => $userId,
                    'completed_at' => now(),
                    'posted_journal_id' => $journal->id,
                    'posted_job_marker' => $jobCard->id,
                ]);
            } catch (QueryException $exception) {
                if ($this->isPostedJobMarkerConflict($exception)) {
                    throw ValidationException::withMessages([
                        'production_job_card_id' => __('This production job has already been completed into Finished Goods.'),
                    ]);
                }

                throw $exception;
            }

            $jobCard->refresh();

            if ($jobCard->status !== ProductionJobCardStatus::ReadyForDispatch) {
                if ($jobCard->status->canTransitionTo(ProductionJobCardStatus::ReadyForDispatch)) {
                    $jobCard->transitionTo(ProductionJobCardStatus::ReadyForDispatch);
                } else {
                    $jobCard->update([
                        'status' => ProductionJobCardStatus::ReadyForDispatch,
                        'actual_end_date' => $jobCard->actual_end_date ?? now(),
                    ]);
                }
            }

            return $output->fresh([
                'jobCard',
                'finishedItem',
                'finishedWarehouse',
                'inventoryMovement',
                'postedJournal',
                'completedByUser',
            ]);
        });
    }

    public function resolveFinishedItem(
        ProductionJobCard $jobCard,
        ?int $explicitItemId,
        bool $strict,
    ): ?InventoryItem {
        if ($explicitItemId) {
            $item = InventoryItem::query()
                ->where('company_id', $jobCard->company_id)
                ->where('branch_id', $jobCard->branch_id)
                ->find($explicitItemId);

            if ($item === null && $strict) {
                throw ValidationException::withMessages([
                    'finished_inventory_item_id' => __('Finished inventory item not found for this job.'),
                ]);
            }

            return $item;
        }

        $requirement = ProductionMaterialRequirement::query()
            ->where('production_job_card_id', $jobCard->id)
            ->whereNotNull('finished_item_id')
            ->with('bom.finishedItem')
            ->first();

        if ($requirement?->finishedItem) {
            return $requirement->finishedItem;
        }

        if ($requirement?->bom?->finishedItem) {
            return $requirement->bom->finishedItem;
        }

        $bom = ProductBom::query()
            ->where('company_id', $jobCard->company_id)
            ->where('branch_id', $jobCard->branch_id)
            ->where('is_active', true)
            ->with('finishedItem')
            ->first();

        if ($bom?->finishedItem) {
            return $bom->finishedItem;
        }

        if ($jobCard->inventory_item_id) {
            $item = InventoryItem::query()
                ->where('company_id', $jobCard->company_id)
                ->where('branch_id', $jobCard->branch_id)
                ->find($jobCard->inventory_item_id);

            if ($item !== null) {
                return $item;
            }
        }

        $jobCard->loadMissing('salesOrder.items');

        foreach ($jobCard->salesOrder?->items ?? [] as $orderLine) {
            if ($orderLine->inventory_item_id === null) {
                continue;
            }

            $item = InventoryItem::query()
                ->where('company_id', $jobCard->company_id)
                ->where('branch_id', $jobCard->branch_id)
                ->find($orderLine->inventory_item_id);

            if ($item !== null) {
                return $item;
            }
        }

        if ($strict) {
            throw ValidationException::withMessages([
                'finished_inventory_item_id' => __('Finished inventory item is required.'),
            ]);
        }

        return null;
    }

    public function resolveSuggestedQuantity(ProductionJobCard $jobCard): float
    {
        $jobCard->loadMissing([
            'salesOrder.items',
            'productionSpecification',
        ]);

        if ($jobCard->productionSpecification && (float) $jobCard->productionSpecification->quantity > 0) {
            return (float) $jobCard->productionSpecification->quantity;
        }

        $salesOrder = $jobCard->salesOrder;

        if ($salesOrder !== null) {
            $items = $salesOrder->items;

            if ($jobCard->inventory_item_id) {
                $matched = $items->first(
                    fn ($item) => (int) $item->inventory_item_id === (int) $jobCard->inventory_item_id,
                );

                if ($matched && (float) $matched->quantity > 0) {
                    return (float) $matched->quantity;
                }
            }

            if ($jobCard->customer_print_specification_id) {
                $matched = $items->first(
                    fn ($item) => (int) $item->customer_print_specification_id
                        === (int) $jobCard->customer_print_specification_id,
                );

                if ($matched && (float) $matched->quantity > 0) {
                    return (float) $matched->quantity;
                }
            }

            if ($items->count() === 1 && (float) $items->first()->quantity > 0) {
                return (float) $items->first()->quantity;
            }

            $sum = (float) $items->sum(fn ($item) => (float) $item->quantity);

            if ($sum > 0) {
                return $sum;
            }
        }

        return 1.0;
    }

    public function resolveSuggestedNotes(ProductionJobCard $jobCard): ?string
    {
        $segments = array_filter([
            $jobCard->production_notes_snapshot,
            $jobCard->customer_instructions_snapshot,
            $jobCard->salesOrder?->notes,
        ]);

        if ($segments === []) {
            return null;
        }

        return implode("\n\n", $segments);
    }

    protected function deriveUnitCost(
        ProductionJobCard $jobCard,
        float $quantityCompleted,
        ?float $manualUnitCost,
        bool $strict,
    ): ?float {
        if ($manualUnitCost !== null) {
            return round($manualUnitCost, 4);
        }

        $sheet = JobCostingService::buildOrRefresh($jobCard);
        $totalJobCost = (float) $sheet->total_cost;

        if ($totalJobCost <= 0) {
            if ($strict) {
                throw ValidationException::withMessages([
                    'unit_cost' => __('Job cost is unavailable. Provide manual unit cost or record material consumption.'),
                ]);
            }

            return null;
        }

        return round($totalJobCost / $quantityCompleted, 4);
    }

    protected function resolveFinishedGoodsWarehouse(int $companyId): Warehouse
    {
        app(VirtualWarehouseResolverService::class)->ensureDefaults($companyId);

        $warehouse = $this->virtualWarehouses->finishedGoods($companyId);

        if ($warehouse === null || ! $warehouse->is_virtual || $warehouse->virtual_role !== VirtualWarehouseRole::FinishedGoods) {
            throw ValidationException::withMessages([
                'warehouse_id' => __('Finished goods virtual warehouse could not be resolved.'),
            ]);
        }

        return $warehouse;
    }

    public static function fgReceiptKey(int $productionOutputId): string
    {
        return self::FG_RECEIPT_KEY_PREFIX.$productionOutputId;
    }

    public function hasPostedFinishedGoods(ProductionJobCard $jobCard): bool
    {
        return $this->jobHasPostedOutput($jobCard);
    }

    protected function jobHasPostedOutput(ProductionJobCard $jobCard): bool
    {
        return ProductionOutput::query()
            ->where('production_job_card_id', $jobCard->id)
            ->where('completion_status', ProductionOutputStatus::Posted)
            ->exists();
    }

    protected function assertJobHasNoPostedOutput(ProductionJobCard $jobCard): void
    {
        if ($this->jobHasPostedOutput($jobCard)) {
            throw ValidationException::withMessages([
                'production_job_card_id' => __('This production job has already been completed into Finished Goods.'),
            ]);
        }
    }

    protected function assertFgReceiptMovementAvailable(string $receiptKey): void
    {
        if (InventoryMovement::query()->where('lifecycle_receipt_key', $receiptKey)->exists()) {
            throw ValidationException::withMessages([
                'production_job_card_id' => __('Finished goods receipt for this production output already exists.'),
            ]);
        }
    }

    protected function isPostedJobMarkerConflict(QueryException $exception): bool
    {
        $message = $exception->getMessage();

        return str_contains($message, 'prod_outputs_one_posted_per_job')
            || str_contains($message, 'posted_job_marker')
            || str_contains($message, 'UNIQUE constraint failed');
    }
}
