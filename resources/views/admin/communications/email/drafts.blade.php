<x-admin-layout :title="__('Drafts')" :breadcrumbs="[['label' => __('Email'), 'url' => route('admin.communications.email.dashboard')], ['label' => __('Drafts')]]">
    @include('admin.communications.email.partials.mailbox-chrome')
    @include('admin.communications.email.partials.mail-list', [
        'messages' => $messages,
        'viewMode' => $viewMode,
        'listTitle' => __('Drafts'),
        'emptyMessage' => __('No drafts saved.'),
    ])
</x-admin-layout>
