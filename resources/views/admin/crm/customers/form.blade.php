@php($fields = $formFields ?? [])
<div class="erp-form-grid">
    @if(($fields['company_name']['visible'] ?? true))
    <div><x-input-label for="company_name" :value="__('Company name')" /><x-text-input id="company_name" name="company_name" class="block mt-1 w-full" :value="old('company_name', $customer?->company_name ?? ($fields['company_name']['default'] ?? ''))" :required="($fields['company_name']['required'] ?? true)" :readonly="($fields['company_name']['read_only'] ?? false)" /></div>
    @endif
    @if(($fields['customer_type']['visible'] ?? true))
    <div><x-input-label for="customer_type" :value="__('Type')" />
        <select name="customer_type" class="erp-select mt-1" @required($fields['customer_type']['required'] ?? true) @disabled($fields['customer_type']['read_only'] ?? false)>
            @foreach ($types as $type)<option value="{{ $type->value }}" @selected(old('customer_type', $customer?->customer_type?->value ?? ($fields['customer_type']['default'] ?? null)) === $type->value)>{{ $type->name }}</option>@endforeach
        </select></div>
    @endif
    @if(($fields['contact_person']['visible'] ?? true))
    <div><x-input-label for="contact_person" :value="__('Contact person')" /><x-text-input id="contact_person" name="contact_person" class="block mt-1 w-full" :value="old('contact_person', $customer?->contact_person ?? ($fields['contact_person']['default'] ?? ''))" :required="($fields['contact_person']['required'] ?? false)" :readonly="($fields['contact_person']['read_only'] ?? false)" /></div>
    @endif
    @if(($fields['phone']['visible'] ?? true))
    <div><x-input-label for="phone" :value="__('Phone')" /><x-text-input id="phone" name="phone" class="block mt-1 w-full" :value="old('phone', $customer?->phone ?? ($fields['phone']['default'] ?? ''))" :required="($fields['phone']['required'] ?? false)" :readonly="($fields['phone']['read_only'] ?? false)" /></div>
    @endif
    @if(($fields['email']['visible'] ?? true))
    <div><x-input-label for="email" :value="__('Email')" /><x-text-input id="email" name="email" type="email" class="block mt-1 w-full" :value="old('email', $customer?->email ?? ($fields['email']['default'] ?? ''))" :required="($fields['email']['required'] ?? false)" :readonly="($fields['email']['read_only'] ?? false)" /></div>
    @endif
    @if(($fields['kra_pin']['visible'] ?? true))
    <div><x-input-label for="kra_pin" :value="__('KRA PIN')" /><x-text-input id="kra_pin" name="kra_pin" class="block mt-1 w-full" :value="old('kra_pin', $customer?->kra_pin ?? ($fields['kra_pin']['default'] ?? ''))" :required="($fields['kra_pin']['required'] ?? false)" :readonly="($fields['kra_pin']['read_only'] ?? false)" /></div>
    @endif
    @if(($fields['website']['visible'] ?? true))
    <div><x-input-label for="website" :value="__('Website')" /><x-text-input id="website" name="website" class="block mt-1 w-full" :value="old('website', $customer?->website ?? ($fields['website']['default'] ?? ''))" :required="($fields['website']['required'] ?? false)" :readonly="($fields['website']['read_only'] ?? false)" /></div>
    @endif
    @if(($fields['physical_address']['visible'] ?? true))
    <div class="md:col-span-2"><x-input-label for="physical_address" :value="__('Physical address')" /><textarea id="physical_address" name="physical_address" class="erp-input mt-1 w-full" rows="2" @required($fields['physical_address']['required'] ?? false) @readonly($fields['physical_address']['read_only'] ?? false)>{{ old('physical_address', $customer?->physical_address ?? ($fields['physical_address']['default'] ?? '')) }}</textarea></div>
    @endif
    @if(($fields['city']['visible'] ?? true))
    <div><x-input-label for="city" :value="__('City')" /><x-text-input id="city" name="city" class="block mt-1 w-full" :value="old('city', $customer?->city ?? ($fields['city']['default'] ?? ''))" :required="($fields['city']['required'] ?? false)" :readonly="($fields['city']['read_only'] ?? false)" /></div>
    @endif
    @if(($fields['credit_limit']['visible'] ?? true))
    <div><x-input-label for="credit_limit" :value="__('Credit limit')" /><x-text-input id="credit_limit" name="credit_limit" type="number" step="0.01" class="block mt-1 w-full" :value="old('credit_limit', $customer?->credit_limit ?? ($fields['credit_limit']['default'] ?? ''))" :required="($fields['credit_limit']['required'] ?? false)" :readonly="($fields['credit_limit']['read_only'] ?? false)" /></div>
    @endif
    @if(($fields['payment_terms']['visible'] ?? true))
    <div><x-input-label for="payment_terms" :value="__('Payment terms')" /><x-text-input id="payment_terms" name="payment_terms" class="block mt-1 w-full" :value="old('payment_terms', $customer?->payment_terms ?? ($fields['payment_terms']['default'] ?? ''))" :required="($fields['payment_terms']['required'] ?? false)" :readonly="($fields['payment_terms']['read_only'] ?? false)" /></div>
    @endif
    @if(($fields['status']['visible'] ?? true))
    <div><x-input-label for="status" :value="__('Status')" />
        <select name="status" class="erp-select mt-1" @required($fields['status']['required'] ?? true) @disabled($fields['status']['read_only'] ?? false)>
            @foreach ($statuses as $s)<option value="{{ $s->value }}" @selected(old('status', $customer?->status?->value ?? ($fields['status']['default'] ?? null)) === $s->value)>{{ $s->name }}</option>@endforeach
        </select></div>
    @endif
    @if (auth()->user()->hasRole('Super Admin') && ! $customer)
        <div><x-input-label for="company_id" :value="__('Company')" />
            <select name="company_id" class="erp-select mt-1">
                @foreach ($companies as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
            </select></div>
        <div><x-input-label for="branch_id" :value="__('Branch')" />
            <select name="branch_id" class="erp-select mt-1">
                @foreach ($branches as $b)<option value="{{ $b->id }}">{{ $b->name }}</option>@endforeach
            </select></div>
    @endif
    @if(($fields['segment_ids']['visible'] ?? true))
    <div class="md:col-span-2"><x-input-label :value="__('Segments')" />
        <div class="mt-2 flex flex-wrap gap-2">
            @foreach ($segments as $segment)
                <label class="inline-flex items-center gap-1 text-sm">
                    <input type="checkbox" name="segment_ids[]" value="{{ $segment->id }}" @checked(in_array($segment->id, old('segment_ids', $customer?->segments->pluck('id')->all() ?? []))) @disabled($fields['segment_ids']['read_only'] ?? false)>
                    {{ $segment->name }}
                </label>
            @endforeach
        </div></div>
    @endif
    @if(($fields['notes']['visible'] ?? true))
    <div class="md:col-span-2"><x-input-label for="notes" :value="__('Notes')" /><textarea id="notes" name="notes" class="erp-input mt-1 w-full" rows="3" @required($fields['notes']['required'] ?? false) @readonly($fields['notes']['read_only'] ?? false)>{{ old('notes', $customer?->notes ?? ($fields['notes']['default'] ?? '')) }}</textarea></div>
    @endif
    @include('admin.partials.form-custom-fields', ['fields' => $fields, 'model' => $customer ?? null])
</div>
