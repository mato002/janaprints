@php
    use App\Enums\CustomerPrintSpecificationStatus;
    use App\Support\Crm\CustomerArtworkTypeCatalog;

    $defaultArtworkType = app(CustomerArtworkTypeCatalog::class)->defaultCode();
@endphp

<x-admin.lookup-nested-form :title="$title" :action="$action" enctype="multipart/form-data" max-width="5xl">
    @if ($customer)
        <input type="hidden" name="customer_id" value="{{ $customer->id }}">
        <input type="hidden" name="status" value="{{ old('status', $defaultStatus ?? CustomerPrintSpecificationStatus::Active->value) }}">
        <p class="text-sm text-slate-600">
            <span class="font-medium text-slate-800">{{ __('Customer') }}:</span>
            {{ $customer->company_name }}
        </p>
    @else
        <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
            {{ __('Select a customer first.') }}
        </div>
    @endif

    <div>
        <label class="erp-label" for="name">{{ __('Name') }}</label>
        <input
            type="text"
            id="name"
            name="name"
            class="erp-input w-full"
            value="{{ old('name') }}"
            maxlength="255"
            placeholder="{{ __('e.g. Fortress Receipt Book') }}"
            @required((bool) $customer)
            @disabled(! $customer)
        >
    </div>

    <x-admin.lookup-select
        name="inventory_item_id"
        :label="__('Product')"
        :options="[]"
        :value="old('inventory_item_id')"
        :required="(bool) $customer"
        :disabled="! $customer"
        create-route="admin.inventory.items.quick-create"
        refresh-route="admin.lookups.items"
        permission="catalogue.create"
        :modal-title="__('Create product')"
        select-class="erp-input w-full"
        :empty-option="false"
        :placeholder="__('Select product')"
    />

    @if ($customer)
        @include('admin.crm.customers.print-specifications.partials.job-fields', [
            'customer' => $customer,
            'preselectedDestination' => $preselectedDestination ?? old('production_destination', request('production_destination')),
            'lockDestination' => filled($preselectedDestination ?? old('production_destination', request('production_destination'))),
            'idPrefix' => 'quick-spec',
        ])
    @endif

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div>
            <label class="erp-label" for="default_quantity">{{ __('Default quantity') }}</label>
            <input
                type="number"
                step="0.001"
                min="0"
                id="default_quantity"
                name="default_quantity"
                class="erp-input w-full"
                value="{{ old('default_quantity', '1') }}"
                @disabled(! $customer)
            >
        </div>
        <div>
            <label class="erp-label" for="default_unit_price">{{ __('Unit price') }}</label>
            <input
                type="number"
                step="0.01"
                min="0"
                id="default_unit_price"
                name="default_unit_price"
                class="erp-input w-full"
                value="{{ old('default_unit_price') }}"
                @disabled(! $customer)
            >
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <x-admin.lookup-select
            name="artwork_type"
            :label="__('Artwork type')"
            :options="$artworkTypes"
            :value="old('artwork_type', $defaultArtworkType)"
            create-route="admin.crm.artwork-types.quick-create"
            refresh-route="admin.lookups.artwork_types"
            permission="crm.customers.edit"
            :modal-title="__('Create artwork type')"
            select-class="erp-input w-full"
            :empty-option="false"
            :disabled="! $customer"
        />
        <div>
            <label class="erp-label" for="artwork_file">{{ __('Artwork') }}</label>
            <input
                type="file"
                id="artwork_file"
                name="artwork_file"
                class="erp-input w-full"
                accept=".jpg,.jpeg,.png,.webp,.pdf"
                @disabled(! $customer)
            >
        </div>
    </div>
</x-admin.lookup-nested-form>
