<?php

namespace App\Support;

use App\Enums\DocumentType;
use App\Enums\ProductionJobCardStatus;
use App\Enums\ProductionPriority;
use App\Enums\ProductionType;
use App\Models\Production\ProductionJobCard;
use App\Models\Sales\SalesOrder;
use App\Support\Platform\NumberingService;
use App\Support\Production\JobCardPrintSpecificationSnapshotService;
use App\Support\Production\ProductionGovernanceBootstrapService;
use App\Support\Production\JobCardSpecificationBridgeService;
use Illuminate\Support\Facades\DB;

class ProductionJobCardService
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public static function createFromSalesOrder(SalesOrder $salesOrder, int $createdBy, array $attributes = []): ProductionJobCard
    {
        ProductionJobCardValidator::assertCanCreateFromSalesOrder($salesOrder);

        return DB::transaction(function () use ($salesOrder, $createdBy, $attributes) {
            $salesOrder->loadMissing(['items', 'inventoryItem']);

            $snapshotService = app(JobCardPrintSpecificationSnapshotService::class);
            $snapshotAttributes = $snapshotService->snapshotAttributesFromSalesOrder($salesOrder);

            $priority = $attributes['priority']
                ?? ($salesOrder->priority?->value ?? ProductionPriority::Normal->value);

            $destinationAttributes = $salesOrder->productionJobAttributes();

            $jobCard = ProductionJobCard::query()->create([
                'company_id' => $salesOrder->company_id,
                'branch_id' => $salesOrder->branch_id,
                'sales_order_id' => $salesOrder->id,
                'customer_id' => $salesOrder->customer_id,
                'quotation_id' => $salesOrder->quotation_id,
                'artwork_request_id' => $salesOrder->artwork_request_id,
                'inventory_item_id' => $salesOrder->inventory_item_id,
                'customer_artwork_id' => $salesOrder->customer_artwork_id,
                ...$snapshotAttributes,
                'job_card_number' => app(NumberingService::class)->next(
                    DocumentType::JobCard,
                    $salesOrder->company_id,
                    $salesOrder->branch_id,
                ),
                'production_type' => $attributes['production_type']
                    ?? $destinationAttributes['production_type']
                    ?? ProductionType::Mixed->value,
                'production_destination' => $attributes['production_destination']
                    ?? $destinationAttributes['production_destination']
                    ?? null,
                'priority' => $priority,
                'planned_start_date' => $attributes['planned_start_date'] ?? null,
                'planned_end_date' => $attributes['planned_end_date'] ?? $salesOrder->required_date,
                'status' => ProductionJobCardStatus::Draft,
                'created_by' => $createdBy,
            ]);

            app(ProductionGovernanceBootstrapService::class)->bootstrapFromSalesOrder($jobCard, $salesOrder);

            $snapshotService->ensureProductionSpecificationFromOrderLine(
                $jobCard->fresh(),
                $salesOrder,
                $createdBy,
            );

            app(JobCardSpecificationBridgeService::class)->attachOnJobCardCreated(
                $jobCard->fresh(),
                $salesOrder,
            );

            return $jobCard->fresh(['productionSpecification', 'serialAllocation', 'inventoryItem']);
        });
    }
}
