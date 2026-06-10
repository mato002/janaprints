<x-admin.modal-form
    :title="__('New support ticket')"
    :breadcrumbs="[['label' => __('Support Tickets'), 'url' => route('admin.commercial.support-tickets.index')], ['label' => __('Create')]]"
    maxWidth="3xl"
>
    <x-admin.form-shell :action="route('admin.commercial.support-tickets.store')">
        @include('admin.commercial.support-tickets.form')
        <x-admin.form-actions>
            <x-primary-button>{{ __('Create ticket') }}</x-primary-button>
        </x-admin.form-actions>
    </x-admin.form-shell>
</x-admin.modal-form>
