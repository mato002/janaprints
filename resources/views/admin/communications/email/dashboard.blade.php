<x-admin-layout :title="__('Email')" :breadcrumbs="[['label' => __('Communications'), 'url' => route('admin.workspaces.communications')], ['label' => __('Email')]]">
    @include('admin.communications.email.partials.mailbox-chrome')

    @include('admin.communications.email.partials.mail-list', [
        'messages' => $messages,
        'viewMode' => 'sent',
        'listTitle' => __('Sent'),
        'emptyMessage' => __('No sent emails yet. Compose one to get started.'),
    ])

    @canany(['communications.email.manage', 'communications.email.audit'])
        <p class="mt-6 text-center text-xs text-slate-400">
            <a href="{{ route('admin.communications.email.analytics') }}" data-turbo-frame="erp-main" class="text-erp-accent hover:underline">{{ __('Email Operations') }}</a>
            — {{ __('delivery health, volume, and infrastructure metrics') }}
        </p>
    @endcanany
</x-admin-layout>
