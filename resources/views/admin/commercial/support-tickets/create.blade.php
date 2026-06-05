<x-admin-layout :title="__('New Ticket')" :breadcrumbs="[['label' => __('Support Tickets'), 'url' => route('admin.commercial.support-tickets.index')], ['label' => __('Create')]]">
    <x-admin.page-header :title="__('New support ticket')" />
    <x-admin.card>
        <form method="POST" action="{{ route('admin.commercial.support-tickets.store') }}" class="space-y-4 p-4">
            @csrf
            @include('admin.commercial.support-tickets.form')
            <button type="submit" class="erp-btn-primary">{{ __('Create ticket') }}</button>
        </form>
    </x-admin.card>
</x-admin-layout>
