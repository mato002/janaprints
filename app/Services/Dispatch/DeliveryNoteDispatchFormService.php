<?php

namespace App\Services\Dispatch;

use App\Enums\AssetType;
use App\Enums\FulfilmentMethod;
use App\Enums\IntegrationProviderStatus;
use App\Models\Assets\FixedAsset;
use App\Models\Assets\VehicleDriverAssignment;
use App\Models\Crm\CustomerContact;
use App\Models\Dispatch\DeliveryNote;
use App\Models\Integrations\IntegrationProvider;
use App\Models\User;

class DeliveryNoteDispatchFormService
{
    public function build(DeliveryNote $note, ?User $user = null): array
    {
        $user ??= auth()->user();
        $note->loadMissing(['customer', 'salesOrder', 'productionJobCard', 'items']);

        $defaultCourierKey = $this->defaultCourierKey($note);
        $contacts = $this->customerContacts($note);
        $primaryContact = $contacts->firstWhere('is_primary', true) ?? $contacts->first();
        $vehicles = $this->vehicles($note);
        $courierProfiles = $this->courierProfiles($note);

        return [
            'default_courier_key' => $defaultCourierKey,
            'suggested_dispatch_notes' => $this->suggestedDispatchNotes($note, $primaryContact, $user),
            'dispatch_officer' => $user?->name,
            'collection_date' => now()->toDateString(),
            'expected_arrival' => now()->addHours(4)->format('Y-m-d H:i'),
            'collection_otp_preview' => $this->previewCollectionOtp($note),
            'preview_tracking' => $this->previewInternalTracking($note),
            'preview_waybill' => $this->previewInternalWaybill($note),
            'delivery_otp_preview' => $this->previewDeliveryOtp($note),
            'customer_contacts' => $contacts,
            'primary_contact' => $primaryContact,
            'vehicles' => $vehicles,
            'delivery_routes' => collect(config('dispatch_couriers.delivery_routes', []))
                ->map(fn ($label, $key) => ['value' => $key, 'label' => __($label)])
                ->values()
                ->all(),
            'courier_profiles' => $courierProfiles,
            'destination_summary' => $this->destinationSummary($note),
            'order_summary' => $this->orderSummary($note),
        ];
    }

    public function previewInternalTracking(DeliveryNote $note): string
    {
        return sprintf('JPD-%s-%06d', now()->format('Y'), $note->id);
    }

    public function previewInternalWaybill(DeliveryNote $note): string
    {
        return sprintf('JP-WB-%s-%06d', now()->format('Y'), $note->id);
    }

    public function previewCollectionOtp(DeliveryNote $note): string
    {
        return strtoupper(substr(hash('crc32b', $note->id.'|'.$note->delivery_note_number.'|collection'), 0, 6));
    }

    public function previewDeliveryOtp(DeliveryNote $note): string
    {
        return strtoupper(substr(hash('crc32b', $note->id.'|'.$note->delivery_note_number.'|delivery'), 0, 6));
    }

    protected function defaultCourierKey(DeliveryNote $note): string
    {
        $method = $note->salesOrder?->fulfilment_method;

        if ($method === FulfilmentMethod::Collection) {
            return 'pickup';
        }

        return 'in_house';
    }

    protected function customerContacts(DeliveryNote $note)
    {
        if (! $note->customer_id) {
            return collect();
        }

        return CustomerContact::query()
            ->where('customer_id', $note->customer_id)
            ->orderByDesc('is_primary')
            ->orderBy('name')
            ->get(['id', 'name', 'phone', 'email', 'job_title', 'is_primary']);
    }

    protected function vehicles(DeliveryNote $note): array
    {
        $vehicles = FixedAsset::query()
            ->forTenant()
            ->whereNull('archived_at')
            ->whereHas('category', fn ($query) => $query->where('asset_type', AssetType::Vehicle->value))
            ->orderBy('asset_name')
            ->get(['id', 'asset_name', 'asset_number']);

        if ($vehicles->isEmpty()) {
            return [];
        }

        $assignments = VehicleDriverAssignment::query()
            ->whereIn('vehicle_asset_id', $vehicles->pluck('id'))
            ->where(function ($query) {
                $query->whereNull('end_date')->orWhere('end_date', '>=', now()->toDateString());
            })
            ->with('employee:id,first_name,last_name')
            ->get()
            ->keyBy('vehicle_asset_id');

        return $vehicles->map(function (FixedAsset $vehicle) use ($assignments) {
            $assignment = $assignments->get($vehicle->id);
            $driverName = $assignment?->employee
                ? trim($assignment->employee->first_name.' '.$assignment->employee->last_name)
                : null;

            return [
                'id' => $vehicle->id,
                'label' => $vehicle->asset_name ?: $vehicle->asset_number,
                'driver_employee_id' => $assignment?->employee_id,
                'driver_name' => $driverName,
            ];
        })->values()->all();
    }

    protected function courierProfiles(DeliveryNote $note): array
    {
        $profiles = config('dispatch_couriers.courier_profiles', []);
        $integrationKeys = config('dispatch_couriers.integration_keys', []);
        $connected = IntegrationProvider::query()
            ->where('company_id', $note->company_id)
            ->whereIn('provider_key', array_values($integrationKeys))
            ->where('status', IntegrationProviderStatus::Connected)
            ->pluck('provider_key')
            ->all();

        $result = [];

        foreach (config('dispatch_couriers.couriers', []) as $key => $label) {
            if (! in_array($key, ['fargo', 'g4s', 'other'], true)) {
                continue;
            }

            $providerKey = $integrationKeys[$key] ?? null;
            $meta = $profiles[$key] ?? [];

            $result[$key] = [
                'label' => $label,
                'contact' => $meta['contact'] ?? null,
                'sla' => $meta['sla'] ?? null,
                'tracking_url_template' => $meta['tracking_url'] ?? null,
                'integrated' => $providerKey && in_array($providerKey, $connected, true),
            ];
        }

        return $result;
    }

    protected function suggestedDispatchNotes(DeliveryNote $note, ?CustomerContact $contact, ?User $user): string
    {
        $lines = [];

        if ($note->salesOrder?->order_number) {
            $lines[] = __('Order :number', ['number' => $note->salesOrder->order_number]);
        } elseif ($note->productionJobCard?->job_card_number) {
            $lines[] = __('Job :number', ['number' => $note->productionJobCard->job_card_number]);
        }

        $packageCount = max(1, (int) ($note->package_count ?? 1));
        $lines[] = trans_choice(':count package|:count packages', $packageCount, ['count' => $packageCount]);

        $lines[] = __('Handle with care.');

        if ($note->delivery_address) {
            $lines[] = __('Destination:').' '.$note->delivery_address;
        }

        if ($contact) {
            $lines[] = __('Customer contact:').' '.$contact->name.($contact->phone ? ' · '.$contact->phone : '');
        }

        if ($user?->name) {
            $lines[] = __('Dispatch officer:').' '.$user->name;
        }

        return implode("\n", $lines);
    }

    protected function destinationSummary(DeliveryNote $note): ?string
    {
        return $note->delivery_address ?: $note->dispatch_notes;
    }

    protected function orderSummary(DeliveryNote $note): string
    {
        $qty = $note->items->sum(fn ($item) => (float) $item->quantity);
        $itemCount = $note->items->count();

        return trans_choice(
            ':items line · :qty total qty|:items lines · :qty total qty',
            $itemCount,
            ['items' => $itemCount, 'qty' => number_format($qty, 0)]
        );
    }
}
