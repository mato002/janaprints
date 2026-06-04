<x-admin-layout :title="__('Delivery tracking')">
    @include('admin.communications.email.partials.nav')
    <x-admin.page-header :title="__('Delivery tracking')" />
    @include('admin.communications.email.partials.message-table', ['messages' => $messages])
</x-admin-layout>
