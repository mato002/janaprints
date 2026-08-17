<?php

namespace App\Support\Production;

use App\Models\Production\ProductionJobCard;
use App\Models\Production\ProductionSpecification;
use App\Models\Sales\SalesOrder;
use App\Models\Sales\SalesOrderItem;
use App\Models\User;

class JobCardPrintSpecificationSnapshotService
{
    public function __construct(
        protected ProductionSpecificationService $specifications,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function snapshotAttributesFromSalesOrder(SalesOrder $salesOrder): array
    {
        $salesOrder->loadMissing(['items']);

        $line = $this->resolveSnapshotLine($salesOrder);
        $orderSource = $salesOrder->is_direct_order ? 'direct' : 'quotation';

        if (! $line && ! $salesOrder->customer_print_specification_id) {
            return ['order_source' => $orderSource];
        }

        return [
            'order_source' => $orderSource,
            'customer_print_specification_id' => $line?->customer_print_specification_id
                ?? $salesOrder->customer_print_specification_id,
            'specification_code' => $line?->specification_code,
            'specification_name' => $line?->specification_name,
            'artwork_version_number' => $line?->artwork_version_number,
            'production_notes_snapshot' => $line?->production_notes_snapshot,
            'commercial_notes_snapshot' => $line?->commercial_notes_snapshot,
            'customer_instructions_snapshot' => $line?->customer_instructions_snapshot,
        ];
    }

    public function ensureProductionSpecificationFromOrderLine(
        ProductionJobCard $jobCard,
        SalesOrder $salesOrder,
        int $createdBy,
    ): ?ProductionSpecification {
        if ($this->specifications->findForJobCard($jobCard)) {
            return null;
        }

        $salesOrder->loadMissing(['items']);
        $line = $this->resolveSnapshotLine($salesOrder);

        if (! $line) {
            return null;
        }

        if ($this->specifications->findForSalesOrderItem($line)) {
            $existing = $this->specifications->findForSalesOrderItem($line);

            return $this->specifications->linkToJobCard($existing, $jobCard);
        }

        $user = User::query()->find($createdBy) ?? User::query()->find($salesOrder->created_by);

        if (! $user) {
            return null;
        }

        $payload = [
            'product_description' => $line->item_name ?? $line->description,
            'quantity' => $line->quantity,
            'production_type' => $jobCard->production_type?->value,
            'production_notes' => $line->production_notes_snapshot,
            'artwork_reference' => $line->specification_code,
            'artwork_version' => $line->artwork_version_number !== null
                ? (string) $line->artwork_version_number
                : null,
        ];

        if ($line->customer_print_specification_id) {
            $line->loadMissing('customerPrintSpecification.inventoryItem');
            $crmSpec = $line->customerPrintSpecification;
            if ($crmSpec?->hasProduct()) {
                $payload['product_description'] = $crmSpec->productLabel();
            }
        }

        $spec = $this->specifications->createForSalesOrderItem($line, $payload, $user);

        return $this->specifications->linkToJobCard($spec, $jobCard);
    }

    protected function resolveSnapshotLine(SalesOrder $salesOrder): ?SalesOrderItem
    {
        $items = $salesOrder->items;

        if ($items->isEmpty()) {
            return null;
        }

        if ($salesOrder->customer_print_specification_id) {
            $matched = $items->first(
                fn (SalesOrderItem $item) => (int) $item->customer_print_specification_id
                    === (int) $salesOrder->customer_print_specification_id,
            );

            if ($matched) {
                return $matched;
            }
        }

        return $items->first(
            fn (SalesOrderItem $item) => $item->customer_print_specification_id !== null,
        ) ?? $items->first();
    }
}
