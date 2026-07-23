@php
    $dispatchForm = $dispatchForm ?? [];
    $couriers = $couriers ?? [];
    $defaultKey = old('courier_key', $dispatchForm['default_courier_key'] ?? '');
    $suggestedNotes = old('dispatch_notes', $dispatchForm['suggested_dispatch_notes'] ?? '');
    $alpineConfig = [
        'courierKey' => $defaultKey,
        'couriers' => $couriers,
        'profiles' => $dispatchForm['courier_profiles'] ?? [],
        'vehicles' => $dispatchForm['vehicles'] ?? [],
        'previewTracking' => $dispatchForm['preview_tracking'] ?? '',
        'previewWaybill' => $dispatchForm['preview_waybill'] ?? '',
        'collectionOtp' => old('collection_otp', $dispatchForm['collection_otp_preview'] ?? ''),
        'deliveryOtp' => old('delivery_otp', $dispatchForm['delivery_otp_preview'] ?? ''),
        'vehicleId' => old('vehicle_asset_id', ''),
        'driverId' => old('driver_employee_id', ''),
    ];
@endphp

<form
    method="POST"
    action="{{ route('admin.dispatch.delivery-notes.dispatch', $note) }}"
    class="space-y-3 rounded-lg border border-erp-border p-3"
    x-data="{
        courierKey: @js($alpineConfig['courierKey']),
        couriers: @js($alpineConfig['couriers']),
        profiles: @js($alpineConfig['profiles']),
        vehicles: @js($alpineConfig['vehicles']),
        vehicleId: @js($alpineConfig['vehicleId']),
        driverId: @js($alpineConfig['driverId']),
        previewTracking: @js($alpineConfig['previewTracking']),
        previewWaybill: @js($alpineConfig['previewWaybill']),
        collectionOtp: @js($alpineConfig['collectionOtp']),
        deliveryOtp: @js($alpineConfig['deliveryOtp']),
        isExternalCourier() { return ['fargo', 'g4s'].includes(this.courierKey); },
        externalCourierLabel() { return this.couriers[this.courierKey] || @js(__('External courier')); },
        courierProfile() { return this.profiles[this.courierKey] || null; },
        syncDriverFromVehicle() {
            const vehicle = this.vehicles.find((item) => String(item.id) === String(this.vehicleId));
            this.driverId = vehicle?.driver_employee_id ? String(vehicle.driver_employee_id) : '';
        },
        regenerateCollectionOtp() {
            this.collectionOtp = Math.random().toString(36).slice(2, 8).toUpperCase();
        },
    }"
>
    @csrf
    <div class="flex items-center justify-between gap-2">
        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Courier / Dispatch') }}</p>
        <span class="text-[11px] text-slate-500">{{ __('Confirm dispatch') }}</span>
    </div>

    <div>
        <label class="erp-label text-xs">{{ __('Delivery method') }}</label>
        <select name="courier_key" class="erp-input text-sm" required x-model="courierKey">
            <option value="">{{ __('Select delivery method') }}</option>
            @foreach ($couriers as $key => $label)
                <option value="{{ $key }}">{{ $label }}</option>
            @endforeach
        </select>
    </div>

    <div class="space-y-3 rounded-md border border-slate-200 bg-slate-50 p-3" x-show="courierKey === 'pickup'" x-cloak>
        <p class="text-xs font-semibold text-slate-700">{{ __('Customer collection') }}</p>
        <div>
            <label class="erp-label text-xs">{{ __('Collector') }}</label>
            <select name="collector_contact_id" class="erp-select w-full text-sm" :disabled="courierKey !== 'pickup'">
                <option value="">{{ __('Select customer contact') }}</option>
                @foreach ($dispatchForm['customer_contacts'] ?? [] as $contact)
                    <option value="{{ $contact->id }}" @selected(old('collector_contact_id') == $contact->id)>
                        {{ $contact->name }}@if ($contact->phone) · {{ $contact->phone }}@endif
                    </option>
                @endforeach
            </select>
        </div>
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
            <div>
                <label class="erp-label text-xs">{{ __('Collection date') }}</label>
                <input type="date" name="collection_date" class="erp-input w-full text-sm" value="{{ old('collection_date', $dispatchForm['collection_date'] ?? now()->toDateString()) }}" readonly>
            </div>
            <div>
                <label class="erp-label text-xs">{{ __('OTP / collection code') }}</label>
                <div class="flex gap-2">
                    <input type="text" name="collection_otp" class="erp-input w-full text-sm font-mono" x-model="collectionOtp" readonly>
                    <button type="button" class="erp-btn-secondary shrink-0 text-xs" @click="regenerateCollectionOtp()">{{ __('Regenerate') }}</button>
                </div>
            </div>
        </div>
        <div>
            <label class="erp-label text-xs">{{ __('National ID') }}</label>
            <input type="text" name="collector_id_number" class="erp-input w-full text-sm" value="{{ old('collector_id_number') }}" :disabled="courierKey !== 'pickup'">
        </div>
        <div>
            <label class="erp-label text-xs">{{ __('Received by (dispatch officer)') }}</label>
            <input type="text" class="erp-input w-full bg-white text-sm" value="{{ $dispatchForm['dispatch_officer'] ?? '' }}" readonly>
        </div>
    </div>

    <div class="space-y-3 rounded-md border border-slate-200 bg-slate-50 p-3" x-show="courierKey === 'in_house'" x-cloak>
        <p class="text-xs font-semibold text-slate-700">{{ __('In-house delivery') }}</p>
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
            <div>
                <label class="erp-label text-xs">{{ __('Vehicle') }}</label>
                <select name="vehicle_asset_id" class="erp-select w-full text-sm" x-model="vehicleId" @change="syncDriverFromVehicle()" :disabled="courierKey !== 'in_house'">
                    <option value="">{{ __('Select vehicle') }}</option>
                    <template x-for="vehicle in vehicles" :key="vehicle.id">
                        <option :value="vehicle.id" x-text="vehicle.label"></option>
                    </template>
                </select>
            </div>
            <div>
                <label class="erp-label text-xs">{{ __('Driver') }}</label>
                <select name="driver_employee_id" class="erp-select w-full text-sm" x-model="driverId" :disabled="courierKey !== 'in_house'">
                    <option value="">{{ __('Select driver') }}</option>
                    <template x-for="vehicle in vehicles" :key="'driver-'+vehicle.id">
                        <option :value="vehicle.driver_employee_id" x-show="vehicle.driver_employee_id" x-text="vehicle.driver_name"></option>
                    </template>
                </select>
            </div>
            <div>
                <label class="erp-label text-xs">{{ __('Delivery route') }}</label>
                <select name="delivery_route" class="erp-select w-full text-sm" :disabled="courierKey !== 'in_house'">
                    <option value="">{{ __('Select route') }}</option>
                    @foreach ($dispatchForm['delivery_routes'] ?? [] as $route)
                        <option value="{{ $route['value'] }}" @selected(old('delivery_route') === $route['value'])>{{ $route['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="erp-label text-xs">{{ __('Expected arrival') }}</label>
                <input type="text" name="expected_arrival" class="erp-input w-full text-sm" value="{{ old('expected_arrival', $dispatchForm['expected_arrival'] ?? '') }}" readonly>
            </div>
        </div>
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
            <div>
                <label class="erp-label text-xs">{{ __('Tracking number') }}</label>
                <input type="text" class="erp-input w-full bg-white text-sm font-mono" :value="previewTracking" readonly>
                <p class="mt-1 text-[11px] text-emerald-700">{{ __('Auto-generated') }}</p>
            </div>
            <div>
                <label class="erp-label text-xs">{{ __('Waybill') }}</label>
                <input type="text" class="erp-input w-full bg-white text-sm font-mono" :value="previewWaybill" readonly>
                <p class="mt-1 text-[11px] text-emerald-700">{{ __('Auto-generated') }}</p>
            </div>
        </div>
        <template x-if="courierKey === 'in_house'">
            <div>
                <input type="hidden" name="tracking_number" :value="previewTracking">
                <input type="hidden" name="waybill_number" :value="previewWaybill">
            </div>
        </template>
        <div>
            <label class="erp-label text-xs">{{ __('Delivery OTP') }}</label>
            <input type="text" name="delivery_otp" class="erp-input w-full text-sm font-mono" x-model="deliveryOtp" readonly>
        </div>
    </div>

    <div class="space-y-3 rounded-md border border-slate-200 bg-slate-50 p-3" x-show="isExternalCourier()" x-cloak>
        <p class="text-xs font-semibold text-slate-700" x-text="externalCourierLabel()"></p>
        <template x-if="courierProfile()?.integrated">
            <div class="rounded-md border border-sky-200 bg-sky-50 p-3 text-sm text-sky-950">
                <p class="font-semibold">{{ __('Courier integration connected') }}</p>
                <p class="mt-1 text-xs" x-text="courierProfile()?.contact"></p>
                <p class="mt-1 text-xs" x-text="courierProfile()?.sla"></p>
                <button type="button" class="erp-btn-secondary mt-3 text-sm" disabled>{{ __('Create shipment (coming soon)') }}</button>
            </div>
        </template>
        <template x-if="courierProfile() && ! courierProfile().integrated">
            <div class="space-y-3">
                <div class="rounded-md border border-amber-200 bg-amber-50 p-3 text-xs text-amber-950">
                    <p class="font-semibold">{{ __('Manual courier entry') }}</p>
                    <p class="mt-1">{{ __('Enter tracking and waybill from the courier document.') }}</p>
                </div>
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div>
                        <label class="erp-label text-xs">{{ __('Courier tracking') }}</label>
                        <input type="text" name="tracking_number" class="erp-input w-full text-sm font-mono" value="{{ old('tracking_number', $note->tracking_number) }}" :disabled="! isExternalCourier() || courierProfile()?.integrated">
                    </div>
                    <div>
                        <label class="erp-label text-xs">{{ __('Courier waybill') }}</label>
                        <input type="text" name="waybill_number" class="erp-input w-full text-sm font-mono" value="{{ old('waybill_number', $note->waybill_number) }}" :disabled="! isExternalCourier() || courierProfile()?.integrated">
                    </div>
                </div>
            </div>
        </template>
    </div>

    <div class="space-y-3" x-show="courierKey === 'other'" x-cloak>
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
            <div>
                <label class="erp-label text-xs">{{ __('Courier tracking') }}</label>
                <input type="text" name="tracking_number" class="erp-input w-full text-sm font-mono" value="{{ old('tracking_number', $note->tracking_number) }}" :disabled="courierKey !== 'other'">
            </div>
            <div>
                <label class="erp-label text-xs">{{ __('Courier waybill') }}</label>
                <input type="text" name="waybill_number" class="erp-input w-full text-sm font-mono" value="{{ old('waybill_number', $note->waybill_number) }}" :disabled="courierKey !== 'other'">
            </div>
        </div>
    </div>

    <div>
        <label class="erp-label text-xs">{{ __('Dispatch notes') }}</label>
        <textarea name="dispatch_notes" rows="4" class="erp-input w-full text-sm">{{ $suggestedNotes }}</textarea>
    </div>

    @if (! empty($dispatchForm['destination_summary']))
        <div class="rounded-md border border-slate-200 bg-white px-3 py-2 text-xs text-slate-600">
            <p><span class="font-semibold text-slate-800">{{ __('Destination') }}:</span> {{ $dispatchForm['destination_summary'] }}</p>
            @if (! empty($dispatchForm['order_summary']))
                <p class="mt-1"><span class="font-semibold text-slate-800">{{ __('Shipment') }}:</span> {{ $dispatchForm['order_summary'] }}</p>
            @endif
        </div>
    @endif

    <x-primary-button type="submit">{{ __('Dispatch') }}</x-primary-button>
</form>
