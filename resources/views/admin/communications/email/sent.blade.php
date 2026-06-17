<x-admin-layout :title="__('Sent emails')" :breadcrumbs="[['label' => __('Email Center'), 'url' => route('admin.communications.email.dashboard')], ['label' => __('Sent')]]">
    @include('admin.communications.email.partials.nav')
    <x-admin.page-header :title="__('Sent')" :description="__('Successfully delivered outbound messages.')" />
    @include('admin.communications.email.partials.filters')
    @include('admin.communications.email.partials.message-table', ['messages' => $messages, 'viewMode' => $viewMode])
</x-admin-layout>
