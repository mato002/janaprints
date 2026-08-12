<x-admin-layout :title="__('Needs attention')" :breadcrumbs="[['label' => __('Email'), 'url' => route('admin.communications.email.dashboard')], ['label' => __('Needs attention')]]">
    @include('admin.communications.email.partials.mailbox-chrome')
    @include('admin.communications.email.partials.mail-list', [
        'messages' => $messages,
        'viewMode' => $viewMode,
        'listTitle' => __('Needs attention'),
        'emptyMessage' => __('No failed or bounced emails. Delivery looks clear.'),
    ])
</x-admin-layout>
