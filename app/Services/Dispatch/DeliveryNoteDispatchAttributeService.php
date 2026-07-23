<?php

namespace App\Services\Dispatch;

use App\Models\Crm\CustomerContact;
use App\Models\Dispatch\DeliveryNote;
use App\Models\Employee;

class DeliveryNoteDispatchAttributeService
{
    public function __construct(
        protected DeliveryNoteDispatchFormService $form,
    ) {}

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    public function normalize(DeliveryNote $note, array $attributes): array
    {
        $courierKey = $attributes['courier_key'] ?? null;

        if ($courierKey === 'in_house') {
            $attributes['tracking_number'] = trim((string) ($attributes['tracking_number'] ?? ''))
                ?: $this->form->previewInternalTracking($note);
            $attributes['waybill_number'] = trim((string) ($attributes['waybill_number'] ?? ''))
                ?: $this->form->previewInternalWaybill($note);
        }

        if ($courierKey === 'pickup') {
            $attributes['tracking_number'] = null;
            $attributes['waybill_number'] = null;

            if (empty($attributes['collection_otp'])) {
                $attributes['collection_otp'] = $this->form->previewCollectionOtp($note);
            }
        }

        if ($courierKey === 'pickup' && ! empty($attributes['collector_contact_id'])) {
            $contact = CustomerContact::query()->find($attributes['collector_contact_id']);
            if ($contact) {
                $attributes['recipient_name'] = $contact->name;
                $attributes['recipient_phone'] = $contact->phone;
            }
        }

        $attributes['dispatch_notes'] = $this->mergeDispatchNotes($note, $attributes);

        unset(
            $attributes['vehicle_asset_id'],
            $attributes['driver_employee_id'],
            $attributes['delivery_route'],
            $attributes['collector_contact_id'],
            $attributes['collection_otp'],
            $attributes['delivery_otp'],
            $attributes['collector_id_number'],
            $attributes['collection_date'],
            $attributes['expected_arrival'],
        );

        return $attributes;
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    protected function mergeDispatchNotes(DeliveryNote $note, array $attributes): ?string
    {
        $base = trim((string) ($attributes['dispatch_notes'] ?? $note->dispatch_notes ?? ''));
        $details = [];
        $courierKey = $attributes['courier_key'] ?? null;

        if ($courierKey === 'pickup') {
            if (! empty($attributes['collection_date'])) {
                $details[] = __('Collection date: :date', ['date' => $attributes['collection_date']]);
            }
            if (! empty($attributes['collection_otp'])) {
                $details[] = __('Collection OTP: :otp', ['otp' => $attributes['collection_otp']]);
            }
            if (! empty($attributes['collector_id_number'])) {
                $details[] = __('Collector ID: :id', ['id' => $attributes['collector_id_number']]);
            }
        }

        if ($courierKey === 'in_house') {
            if (! empty($attributes['vehicle_asset_id'])) {
                $details[] = __('Vehicle asset #:id', ['id' => $attributes['vehicle_asset_id']]);
            }
            if (! empty($attributes['driver_employee_id'])) {
                $employee = Employee::query()->find($attributes['driver_employee_id']);
                if ($employee) {
                    $details[] = __('Driver: :name', ['name' => trim($employee->first_name.' '.$employee->last_name)]);
                }
            }
            if (! empty($attributes['delivery_route'])) {
                $routeLabel = config('dispatch_couriers.delivery_routes.'.$attributes['delivery_route']) ?? $attributes['delivery_route'];
                $details[] = __('Route: :route', ['route' => $routeLabel]);
            }
            if (! empty($attributes['expected_arrival'])) {
                $details[] = __('Expected arrival: :at', ['at' => $attributes['expected_arrival']]);
            }
            if (! empty($attributes['delivery_otp'])) {
                $details[] = __('Delivery OTP: :otp', ['otp' => $attributes['delivery_otp']]);
            }
        }

        if ($details === []) {
            return $base !== '' ? $base : null;
        }

        $footer = implode("\n", $details);

        return $base !== '' ? $base."\n\n".$footer : $footer;
    }
}
