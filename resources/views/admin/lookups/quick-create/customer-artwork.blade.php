@php
    use App\Support\Crm\CustomerArtworkTypeCatalog;

    $defaultArtworkType = app(CustomerArtworkTypeCatalog::class)->defaultCode();
@endphp

<x-admin.lookup-nested-form :title="$title" :action="$action" enctype="multipart/form-data" max-width="2xl">
    @if ($customer)
        <input type="hidden" name="customer_id" value="{{ $customer->id }}">
        <p class="text-sm text-slate-600">
            <span class="font-medium text-slate-800">{{ __('Customer') }}:</span>
            {{ $customer->company_name }}
        </p>
    @else
        <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
            {{ __('Select a customer in the parent form before uploading artwork.') }}
        </div>
    @endif

    <div>
        <label class="erp-label" for="artwork_name">{{ __('Artwork name') }}</label>
        <input
            type="text"
            id="artwork_name"
            name="artwork_name"
            class="erp-input w-full"
            value="{{ old('artwork_name') }}"
            maxlength="255"
            @required((bool) $customer)
            @disabled(! $customer)
        >
    </div>

    <x-admin.lookup-select
        name="artwork_type"
        :label="__('Artwork type')"
        :options="$artworkTypes"
        :value="old('artwork_type', $defaultArtworkType)"
        create-route="admin.crm.artwork-types.quick-create"
        refresh-route="admin.lookups.artwork_types"
        permission="crm.customers.update"
        :modal-title="__('Create artwork type')"
        select-class="erp-input w-full"
        :empty-option="false"
        :disabled="! $customer"
        :required="(bool) $customer"
    />

    <div>
        <label class="erp-label" for="file">{{ __('Artwork file') }}</label>
        <input
            type="file"
            id="file"
            name="file"
            class="erp-input w-full"
            accept=".jpg,.jpeg,.png,.webp,.pdf"
            @required((bool) $customer)
            @disabled(! $customer)
        >
        <p class="mt-1 text-xs text-slate-500">{{ __('Accepted formats: JPG, PNG, WebP, PDF. Max 20 MB.') }}</p>
    </div>
</x-admin.lookup-nested-form>
