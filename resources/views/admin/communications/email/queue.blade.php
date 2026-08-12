<x-admin-layout :title="__('Queued emails')" :breadcrumbs="[['label' => __('Email'), 'url' => route('admin.communications.email.dashboard')], ['label' => __('Queued')]]">
    @include('admin.communications.email.partials.mailbox-chrome')
    @include('admin.communications.email.partials.mail-list', [
        'messages' => $messages,
        'viewMode' => $viewMode,
        'listTitle' => __('Queued'),
        'emptyMessage' => __('Nothing waiting in the queue.'),
    ])
</x-admin-layout>
