@php($fields = $formFields ?? [])
<div class="erp-form-grid">
    @if(($fields['company_name']['visible'] ?? true))
    <x-admin.input
        name="company_name"
        :label="__('Company name')"
        :value="old('company_name', $customer?->company_name ?? ($fields['company_name']['default'] ?? ''))"
        :required="($fields['company_name']['required'] ?? true)"
        :readonly="($fields['company_name']['read_only'] ?? false)"
    />
    @endif

    @if(($fields['customer_type']['visible'] ?? true))
    <x-admin.form-field
        name="customer_type"
        :label="__('Type')"
        :required="($fields['customer_type']['required'] ?? true)"
        :readonly="($fields['customer_type']['read_only'] ?? false)"
    >
        <select name="customer_type" class="erp-select w-full" @required($fields['customer_type']['required'] ?? true) @disabled($fields['customer_type']['read_only'] ?? false)>
            @foreach ($types as $type)
                <option value="{{ $type->value }}" @selected(old('customer_type', $customer?->customer_type?->value ?? ($fields['customer_type']['default'] ?? null)) === $type->value)>{{ $type->name }}</option>
            @endforeach
        </select>
    </x-admin.form-field>
    @endif

    @if(($fields['contact_person']['visible'] ?? true))
    <x-admin.input
        name="contact_person"
        :label="__('Contact person')"
        :value="old('contact_person', $customer?->contact_person ?? ($fields['contact_person']['default'] ?? ''))"
        :required="($fields['contact_person']['required'] ?? false)"
        :readonly="($fields['contact_person']['read_only'] ?? false)"
    />
    @endif

    @if(($fields['phone']['visible'] ?? true))
    <x-admin.input
        name="phone"
        :label="__('Phone')"
        :value="old('phone', $customer?->phone ?? ($fields['phone']['default'] ?? ''))"
        :required="($fields['phone']['required'] ?? false)"
        :readonly="($fields['phone']['read_only'] ?? false)"
    />
    @endif

    @if(($fields['email']['visible'] ?? true))
    <x-admin.input
        name="email"
        type="email"
        :label="__('Email')"
        :value="old('email', $customer?->email ?? ($fields['email']['default'] ?? ''))"
        :required="($fields['email']['required'] ?? false)"
        :readonly="($fields['email']['read_only'] ?? false)"
    />
    @endif

    @if(($fields['kra_pin']['visible'] ?? true))
    <x-admin.input
        name="kra_pin"
        :label="__('KRA PIN')"
        :value="old('kra_pin', $customer?->kra_pin ?? ($fields['kra_pin']['default'] ?? ''))"
        :required="($fields['kra_pin']['required'] ?? false)"
        :readonly="($fields['kra_pin']['read_only'] ?? false)"
    />
    @endif

    @if(($fields['website']['visible'] ?? true))
    <x-admin.input
        name="website"
        :label="__('Website')"
        :value="old('website', $customer?->website ?? ($fields['website']['default'] ?? ''))"
        :required="($fields['website']['required'] ?? false)"
        :readonly="($fields['website']['read_only'] ?? false)"
    />
    @endif

    @if(($fields['physical_address']['visible'] ?? true))
    <x-admin.textarea
        name="physical_address"
        :label="__('Physical address')"
        :value="old('physical_address', $customer?->physical_address ?? ($fields['physical_address']['default'] ?? ''))"
        :required="($fields['physical_address']['required'] ?? false)"
        :readonly="($fields['physical_address']['read_only'] ?? false)"
    />
    @endif

    @if(($fields['city']['visible'] ?? true))
    <x-admin.input
        name="city"
        :label="__('City')"
        :value="old('city', $customer?->city ?? ($fields['city']['default'] ?? ''))"
        :required="($fields['city']['required'] ?? false)"
        :readonly="($fields['city']['read_only'] ?? false)"
    />
    @endif

    @if(($fields['credit_limit']['visible'] ?? true))
    <x-admin.input
        name="credit_limit"
        type="number"
        :label="__('Credit limit')"
        :value="old('credit_limit', $customer?->credit_limit ?? ($fields['credit_limit']['default'] ?? 0))"
        :required="($fields['credit_limit']['required'] ?? false)"
        :readonly="($fields['credit_limit']['read_only'] ?? false)"
        step="0.01"
    />
    @endif

    @if(($fields['payment_terms']['visible'] ?? true))
    <x-admin.input
        name="payment_terms"
        :label="__('Payment terms')"
        :value="old('payment_terms', $customer?->payment_terms ?? ($fields['payment_terms']['default'] ?? ''))"
        :required="($fields['payment_terms']['required'] ?? false)"
        :readonly="($fields['payment_terms']['read_only'] ?? false)"
    />
    @endif

    @if($customer && ($fields['status']['visible'] ?? true))
        <x-admin.form-status-select
            form-key="customer"
            :field="$fields['status']"
            :value="$customer?->status"
            :model="$customer"
            select-class="erp-select w-full"
        />
    @endif

    @if (auth()->user()->hasRole('Super Admin') && ! $customer)
        <x-admin.form-field name="company_id" :label="__('Company')" :required="true">
            <select name="company_id" class="erp-select w-full" required>
                @foreach ($companies as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
            </select>
        </x-admin.form-field>
        <x-admin.form-field name="branch_id" :label="__('Branch')" :required="true">
            <select name="branch_id" class="erp-select w-full" required>
                @foreach ($branches as $b)<option value="{{ $b->id }}">{{ $b->name }}</option>@endforeach
            </select>
        </x-admin.form-field>
    @endif

    @if(($fields['segment_ids']['visible'] ?? true))
    <x-admin.form-field name="segment_ids" :label="__('Segments')" :colSpan="2">
        <div class="flex flex-wrap gap-2">
            @foreach ($segments as $segment)
                <label class="inline-flex items-center gap-1 text-sm">
                    <input type="checkbox" name="segment_ids[]" value="{{ $segment->id }}" @checked(in_array($segment->id, old('segment_ids', $customer?->segments->pluck('id')->all() ?? []))) @disabled($fields['segment_ids']['read_only'] ?? false)>
                    {{ $segment->name }}
                </label>
            @endforeach
        </div>
    </x-admin.form-field>
    @endif

    @if(($fields['notes']['visible'] ?? true))
    <x-admin.textarea
        name="notes"
        :label="__('Notes')"
        :value="old('notes', $customer?->notes ?? ($fields['notes']['default'] ?? ''))"
        :required="($fields['notes']['required'] ?? false)"
        :readonly="($fields['notes']['read_only'] ?? false)"
    />
    @endif

    @include('admin.partials.form-custom-fields', ['fields' => $fields, 'model' => $customer ?? null, 'formKey' => 'customer'])
</div>
