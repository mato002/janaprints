<x-admin-layout :title="__('Email inbox')" :breadcrumbs="[['label' => __('Email Center'), 'url' => route('admin.communications.email.dashboard')], ['label' => __('Inbox')]]">
    @include('admin.communications.email.partials.nav')
    <x-admin.page-header :title="__('Inbox')" :description="__('Failed and bounced messages requiring attention.')" />
    @include('admin.communications.email.partials.message-table', ['messages' => $messages])
</x-admin-layout>
