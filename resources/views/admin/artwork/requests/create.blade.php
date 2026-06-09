<x-admin.modal-form
    :title="__('New artwork request')"
    :breadcrumbs="[['label' => __('Artwork'), 'url' => route('admin.artwork.dashboard')], ['label' => __('New')]]"
    maxWidth="2xl"
>
    <x-admin.form-shell :action="route('admin.artwork.store')" class="space-y-4">
        @include('admin.artwork.requests.partials.form')
        <x-admin.form-modal-actions>
            <button type="submit" class="erp-btn-primary">{{ __('Create request') }}</button>
        </x-admin.form-modal-actions>
    </x-admin.form-shell>
</x-admin.modal-form>
