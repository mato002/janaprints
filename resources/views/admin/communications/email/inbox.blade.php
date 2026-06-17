<x-admin-layout :title="__('Failed emails')" :breadcrumbs="[['label' => __('Email Center'), 'url' => route('admin.communications.email.dashboard')], ['label' => __('Failed')]]">
    @include('admin.communications.email.partials.nav')
    <x-admin.page-header :title="__('Failed')" :description="__('Failed and bounced messages requiring attention.')" />
    @include('admin.communications.email.partials.filters')
    @include('admin.communications.email.partials.message-table', ['messages' => $messages, 'viewMode' => $viewMode])
</x-admin-layout>
