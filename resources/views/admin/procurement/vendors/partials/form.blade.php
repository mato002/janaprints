@php
    $vendor = $vendor ?? null;
@endphp
<div>
    <x-input-label for="vendor_name" :value="__('Vendor name')" />
    <x-text-input id="vendor_name" name="vendor_name" class="mt-1 block w-full" :value="old('vendor_name', $vendor?->vendor_name ?? '')" required />
</div>
<div>
    <x-input-label for="vendor_type" :value="__('Vendor type')" />
    <select id="vendor_type" name="vendor_type" class="erp-select mt-1 w-full">
        @foreach ($types as $type)
            <option value="{{ $type->value }}" @selected(old('vendor_type', $vendor?->vendor_type?->value ?? '') === $type->value)>{{ str($type->value)->headline() }}</option>
        @endforeach
    </select>
</div>
<div>
    <x-input-label for="phone" :value="__('Phone')" />
    <x-text-input id="phone" name="phone" class="mt-1 block w-full" :value="old('phone', $vendor?->phone ?? '')" />
</div>
<div>
    <x-input-label for="email" :value="__('Email')" />
    <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $vendor?->email ?? '')" />
</div>
<div>
    <x-input-label for="kra_pin" :value="__('KRA PIN')" />
    <x-text-input id="kra_pin" name="kra_pin" class="mt-1 block w-full" :value="old('kra_pin', $vendor?->kra_pin ?? '')" />
</div>
<div>
    <x-input-label for="payment_terms" :value="__('Payment terms')" />
    <x-text-input id="payment_terms" name="payment_terms" class="mt-1 block w-full" :value="old('payment_terms', $vendor?->payment_terms ?? '')" />
</div>
<div>
    <x-input-label for="status" :value="__('Status')" />
    <select id="status" name="status" class="erp-select mt-1 w-full">
        @foreach ($statuses as $status)
            <option value="{{ $status->value }}" @selected(old('status', $vendor?->status?->value ?? 'active') === $status->value)>{{ str($status->value)->headline() }}</option>
        @endforeach
    </select>
</div>
<div class="md:col-span-2">
    <x-input-label for="address" :value="__('Address')" />
    <textarea id="address" name="address" class="erp-input mt-1 w-full" rows="2">{{ old('address', $vendor?->address ?? '') }}</textarea>
</div>
<div class="md:col-span-2">
    <x-input-label for="notes" :value="__('Notes')" />
    <textarea id="notes" name="notes" class="erp-input mt-1 w-full" rows="2">{{ old('notes', $vendor?->notes ?? '') }}</textarea>
</div>
