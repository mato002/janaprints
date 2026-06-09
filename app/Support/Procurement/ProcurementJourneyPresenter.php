<?php

namespace App\Support\Procurement;

use App\Enums\GoodsReceiptStatus;
use App\Enums\PurchaseRequestStatus;
use App\Enums\RfqStatus;
use App\Models\Procurement\GoodsReceipt;
use App\Models\Procurement\PurchaseOrder;
use App\Models\Procurement\PurchaseRequest;
use App\Models\Procurement\Rfq;
use Illuminate\Support\Collection;

class ProcurementJourneyPresenter
{
    /**
     * @return array{
     *     steps: list<array{key: string, label: string, document: string|null, route: string|null, state: string}>,
     *     conversion_path: string,
     *     destination: string|null,
     * }
     */
    public function present(PurchaseRequest $request): array
    {
        $request->loadMissing([
            'rfqs.awardedVendor',
            'rfqs.purchaseOrder.goodsReceipts',
            'purchaseOrder.goodsReceipts',
            'items.assetCategory',
        ]);

        $rfq = $request->rfqs->sortByDesc('created_at')->first();
        $order = $request->purchaseOrder ?? $this->resolvePurchaseOrder($request, $rfq);
        $receipts = $order?->goodsReceipts ?? collect();
        $postedReceipt = $receipts->first(fn (GoodsReceipt $receipt) => $receipt->status === GoodsReceiptStatus::Posted);
        $directConversion = $order !== null && $request->rfqs->isEmpty();

        $hasCapitalLines = $request->items->contains(
            fn ($item) => (bool) $item->capitalization_required || $item->item_classification?->isCapitalizable()
        );
        $hasInventoryLines = $request->items->contains(
            fn ($item) => $item->item_classification?->requiresInventory() ?? true
        );

        $destination = $this->resolveDestination($postedReceipt, $hasCapitalLines, $hasInventoryLines);

        $steps = [
            $this->step('pr', __('Purchase Request'), $request->request_number, route('admin.procurement.requests.show', $request), $this->purchaseRequestState($request)),
            $this->step('rfq', __('RFQ'), $rfq?->rfq_number, $rfq ? route('admin.procurement.rfqs.show', $rfq) : null, $this->rfqState($request, $rfq, $directConversion)),
            $this->step('award', __('Award'), $rfq?->awardedVendor?->vendor_name, null, $this->awardState($rfq, $directConversion)),
            $this->step('po', __('Purchase Order'), $order?->po_number, $order ? route('admin.procurement.orders.show', $order) : null, $this->purchaseOrderState($order)),
            $this->step('grn', __('GRN'), $postedReceipt?->receipt_number ?? $receipts->first()?->receipt_number, $this->goodsReceiptRoute($postedReceipt ?? $receipts->first()), $this->goodsReceiptState($receipts)),
            $this->step('destination', __('Inventory / Asset'), $destination, $hasCapitalLines ? route('admin.assets.acquisitions.queue') : null, $this->destinationState($postedReceipt, $hasCapitalLines)),
        ];

        return [
            'steps' => $steps,
            'conversion_path' => $directConversion
                ? __('Direct conversion')
                : ($rfq ? __('RFQ sourcing') : __('Not started')),
            'destination' => $destination,
        ];
    }

    protected function resolvePurchaseOrder(PurchaseRequest $request, ?Rfq $rfq): ?PurchaseOrder
    {
        if ($request->purchaseOrder) {
            return $request->purchaseOrder;
        }

        return $request->rfqs
            ->sortByDesc('created_at')
            ->first(fn (Rfq $candidate) => $candidate->purchaseOrder !== null)
            ?->purchaseOrder;
    }

    protected function resolveDestination(?GoodsReceipt $postedReceipt, bool $hasCapitalLines, bool $hasInventoryLines): ?string
    {
        if ($postedReceipt === null) {
            return null;
        }

        if ($hasCapitalLines && $hasInventoryLines) {
            return __('Inventory & asset register');
        }

        if ($hasCapitalLines) {
            return __('Asset register');
        }

        return __('Inventory');
    }

    /**
     * @return array{key: string, label: string, document: string|null, route: string|null, state: string}
     */
    protected function step(string $key, string $label, ?string $document, ?string $route, string $state): array
    {
        return [
            'key' => $key,
            'label' => $label,
            'document' => $document,
            'route' => $route,
            'state' => $state,
        ];
    }

    protected function purchaseRequestState(PurchaseRequest $request): string
    {
        return match ($request->status) {
            PurchaseRequestStatus::Draft => 'active',
            PurchaseRequestStatus::Submitted => 'active',
            default => 'complete',
        };
    }

    protected function rfqState(PurchaseRequest $request, ?Rfq $rfq, bool $directConversion): string
    {
        if ($directConversion) {
            return 'skipped';
        }

        if ($rfq !== null) {
            return 'complete';
        }

        return $request->status === PurchaseRequestStatus::Approved ? 'active' : 'pending';
    }

    protected function awardState(?Rfq $rfq, bool $directConversion): string
    {
        if ($directConversion) {
            return 'skipped';
        }

        if ($rfq === null) {
            return 'pending';
        }

        return in_array($rfq->status, [RfqStatus::Awarded, RfqStatus::ConvertedToPo], true) ? 'complete' : 'pending';
    }

    protected function purchaseOrderState(?PurchaseOrder $order): string
    {
        if ($order === null) {
            return 'pending';
        }

        return 'complete';
    }

    /**
     * @param  Collection<int, GoodsReceipt>  $receipts
     */
    protected function goodsReceiptState(Collection $receipts): string
    {
        if ($receipts->isEmpty()) {
            return 'pending';
        }

        return $receipts->contains(fn (GoodsReceipt $receipt) => $receipt->status === GoodsReceiptStatus::Posted)
            ? 'complete'
            : 'active';
    }

    protected function destinationState(?GoodsReceipt $postedReceipt, bool $hasCapitalLines): string
    {
        if ($postedReceipt === null) {
            return 'pending';
        }

        return $hasCapitalLines ? 'active' : 'complete';
    }

    protected function goodsReceiptRoute(?GoodsReceipt $receipt): ?string
    {
        if ($receipt === null) {
            return null;
        }

        return route('admin.procurement.receipts.show', $receipt);
    }
}
