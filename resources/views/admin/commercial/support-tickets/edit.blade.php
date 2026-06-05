<x-admin-layout :title="__('Edit Ticket')" :breadcrumbs="[['label' => __('Support Tickets'), 'url' => route('admin.commercial.support-tickets.index')], ['label' => $ticket->ticket_number]]">
    <x-admin.page-header :title="__('Edit ticket')" />
    <x-admin.card>
        <form method="POST" action="{{ route('admin.commercial.support-tickets.update', $ticket) }}" class="space-y-4 p-4">
            @csrf @method('PUT')
            @include('admin.commercial.support-tickets.form', ['ticket' => $ticket])
            <button type="submit" class="erp-btn-primary">{{ __('Save changes') }}</button>
        </form>
    </x-admin.card>
</x-admin-layout>
