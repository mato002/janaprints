<x-admin-layout :title="__('Queued emails')" :breadcrumbs="[['label' => __('Email Center'), 'url' => route('admin.communications.email.dashboard')], ['label' => __('Queued')]]">
    @include('admin.communications.email.partials.nav')
    <x-admin.page-header :title="__('Queued')" :description="__('Messages waiting for delivery.')" />
    @include('admin.communications.email.partials.filters')
    @include('admin.communications.email.partials.message-table', ['messages' => $messages, 'viewMode' => $viewMode])
</x-admin-layout>
