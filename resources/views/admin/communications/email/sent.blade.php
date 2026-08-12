<x-admin-layout :title="__('Sent emails')" :breadcrumbs="[['label' => __('Email'), 'url' => route('admin.communications.email.dashboard')], ['label' => __('Sent')]]">
    @include('admin.communications.email.partials.mailbox-chrome')
    @include('admin.communications.email.partials.mail-list', [
        'messages' => $messages,
        'viewMode' => $viewMode,
        'listTitle' => __('Sent'),
        'emptyMessage' => __('No sent emails yet.'),
    ])
</x-admin-layout>
