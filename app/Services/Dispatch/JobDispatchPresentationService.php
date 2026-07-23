<?php

namespace App\Services\Dispatch;

use App\Enums\Dispatch\DeliveryNoteStatus;
use App\Enums\SalesOrderStatus;
use App\Models\Dispatch\DeliveryNote;
use App\Models\Production\ProductionJobCard;
use Illuminate\Support\Facades\Schema;

class JobDispatchPresentationService
{
    public function activeDeliveryNote(ProductionJobCard $jobCard): ?DeliveryNote
    {
        if (! Schema::hasTable('delivery_notes')) {
            return null;
        }

        return DeliveryNote::query()
            ->where('production_job_card_id', $jobCard->id)
            ->whereNot('status', DeliveryNoteStatus::Cancelled)
            ->with(['dispatcher:id,name', 'deliverer:id,name', 'packager:id,name'])
            ->orderByDesc('id')
            ->first();
    }

    /**
     * @return array<string, mixed>
     */
    public function build(ProductionJobCard $jobCard): array
    {
        $note = $this->activeDeliveryNote($jobCard);

        if ($note === null) {
            return [
                'has_delivery_note' => false,
                'delivery_note' => null,
            ];
        }

        $jobCard->loadMissing('salesOrder:id,status,order_number');

        $phase = $this->workflowPhase($note, $jobCard);

        return [
            'has_delivery_note' => true,
            'delivery_note' => $note,
            'workflow_phase' => $phase,
            'workflow_label' => $this->workflowLabel($phase),
            'next_action' => $this->nextActionCopy($phase, $note),
            'summary' => $this->summary($note),
            'timeline' => $this->timeline($note),
            'actions' => $this->actions($note),
            'courier_icon' => $this->courierIcon($note),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function summary(DeliveryNote $note): array
    {
        return [
            'delivery_note_number' => $note->delivery_note_number,
            'status' => $note->status->value,
            'status_label' => match ($note->status) {
                DeliveryNoteStatus::Draft => __('Dispatch created'),
                default => $note->status->label(),
            },
            'dispatch_date' => $note->dispatched_at?->format('M j, Y H:i'),
            'delivery_date' => $note->delivered_at?->format('M j, Y H:i') ?? $note->delivery_date?->format('M j, Y'),
            'courier' => $note->courier_name,
            'driver' => $this->extractDriverFromNotes($note),
            'tracking_number' => $note->tracking_number,
            'waybill_number' => $note->waybill_number,
            'recipient_name' => $note->recipient_name,
            'recipient_phone' => $note->recipient_phone,
            'package_count' => $note->package_count,
            'delivery_address' => $note->delivery_address,
            'dispatch_officer' => $note->dispatcher?->name,
            'show_url' => route('admin.dispatch.delivery-notes.show', $note),
            'track_url' => $this->trackingUrl($note),
            'has_pod' => $note->status === DeliveryNoteStatus::Delivered,
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function timeline(DeliveryNote $note): array
    {
        $steps = [
            [
                'key' => 'created',
                'label' => __('Dispatch created'),
                'at' => $note->created_at?->format('M j, Y H:i'),
                'state' => 'completed',
            ],
            [
                'key' => 'packaged',
                'label' => __('Packaged'),
                'at' => $note->packaged_at?->format('M j, Y H:i'),
                'state' => $note->packaged_at ? 'completed' : ($note->status === DeliveryNoteStatus::Draft ? 'current' : 'completed'),
            ],
            [
                'key' => 'dispatched',
                'label' => __('Dispatched'),
                'at' => $note->dispatched_at?->format('M j, Y H:i'),
                'state' => match (true) {
                    $note->dispatched_at !== null => 'completed',
                    $note->isPackaged() && $note->status === DeliveryNoteStatus::Draft => 'current',
                    default => 'future',
                },
            ],
            [
                'key' => 'delivered',
                'label' => __('Delivered'),
                'at' => $note->delivered_at?->format('M j, Y H:i'),
                'state' => match (true) {
                    $note->delivered_at !== null => 'completed',
                    $note->status === DeliveryNoteStatus::Dispatched => 'current',
                    default => 'future',
                },
            ],
        ];

        return $steps;
    }

    /**
     * @return array{primary: ?array<string, mixed>, secondary: list<array<string, mixed>>, danger: list<array<string, mixed>>}
     */
    protected function actions(DeliveryNote $note): array
    {
        $showUrl = route('admin.dispatch.delivery-notes.show', $note);
        $trackUrl = $this->trackingUrl($note);

        $primary = [
            'label' => __('Open delivery note'),
            'type' => 'link',
            'url' => $showUrl,
            'variant' => 'primary',
        ];

        $secondary = [
            [
                'label' => __('Print delivery note'),
                'type' => 'link',
                'url' => $showUrl,
                'variant' => 'secondary',
                'target' => '_blank',
            ],
        ];

        if ($trackUrl) {
            $secondary[] = [
                'label' => __('Track delivery'),
                'type' => 'link',
                'url' => $trackUrl,
                'variant' => 'secondary',
                'target' => '_blank',
            ];
        }

        if ($note->status === DeliveryNoteStatus::Delivered) {
            $secondary[] = [
                'label' => __('View proof of delivery'),
                'type' => 'link',
                'url' => $showUrl.'#pod',
                'variant' => 'secondary',
            ];
        }

        $danger = [];
        if ($note->status->canCancel() && auth()->user()?->can('cancel', $note)) {
            $danger[] = [
                'label' => __('Cancel dispatch'),
                'type' => 'link',
                'url' => $showUrl,
                'variant' => 'danger',
            ];
        }

        return [
            'primary' => $primary,
            'secondary' => $secondary,
            'danger' => $danger,
        ];
    }

    protected function workflowPhase(DeliveryNote $note, ProductionJobCard $jobCard): string
    {
        if (in_array($jobCard->salesOrder?->status, [SalesOrderStatus::Closed], true)) {
            return 'closed';
        }

        return match ($note->status) {
            DeliveryNoteStatus::Delivered => 'delivered',
            DeliveryNoteStatus::Dispatched => 'dispatched',
            DeliveryNoteStatus::Draft => 'dispatch_created',
            default => 'dispatch_created',
        };
    }

    protected function workflowLabel(string $phase): string
    {
        return match ($phase) {
            'dispatch_created' => __('Dispatch created'),
            'dispatched' => __('Dispatched'),
            'delivered' => __('Delivered'),
            'closed' => __('Closed'),
            default => __('In dispatch'),
        };
    }

    protected function nextActionCopy(string $phase, DeliveryNote $note): string
    {
        return match ($phase) {
            'dispatch_created' => __('Delivery note :number is active. Continue dispatch on the delivery note.', [
                'number' => $note->delivery_note_number,
            ]),
            'dispatched' => __('Shipment is in transit. Track delivery or capture proof of delivery on the delivery note.'),
            'delivered' => __('Delivery complete. Review proof of delivery and billing on the delivery note.'),
            'closed' => __('Job dispatch cycle is complete.'),
            default => __('Manage delivery on the delivery note.'),
        };
    }

    protected function trackingUrl(DeliveryNote $note): ?string
    {
        if (! $note->tracking_number) {
            return null;
        }

        $courierKey = collect(config('dispatch_couriers.couriers', []))
            ->search($note->courier_name);

        if ($courierKey === false) {
            $courierKey = match (true) {
                str_contains(strtolower((string) $note->courier_name), 'fargo') => 'fargo',
                str_contains(strtolower((string) $note->courier_name), 'g4s') => 'g4s',
                default => null,
            };
        }

        $template = $courierKey
            ? (config('dispatch_couriers.courier_profiles.'.$courierKey.'.tracking_url') ?? null)
            : null;

        if ($template) {
            return str_replace('{tracking}', urlencode($note->tracking_number), $template);
        }

        return null;
    }

    protected function courierIcon(DeliveryNote $note): string
    {
        $name = strtolower((string) $note->courier_name);

        return match (true) {
            str_contains($name, 'in-house') => '🚐',
            str_contains($name, 'fargo') => '📦',
            str_contains($name, 'g4s') => '🛡️',
            str_contains($name, 'collection') || str_contains($name, 'pickup') => '🏪',
            default => '🚚',
        };
    }

    protected function extractDriverFromNotes(DeliveryNote $note): ?string
    {
        if (! $note->dispatch_notes) {
            return null;
        }

        if (preg_match('/Driver:\s*(.+)/i', $note->dispatch_notes, $matches)) {
            return trim($matches[1]);
        }

        return null;
    }
}
