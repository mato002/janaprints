<x-admin.modal-form
    :title="__('New artwork request')"
    :breadcrumbs="[['label' => __('Artwork'), 'url' => route('admin.artwork.dashboard')], ['label' => __('New')]]"
    maxWidth="2xl"
>
    <x-admin.form-shell :action="route('admin.artwork.store')" class="space-y-4">
        @if ($fromSalesDesk ?? false)
            <input type="hidden" name="from" value="sales-desk">
        @endif
        @include('admin.artwork.requests.partials.form', [
            'presetCustomerId' => $presetCustomerId ?? null,
        ])
        <x-admin.form-modal-actions>
            <button type="submit" class="erp-btn-primary">{{ __('Create request') }}</button>
        </x-admin.form-modal-actions>
    </x-admin.form-shell>
</x-admin.modal-form>
