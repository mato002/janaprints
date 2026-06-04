<x-admin-layout :title="__('Sent emails')" :breadcrumbs="[['label' => __('Email Center'), 'url' => route('admin.communications.email.dashboard')], ['label' => __('Sent')]]">
    @include('admin.communications.email.partials.nav')
    <x-admin.page-header :title="__('Sent')" />
    @include('admin.communications.email.partials.message-table', ['messages' => $messages])
</x-admin-layout>
