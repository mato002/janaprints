@php
    use App\Enums\CustomerArtworkType;
    use App\Enums\CustomerPrintSpecificationStatus;
@endphp

<x-admin.lookup-nested-form :title="$title" :action="$action" enctype="multipart/form-data" max-width="3xl">
    @if ($customer)
        <input type="hidden" name="customer_id" value="{{ $customer->id }}">
        <p class="text-sm text-slate-600">
            <span class="font-medium text-slate-800">{{ __('Customer') }}:</span>
            {{ $customer->company_name }}
        </p>
    @else
        <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
            {{ __('Select a customer in the parent form before creating a print specification.') }}
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
        :label="__('Product / inventory item')"
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
            <label class="erp-label" for="default_unit_price">{{ __('Default unit price') }}</label>
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

    <div>
        <label class="erp-label" for="status">{{ __('Status') }}</label>
        <select id="status" name="status" class="erp-input w-full" @required((bool) $customer) @disabled(! $customer)>
            @foreach ($statuses as $status)
                <option value="{{ $status->value }}" @selected(old('status', $defaultStatus ?? CustomerPrintSpecificationStatus::Active->value) === $status->value)>
                    {{ $status->label() }}
                </option>
            @endforeach
        </select>
        <p class="mt-1 text-xs text-slate-500">{{ __('Use Active so the specification is available for orders immediately.') }}</p>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div>
            <label class="erp-label" for="artwork_type">{{ __('Artwork type') }}</label>
            <select id="artwork_type" name="artwork_type" class="erp-input w-full" @disabled(! $customer)>
                @foreach ($artworkTypes as $type)
                    <option value="{{ $type->value }}" @selected(old('artwork_type', CustomerArtworkType::Layout->value) === $type->value)>
                        {{ $type->label() }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="erp-label" for="artwork_file">{{ __('Initial artwork file') }}</label>
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
