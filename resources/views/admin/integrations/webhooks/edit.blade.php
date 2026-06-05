<x-admin-layout :title="__('Edit Webhook')">
    <x-admin.page-header :title="__('Edit webhook')" :description="$webhook->name" />
    <form method="POST" action="{{ route('admin.integrations.webhooks.update', $webhook) }}" class="max-w-2xl space-y-4">
        @csrf @method('PUT')
        @include('admin.integrations.webhooks.form', ['webhook' => $webhook, 'events' => $events, 'statuses' => $statuses])
        <button type="submit" class="erp-btn-primary">{{ __('Save webhook') }}</button>
    </form>
</x-admin-layout>
