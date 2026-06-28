<?php

namespace App\Support\Dispatch;

use App\Enums\Dispatch\DeliveryNoteStatus;
use App\Enums\ProductionJobCardStatus;
use App\Enums\SalesOrderStatus;
use App\Models\Dispatch\DeliveryNote;
use App\Models\Production\ProductionJobCard;
use App\Models\Sales\SalesOrder;
use App\Support\Production\SalesOrderProductionBridgeService;
use Illuminate\Validation\ValidationException;

class DeliverySalesOrderSyncService
{
    public function __construct(
        protected SalesOrderProductionBridgeService $bridge,
    ) {}

    /**
     * @param  array<string, mixed>  $attributes
     * @return array{eligible: bool, blockers: list<string>}
     */
    public function deliveryEligibility(DeliveryNote $note, array $attributes = []): array
    {
        $blockers = [];

        if ($note->status !== DeliveryNoteStatus::Dispatched) {
            $blockers[] = __('Delivery note must be dispatched before delivery confirmation.');
        }

        if (! $this->hasProofOfDelivery($note, $attributes)) {
            $blockers[] = __('Proof of delivery requires a recipient name.');
        }

        $note->loadMissing('productionJobCard', 'salesOrder');

        $jobCard = $note->productionJobCard;

        if ($jobCard === null) {
            $blockers[] = __('Delivery note must be linked to a production job.');
        } elseif (! $this->isJobProductionComplete($jobCard)) {
            $blockers[] = __('Linked production job must be complete and ready for dispatch.');
        }

        return [
            'eligible' => $blockers === [],
            'blockers' => $blockers,
        ];
    }

    /**
     * @param  array<string, mixed>  $attributes
     *
     * @throws ValidationException
     */
    public function assertDeliveryEligible(DeliveryNote $note, array $attributes = []): void
    {
        $result = $this->deliveryEligibility($note, $attributes);

        if ($result['eligible']) {
            return;
        }

        throw ValidationException::withMessages([
            'delivery' => implode(' ', $result['blockers']),
        ]);
    }

    public function syncFromDeliveredNote(DeliveryNote $note): void
    {
        $note->loadMissing('salesOrder');

        if ($note->salesOrder === null) {
            return;
        }

        if ($note->status !== DeliveryNoteStatus::Delivered) {
            return;
        }

        if (! $this->hasProofOfDelivery($note)) {
            return;
        }

        if (! $this->canSynchronizeSalesOrder($note->salesOrder)) {
            return;
        }

        $this->bridge->advanceSalesOrderTo($note->salesOrder, SalesOrderStatus::Delivered);
    }

    public function canSynchronizeSalesOrder(SalesOrder $salesOrder): bool
    {
        return $this->allLinkedJobsProductionComplete($salesOrder)
            && $this->allActiveDeliveryNotesDelivered($salesOrder)
            && $this->allJobsHaveDeliveredNotes($salesOrder);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function hasProofOfDelivery(DeliveryNote $note, array $attributes = []): bool
    {
        $recipientName = $attributes['recipient_name'] ?? $note->recipient_name;

        return is_string($recipientName) && trim($recipientName) !== '';
    }

    public function allLinkedJobsProductionComplete(SalesOrder $salesOrder): bool
    {
        $incompleteCount = ProductionJobCard::query()
            ->where('sales_order_id', $salesOrder->id)
            ->where('status', '!=', ProductionJobCardStatus::Cancelled)
            ->whereNotIn('status', [
                ProductionJobCardStatus::ReadyForDispatch,
                ProductionJobCardStatus::Completed,
            ])
            ->count();

        return $incompleteCount === 0
            && ProductionJobCard::query()
                ->where('sales_order_id', $salesOrder->id)
                ->where('status', '!=', ProductionJobCardStatus::Cancelled)
                ->exists();
    }

    public function allActiveDeliveryNotesDelivered(SalesOrder $salesOrder): bool
    {
        return ! DeliveryNote::query()
            ->where('sales_order_id', $salesOrder->id)
            ->whereNot('status', DeliveryNoteStatus::Cancelled)
            ->where('status', '!=', DeliveryNoteStatus::Delivered)
            ->exists();
    }

    public function allJobsHaveDeliveredNotes(SalesOrder $salesOrder): bool
    {
        $jobIds = ProductionJobCard::query()
            ->where('sales_order_id', $salesOrder->id)
            ->where('status', '!=', ProductionJobCardStatus::Cancelled)
            ->pluck('id');

        if ($jobIds->isEmpty()) {
            return false;
        }

        foreach ($jobIds as $jobId) {
            $hasDelivered = DeliveryNote::query()
                ->where('production_job_card_id', $jobId)
                ->where('status', DeliveryNoteStatus::Delivered)
                ->exists();

            if (! $hasDelivered) {
                return false;
            }
        }

        return true;
    }

    protected function isJobProductionComplete(ProductionJobCard $jobCard): bool
    {
        return in_array($jobCard->status, [
            ProductionJobCardStatus::ReadyForDispatch,
            ProductionJobCardStatus::Completed,
        ], true);
    }
}
