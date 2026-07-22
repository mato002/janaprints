<?php

namespace App\Support\Production;

use App\Enums\Dispatch\DeliveryNoteStatus;
use App\Enums\DomainCommunicationEvent;
use App\Enums\FulfilmentMethod;
use App\Enums\FulfilmentStatus;
use App\Enums\ProductionJobCardStatus;
use App\Models\Dispatch\DeliveryNote;
use App\Models\Production\ProductionFulfilment;
use App\Models\Production\ProductionJobCard;
use App\Support\Communications\CommunicationEventDispatcher;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProductionFulfilmentService
{
    public function __construct(
        protected CommunicationEventDispatcher $communications,
    ) {}

    public function resolveForJobCard(ProductionJobCard $jobCard): ProductionFulfilment
    {
        $existing = ProductionFulfilment::query()
            ->where('production_job_card_id', $jobCard->id)
            ->first();

        if ($existing !== null) {
            return $existing;
        }

        $jobCard->loadMissing('salesOrder');

        return ProductionFulfilment::query()->create([
            'company_id' => $jobCard->company_id,
            'branch_id' => $jobCard->branch_id,
            'sales_order_id' => $jobCard->sales_order_id,
            'production_job_card_id' => $jobCard->id,
            'fulfilment_method' => $jobCard->salesOrder?->fulfilment_method ?? FulfilmentMethod::Collection,
            'status' => FulfilmentStatus::Pending,
        ]);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function markReadyForCollection(ProductionJobCard $jobCard, int $userId, array $payload = []): ProductionFulfilment
    {
        $this->assertJobReadyForDispatch($jobCard);

        $fulfilment = $this->resolveForJobCard($jobCard);

        if ($fulfilment->fulfilment_method !== FulfilmentMethod::Collection) {
            throw ValidationException::withMessages([
                'fulfilment_method' => __('This order is configured for delivery, not collection.'),
            ]);
        }

        return DB::transaction(function () use ($fulfilment, $userId, $payload, $jobCard) {
            $fulfilment->update([
                'status' => FulfilmentStatus::ReadyForCollection,
                'prepared_by' => $userId,
                'prepared_at' => now(),
                'collection_notes' => $payload['collection_notes'] ?? null,
            ]);

            $this->communications->dispatch(
                DomainCommunicationEvent::ReadyForCollection,
                $jobCard->fresh(['customer', 'salesOrder']),
                auth()->user(),
            );

            return $fulfilment->fresh(['preparedByUser', 'jobCard', 'salesOrder']);
        });
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function confirmCollection(ProductionFulfilment $fulfilment, int $userId, array $payload): ProductionFulfilment
    {
        if ($fulfilment->status !== FulfilmentStatus::ReadyForCollection) {
            throw ValidationException::withMessages([
                'status' => __('Order must be marked ready for collection first.'),
            ]);
        }

        return DB::transaction(function () use ($fulfilment, $payload) {
            $fulfilment->update([
                'status' => FulfilmentStatus::Collected,
                'collected_by_name' => $payload['collected_by_name'] ?? $payload['collected_by'] ?? null,
                'collector_id_number' => $payload['collector_id_number'] ?? null,
                'collector_phone' => $payload['collector_phone'] ?? null,
                'collected_at' => $payload['collected_at'] ?? now(),
                'collection_remarks' => $payload['collection_remarks'] ?? $payload['remarks'] ?? null,
                'invoice_ready' => true,
            ]);

            return $fulfilment->fresh(['jobCard.customer', 'salesOrder']);
        });
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function recordDeliveryDispatch(ProductionFulfilment $fulfilment, DeliveryNote $note, int $userId): ProductionFulfilment
    {
        if ($fulfilment->status === FulfilmentStatus::Dispatched && $fulfilment->delivery_note_id === $note->id) {
            return $fulfilment;
        }

        return DB::transaction(function () use ($fulfilment, $note, $userId) {
            $fulfilment->update([
                'delivery_note_id' => $note->id,
                'fulfilment_method' => FulfilmentMethod::Delivery,
                'status' => FulfilmentStatus::Dispatched,
                'recipient_name' => $note->recipient_name,
                'recipient_phone' => $note->recipient_phone,
                'delivery_address' => $fulfilment->delivery_address ?? $note->dispatch_notes,
                'dispatched_by' => $userId,
                'dispatched_at' => $note->dispatched_at ?? now(),
                'dispatch_date' => ($note->dispatched_at ?? now())->toDateString(),
            ]);

            $jobCard = $fulfilment->jobCard;
            if ($jobCard) {
                $this->communications->dispatch(
                    DomainCommunicationEvent::Dispatched,
                    $jobCard->fresh(['customer', 'salesOrder']),
                );
            }

            return $fulfilment->fresh(['deliveryNote', 'dispatchedByUser']);
        });
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function confirmDelivery(ProductionFulfilment $fulfilment, int $userId, array $payload = []): ProductionFulfilment
    {
        if ($fulfilment->status !== FulfilmentStatus::Dispatched) {
            throw ValidationException::withMessages([
                'status' => __('Delivery must be dispatched before confirmation.'),
            ]);
        }

        if ($fulfilment->delivery_note_id) {
            $note = DeliveryNote::query()->findOrFail($fulfilment->delivery_note_id);

            app(\App\Services\Dispatch\DeliveryNoteService::class)->deliver($note, $userId, [
                'recipient_name' => $payload['received_by'] ?? $payload['recipient_name'] ?? $fulfilment->recipient_name,
                'recipient_phone' => $payload['recipient_phone'] ?? $fulfilment->recipient_phone,
                'recipient_signature' => $payload['signature_name'] ?? $payload['recipient_signature'] ?? null,
                'delivery_notes' => $payload['delivery_remarks'] ?? $payload['remarks'] ?? null,
                'pod_photo_path' => $payload['pod_photo_path'] ?? null,
            ]);

            return $fulfilment->fresh(['deliveryNote', 'jobCard.customer', 'salesOrder']);
        }

        return DB::transaction(function () use ($fulfilment, $payload) {
            $fulfilment->update([
                'status' => FulfilmentStatus::Delivered,
                'received_by' => $payload['received_by'] ?? null,
                'delivered_at' => $payload['delivered_at'] ?? now(),
                'signature_name' => $payload['signature_name'] ?? null,
                'delivery_remarks' => $payload['delivery_remarks'] ?? $payload['remarks'] ?? null,
                'invoice_ready' => true,
            ]);

            $this->communications->dispatch(
                DomainCommunicationEvent::DeliveryCompleted,
                $fulfilment->jobCard()->with(['customer', 'salesOrder'])->firstOrFail(),
            );

            return $fulfilment->fresh(['jobCard.customer', 'salesOrder']);
        });
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function createAndDispatchDelivery(ProductionJobCard $jobCard, int $userId, array $payload): ProductionFulfilment
    {
        $this->assertJobReadyForDispatch($jobCard);

        $fulfilment = $this->resolveForJobCard($jobCard);

        if ($fulfilment->fulfilment_method !== FulfilmentMethod::Delivery) {
            throw ValidationException::withMessages([
                'fulfilment_method' => __('This order is configured for collection.'),
            ]);
        }

        return DB::transaction(function () use ($jobCard, $userId, $payload, $fulfilment) {
            $fulfilment = $this->prepareDelivery($fulfilment, $payload);

            $authority = app(\App\Services\Dispatch\DeliveryNoteAuthority::class);
            $result = $authority->createDraftFromJobCard($jobCard, [
                'recipient_name' => $fulfilment->recipient_name,
                'recipient_phone' => $fulfilment->recipient_phone,
                'dispatch_notes' => $fulfilment->delivery_address,
                'delivery_date' => $payload['dispatch_date'] ?? $fulfilment->dispatch_date ?? now()->toDateString(),
            ]);
            $note = $result->note;
            $noteService = app(\App\Services\Dispatch\DeliveryNoteService::class);
            $noteService->markPackaged($note, $userId, [
                'package_count' => (int) ($payload['package_count'] ?? 1),
                'package_notes' => $payload['package_notes'] ?? null,
                'delivery_address' => $fulfilment->delivery_address,
            ]);
            $noteService->dispatch($note->fresh(), $userId, [
                'dispatch_notes' => $fulfilment->delivery_address,
                'delivery_address' => $fulfilment->delivery_address,
                'courier_key' => $payload['courier_key'] ?? null,
                'courier_name' => $payload['courier_name'] ?? null,
                'tracking_number' => $payload['tracking_number'] ?? null,
                'waybill_number' => $payload['waybill_number'] ?? null,
            ]);

            return $this->resolveForJobCard($jobCard)->fresh(['deliveryNote', 'dispatchedByUser', 'jobCard', 'salesOrder']);
        });
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function prepareDelivery(ProductionFulfilment $fulfilment, array $payload): ProductionFulfilment
    {
        $this->assertJobReadyForDispatch($fulfilment->jobCard);

        if ($fulfilment->fulfilment_method !== FulfilmentMethod::Delivery) {
            throw ValidationException::withMessages([
                'fulfilment_method' => __('This order is configured for collection.'),
            ]);
        }

        $fulfilment->update([
            'recipient_name' => $payload['recipient_name'] ?? $fulfilment->recipient_name,
            'recipient_phone' => $payload['recipient_phone'] ?? $fulfilment->recipient_phone,
            'delivery_address' => $payload['delivery_address'] ?? $fulfilment->delivery_address,
            'dispatch_date' => $payload['dispatch_date'] ?? $fulfilment->dispatch_date,
        ]);

        return $fulfilment->fresh();
    }

    public function syncFromDeliveryNote(DeliveryNote $note): void
    {
        if (! $note->production_job_card_id) {
            return;
        }

        $fulfilment = $this->resolveForJobCard($note->productionJobCard);

        if ($note->status === DeliveryNoteStatus::Dispatched) {
            $this->recordDeliveryDispatch($fulfilment, $note, (int) ($note->dispatched_by ?? auth()->id()));
        }

        if ($note->status === DeliveryNoteStatus::Delivered) {
            if ($fulfilment->status === FulfilmentStatus::Delivered && $fulfilment->delivery_note_id === $note->id) {
                return;
            }

            $fulfilment->update([
                'delivery_note_id' => $note->id,
                'status' => FulfilmentStatus::Delivered,
                'received_by' => $note->recipient_name,
                'delivered_at' => $note->delivered_at ?? now(),
                'signature_name' => $note->recipient_signature,
                'delivery_remarks' => $note->delivery_notes,
                'invoice_ready' => true,
            ]);

            $this->communications->dispatch(
                DomainCommunicationEvent::DeliveryCompleted,
                $note->productionJobCard()->with(['customer', 'salesOrder'])->firstOrFail(),
            );
        }
    }

    protected function assertJobReadyForDispatch(ProductionJobCard $jobCard): void
    {
        if ($jobCard->status !== ProductionJobCardStatus::ReadyForDispatch) {
            throw ValidationException::withMessages([
                'status' => __('Job card must be ready for dispatch before fulfilment actions.'),
            ]);
        }
    }
}
