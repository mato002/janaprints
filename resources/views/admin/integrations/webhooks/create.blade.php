<x-admin-layout :title="__('Add Webhook')">
    <x-admin.page-header :title="__('Add webhook')" />
    <form method="POST" action="{{ route('admin.integrations.webhooks.store') }}" class="max-w-2xl space-y-4">
        @csrf
        @include('admin.integrations.webhooks.form', ['events' => $events, 'statuses' => $statuses])
        <button type="submit" class="erp-btn-primary">{{ __('Save webhook') }}</button>
    </form>
</x-admin-layout>
