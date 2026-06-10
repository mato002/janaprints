<x-admin.modal-form
    :title="__('Edit ticket')"
    :breadcrumbs="[['label' => __('Support Tickets'), 'url' => route('admin.commercial.support-tickets.index')], ['label' => $ticket->ticket_number]]"
    maxWidth="3xl"
>
    <x-admin.form-shell :action="route('admin.commercial.support-tickets.update', $ticket)" method="PUT">
        @include('admin.commercial.support-tickets.form', ['ticket' => $ticket])
        <x-admin.form-actions>
            <x-primary-button>{{ __('Save changes') }}</x-primary-button>
        </x-admin.form-actions>
    </x-admin.form-shell>
</x-admin.modal-form>
