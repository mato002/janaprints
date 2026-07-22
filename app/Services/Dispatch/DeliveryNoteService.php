<?php

namespace App\Services\Dispatch;

use App\Enums\Dispatch\DeliveryNoteStatus;
use App\Enums\DocumentType;
use App\Enums\ProductionJobCardStatus;
use App\Events\Dispatch\DeliveryNoteDelivered;
use App\Models\Dispatch\DeliveryNote;
use App\Models\Dispatch\DeliveryNoteItem;
use App\Enums\ProductionOutputStatus;
use App\Models\Production\ProductionJobCard;
use App\Models\Production\ProductionOutput;
use App\Models\Sales\SalesOrderItem;
use App\Services\Production\JobProductionControlService;
use App\Support\Platform\NumberGenerator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DeliveryNoteService
{
    public function __construct(
        protected NumberGenerator $numberGenerator,
        protected JobProductionControlService $productionControl,
        protected DispatchInventoryService $dispatchInventory,
    ) {}

    public function generateDeliveryNoteNumber(int $companyId, ?int $branchId = null): string
    {
        return $this->numberGenerator->generate(
            DocumentType::DeliveryNote,
            $companyId,
            $branchId,
        );
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @param  list<array<string, mixed>>|null  $items
     */
    public function createDraftFromJobCard(
        ProductionJobCard $jobCard,
        array $attributes = [],
        ?array $items = null,
    ): DeliveryNote {
        $this->assertDeliveryNoteCreationEligible($jobCard);

        $existing = DeliveryNote::query()
            ->where('production_job_card_id', $jobCard->id)
            ->whereNotIn('status', [DeliveryNoteStatus::Cancelled->value])
            ->exists();

        if ($existing) {
            throw ValidationException::withMessages([
                'production_job_card_id' => __('An active delivery note already exists for this job.'),
            ]);
        }

        return DB::transaction(function () use ($jobCard, $attributes, $items) {
            $note = DeliveryNote::query()->create([
                'company_id' => $jobCard->company_id,
                'branch_id' => $jobCard->branch_id,
                'delivery_note_number' => $this->generateDeliveryNoteNumber($jobCard->company_id, $jobCard->branch_id),
                'customer_id' => $jobCard->customer_id,
                'sales_order_id' => $jobCard->sales_order_id,
                'production_job_card_id' => $jobCard->id,
                'delivery_date' => $attributes['delivery_date'] ?? now()->toDateString(),
                'status' => DeliveryNoteStatus::Draft,
                'recipient_name' => $attributes['recipient_name'] ?? null,
                'recipient_phone' => $attributes['recipient_phone'] ?? null,
                'delivery_address' => $attributes['delivery_address'] ?? $attributes['dispatch_notes'] ?? null,
                'dispatch_notes' => $attributes['dispatch_notes'] ?? null,
            ]);

            $lineItems = $items ?? $this->defaultItemsFromJob($jobCard);
            $this->syncItems($note, $lineItems);

            return $note->fresh(['items', 'customer', 'productionJobCard', 'salesOrder']);
        });
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @param  list<array<string, mixed>>|null  $items
     */
    public function updateDraft(DeliveryNote $note, array $attributes, ?array $items = null): DeliveryNote
    {
        $this->assertEditable($note);

        return DB::transaction(function () use ($note, $attributes, $items) {
            $note->update([
                'delivery_date' => $attributes['delivery_date'] ?? $note->delivery_date,
                'recipient_name' => array_key_exists('recipient_name', $attributes) ? $attributes['recipient_name'] : $note->recipient_name,
                'recipient_phone' => array_key_exists('recipient_phone', $attributes) ? $attributes['recipient_phone'] : $note->recipient_phone,
                'delivery_address' => array_key_exists('delivery_address', $attributes) ? $attributes['delivery_address'] : $note->delivery_address,
                'dispatch_notes' => array_key_exists('dispatch_notes', $attributes) ? $attributes['dispatch_notes'] : $note->dispatch_notes,
                'delivery_notes' => array_key_exists('delivery_notes', $attributes) ? $attributes['delivery_notes'] : $note->delivery_notes,
            ]);

            if ($items !== null) {
                $this->syncItems($note, $items);
            }

            return $note->fresh(['items', 'customer', 'productionJobCard', 'salesOrder']);
        });
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function markPackaged(DeliveryNote $note, int $userId, array $attributes = []): DeliveryNote
    {
        $this->assertStatus($note, DeliveryNoteStatus::Draft);

        if ($note->isPackaged()) {
            throw ValidationException::withMessages([
                'status' => __('This delivery note is already packaged.'),
            ]);
        }

        if ($note->items()->count() === 0) {
            throw ValidationException::withMessages([
                'items' => __('Delivery note must have at least one line item before packaging.'),
            ]);
        }

        $note->update([
            'package_count' => max(1, (int) ($attributes['package_count'] ?? 1)),
            'package_notes' => $attributes['package_notes'] ?? null,
            'packaged_by' => $userId,
            'packaged_at' => now(),
            'delivery_address' => $attributes['delivery_address'] ?? $note->delivery_address ?? $note->dispatch_notes,
        ]);

        return $note->fresh(['items', 'customer', 'productionJobCard', 'packager']);
    }

    /**
     * @param  array<string, mixed>|string|null  $attributes
     */
    public function dispatch(DeliveryNote $note, int $userId, array|string|null $attributes = []): DeliveryNote
    {
        if (is_string($attributes)) {
            $attributes = ['dispatch_notes' => $attributes];
        }

        $attributes ??= [];

        $this->assertStatus($note, DeliveryNoteStatus::Draft);

        if (! $note->isPackaged()) {
            throw ValidationException::withMessages([
                'status' => __('Package the delivery note before dispatch.'),
            ]);
        }

        if ($note->items()->count() === 0) {
            throw ValidationException::withMessages([
                'items' => __('Delivery note must have at least one line item.'),
            ]);
        }

        return DB::transaction(function () use ($note, $userId, $attributes) {
            $this->dispatchInventory->dispatch($note, $userId);

            $courierName = $attributes['courier_name'] ?? $note->courier_name;
            if (isset($attributes['courier_key'])) {
                $courierName = config('dispatch_couriers.couriers.'.$attributes['courier_key']) ?? $courierName;
            }

            $note->update([
                'status' => DeliveryNoteStatus::Dispatched,
                'dispatched_by' => $userId,
                'dispatched_at' => now(),
                'dispatch_notes' => $attributes['dispatch_notes'] ?? $note->dispatch_notes,
                'delivery_address' => $attributes['delivery_address'] ?? $note->delivery_address,
                'courier_name' => $courierName,
                'tracking_number' => $attributes['tracking_number'] ?? $note->tracking_number,
                'waybill_number' => $attributes['waybill_number'] ?? $note->waybill_number,
            ]);

            $note = $note->fresh(['items', 'items.inventoryItem', 'customer', 'productionJobCard', 'dispatcher']);
            app(\App\Support\Production\ProductionFulfilmentService::class)->syncFromDeliveryNote($note);

            return $note;
        });
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function deliver(DeliveryNote $note, int $userId, array $attributes = []): DeliveryNote
    {
        $this->assertStatus($note, DeliveryNoteStatus::Dispatched);

        return DB::transaction(function () use ($note, $userId, $attributes) {
            $this->dispatchInventory->confirmDelivery($note, $userId);

            $note->update([
                'status' => DeliveryNoteStatus::Delivered,
                'delivered_by' => $userId,
                'delivered_at' => now(),
                'recipient_name' => $attributes['recipient_name'] ?? $note->recipient_name,
                'recipient_phone' => $attributes['recipient_phone'] ?? $note->recipient_phone,
                'recipient_signature' => $attributes['recipient_signature'] ?? $note->recipient_signature,
                'delivery_notes' => $attributes['delivery_notes'] ?? $note->delivery_notes,
                'pod_photo_path' => $attributes['pod_photo_path'] ?? $note->pod_photo_path,
                'pod_captured_at' => ($attributes['pod_photo_path'] ?? $note->pod_photo_path) ? now() : $note->pod_captured_at,
                'invoice_ready' => true,
            ]);

            $note = $note->fresh(['items', 'items.inventoryItem', 'postedJournal', 'customer', 'productionJobCard', 'deliverer']);
            app(\App\Support\Production\ProductionFulfilmentService::class)->syncFromDeliveryNote($note);
            DeliveryNoteDelivered::dispatch($note);

            return $note;
        });
    }

    public function cancel(DeliveryNote $note, ?string $reason = null): DeliveryNote
    {
        if (! $note->status->canCancel()) {
            $message = in_array($note->status, [DeliveryNoteStatus::Dispatched, DeliveryNoteStatus::Delivered], true)
                ? __('Dispatched delivery notes cannot be cancelled. Create a return or reversal workflow if required.')
                : __('This delivery note cannot be cancelled.');

            throw ValidationException::withMessages([
                'status' => $message,
            ]);
        }

        $note->update([
            'status' => DeliveryNoteStatus::Cancelled,
            'delivery_notes' => trim(($note->delivery_notes ?? '')."\n".__('Cancelled').': '.($reason ?? '—')),
        ]);

        return $note->fresh(['items', 'customer', 'productionJobCard']);
    }

    public function assertDeliveryNoteCreationEligible(ProductionJobCard $jobCard): void
    {
        $result = $this->productionControl->deliveryNoteCreationEligibility($jobCard);

        if (! $result['eligible']) {
            throw ValidationException::withMessages([
                'production_job_card_id' => implode(' ', $result['blockers']),
            ]);
        }
    }

    /**
     * @param  list<array<string, mixed>>  $items
     */
    public function syncItems(DeliveryNote $note, array $items): void
    {
        $this->assertEditable($note);

        if ($items === []) {
            throw ValidationException::withMessages([
                'items' => __('At least one delivery line is required.'),
            ]);
        }

        $note->items()->delete();

        foreach ($items as $item) {
            DeliveryNoteItem::query()->create([
                'delivery_note_id' => $note->id,
                'description' => $item['description'],
                'quantity' => $item['quantity'] ?? 1,
                'unit' => $item['unit'] ?? 'pcs',
                'sales_order_item_id' => $item['sales_order_item_id'] ?? null,
                'inventory_item_id' => $item['inventory_item_id'] ?? null,
                'production_output_id' => $item['production_output_id'] ?? null,
                'unit_cost' => $item['unit_cost'] ?? null,
                'total_cost' => $item['total_cost'] ?? null,
            ]);
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function defaultItemsFromJob(ProductionJobCard $jobCard): array
    {
        $outputs = ProductionOutput::query()
            ->where('production_job_card_id', $jobCard->id)
            ->where('completion_status', ProductionOutputStatus::Posted)
            ->with('finishedItem:id,item_name,sku')
            ->orderBy('id')
            ->get();

        if ($outputs->isNotEmpty()) {
            return $outputs->map(fn (ProductionOutput $output) => [
                'description' => $output->finishedItem
                    ? ($output->finishedItem->sku.' — '.$output->finishedItem->item_name)
                    : __('Finished goods output #:id', ['id' => $output->id]),
                'quantity' => (float) $output->quantity_completed,
                'unit' => 'pcs',
                'inventory_item_id' => $output->finished_inventory_item_id,
                'production_output_id' => $output->id,
                'unit_cost' => $output->unit_cost,
                'total_cost' => $output->total_cost,
            ])->all();
        }

        if (! $jobCard->sales_order_id) {
            return [
                [
                    'description' => __('Production deliverable for job :number', ['number' => $jobCard->job_card_number]),
                    'quantity' => 1,
                    'unit' => 'job',
                ],
            ];
        }

        return SalesOrderItem::query()
            ->where('sales_order_id', $jobCard->sales_order_id)
            ->orderBy('sort_order')
            ->get()
            ->map(fn (SalesOrderItem $line) => [
                'description' => $line->description ?: $line->item_name,
                'quantity' => (float) $line->quantity,
                'unit' => 'pcs',
                'sales_order_item_id' => $line->id,
            ])
            ->all();
    }

    protected function assertEditable(DeliveryNote $note): void
    {
        if ($note->status->isImmutable()) {
            throw ValidationException::withMessages([
                'status' => __('Delivered delivery notes cannot be modified.'),
            ]);
        }

        if (! $note->status->isEditable()) {
            throw ValidationException::withMessages([
                'status' => __('Only draft delivery notes can be edited.'),
            ]);
        }
    }

    protected function assertStatus(DeliveryNote $note, DeliveryNoteStatus $expected): void
    {
        if ($note->status !== $expected) {
            throw ValidationException::withMessages([
                'status' => __('Invalid status transition for delivery note.'),
            ]);
        }
    }
}
