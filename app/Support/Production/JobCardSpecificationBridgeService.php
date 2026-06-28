<?php

namespace App\Support\Production;

use App\Enums\ProductionType;
use App\Models\Production\ProductionJobCard;
use App\Models\Production\ProductionSpecification;
use App\Models\Sales\SalesOrder;
use Illuminate\Support\Collection;

class JobCardSpecificationBridgeService
{
    public function __construct(
        protected ProductionSpecificationService $specifications,
    ) {}

    public function attachOnJobCardCreated(ProductionJobCard $jobCard, SalesOrder $salesOrder): ?ProductionSpecification
    {
        $salesOrder->loadMissing(['items']);

        $spec = $this->resolveUnlinkedSpecification($salesOrder, $jobCard);

        if (! $spec) {
            return null;
        }

        $this->specifications->linkToJobCard($spec, $jobCard);
        $this->syncJobCardFromSpecification($jobCard, $spec);

        return $spec->fresh([
            'paperInventoryItem',
            'materialInventoryItem',
            'inkProfile',
            'printProductTemplate.preferredWorkCenter',
            'printProductTemplate.preferredMachineAsset',
        ]);
    }

    public function resolveForJobCard(ProductionJobCard $jobCard): ?ProductionSpecification
    {
        $spec = $this->specifications->findForJobCard($jobCard);

        if ($spec) {
            $spec->loadMissing([
                'paperInventoryItem',
                'materialInventoryItem',
                'inkProfile',
                'printProductTemplate.preferredWorkCenter',
                'printProductTemplate.preferredMachineAsset',
                'printProductTemplate.recommendedQcChecklist.lines',
                'salesOrderItem',
            ]);
        }

        return $spec;
    }

    protected function resolveUnlinkedSpecification(SalesOrder $salesOrder, ProductionJobCard $jobCard): ?ProductionSpecification
    {
        $candidates = ProductionSpecification::query()
            ->where('sales_order_id', $salesOrder->id)
            ->where(function ($query) use ($jobCard) {
                $query->whereNull('production_job_card_id')
                    ->orWhere('production_job_card_id', $jobCard->id);
            })
            ->orderBy('sales_order_item_id')
            ->get();

        if ($candidates->isEmpty()) {
            return null;
        }

        if ($jobCard->inventory_item_id) {
            $matched = $candidates->first(function (ProductionSpecification $spec) use ($salesOrder, $jobCard) {
                $item = $salesOrder->items->firstWhere('id', $spec->sales_order_item_id);

                return $item && (int) $item->inventory_item_id === (int) $jobCard->inventory_item_id;
            });

            if ($matched) {
                return $matched;
            }
        }

        return $candidates->first();
    }

    protected function syncJobCardFromSpecification(ProductionJobCard $jobCard, ProductionSpecification $spec): void
    {
        $updates = [];

        if ($spec->production_type && in_array($jobCard->production_type, [ProductionType::Mixed, null], true)) {
            $updates['production_type'] = $spec->production_type;
        }

        if ($spec->salesOrderItem && ! $jobCard->inventory_item_id && $spec->salesOrderItem->inventory_item_id) {
            $updates['inventory_item_id'] = $spec->salesOrderItem->inventory_item_id;
        }

        if ($updates !== []) {
            $jobCard->update($updates);
        }
    }

    /**
     * @return Collection<int, ProductionSpecification>
     */
    public function specificationsForSalesOrder(SalesOrder $salesOrder): Collection
    {
        return ProductionSpecification::query()
            ->where('sales_order_id', $salesOrder->id)
            ->orderBy('sales_order_item_id')
            ->get();
    }
}
