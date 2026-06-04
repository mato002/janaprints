<?php

namespace App\Services\Dispatch;

use App\Enums\Dispatch\DeliveryNoteStatus;
use App\Enums\DocumentType;
use App\Enums\ProductionJobCardStatus;
use App\Models\Dispatch\DeliveryNote;
use App\Models\Dispatch\DeliveryNoteItem;
use App\Models\Production\ProductionJobCard;
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
                'dispatch_notes' => array_key_exists('dispatch_notes', $attributes) ? $attributes['dispatch_notes'] : $note->dispatch_notes,
                'delivery_notes' => array_key_exists('delivery_notes', $attributes) ? $attributes['delivery_notes'] : $note->delivery_notes,
            ]);

            if ($items !== null) {
                $this->syncItems($note, $items);
            }

            return $note->fresh(['items', 'customer', 'productionJobCard', 'salesOrder']);
        });
    }

    public function dispatch(DeliveryNote $note, int $userId, ?string $dispatchNotes = null): DeliveryNote
    {
        $this->assertStatus($note, DeliveryNoteStatus::Draft);

        if ($note->items()->count() === 0) {
            throw ValidationException::withMessages([
                'items' => __('Delivery note must have at least one line item.'),
            ]);
        }

        $note->update([
            'status' => DeliveryNoteStatus::Dispatched,
            'dispatched_by' => $userId,
            'dispatched_at' => now(),
            'dispatch_notes' => $dispatchNotes ?? $note->dispatch_notes,
        ]);

        return $note->fresh(['items', 'customer', 'productionJobCard', 'dispatcher']);
    }

    public function deliver(DeliveryNote $note, int $userId, array $attributes = []): DeliveryNote
    {
        $this->assertStatus($note, DeliveryNoteStatus::Dispatched);

        $note->update([
            'status' => DeliveryNoteStatus::Delivered,
            'delivered_by' => $userId,
            'delivered_at' => now(),
            'recipient_name' => $attributes['recipient_name'] ?? $note->recipient_name,
            'recipient_phone' => $attributes['recipient_phone'] ?? $note->recipient_phone,
            'recipient_signature' => $attributes['recipient_signature'] ?? $note->recipient_signature,
            'delivery_notes' => $attributes['delivery_notes'] ?? $note->delivery_notes,
            'invoice_ready' => true,
        ]);

        return $note->fresh(['items', 'customer', 'productionJobCard', 'deliverer']);
    }

    public function cancel(DeliveryNote $note, ?string $reason = null): DeliveryNote
    {
        if (! $note->status->canCancel()) {
            throw ValidationException::withMessages([
                'status' => __('This delivery note cannot be cancelled.'),
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
            ]);
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function defaultItemsFromJob(ProductionJobCard $jobCard): array
    {
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
