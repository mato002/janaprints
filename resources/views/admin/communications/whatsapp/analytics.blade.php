<x-admin-layout :title="__('WhatsApp Analytics')" :breadcrumbs="[['label' => __('WhatsApp'), 'url' => route('admin.communications.whatsapp.inbox')], ['label' => __('Analytics')]]">
    @include('admin.communications.whatsapp.partials.nav')
    <x-admin.page-header :title="__('WhatsApp analytics')" />
    <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
        <x-admin.stat-card :label="__('Total messages')" :value="$stats['total_messages']" />
        <x-admin.stat-card :label="__('Delivery rate')" :value="$stats['delivery_rate'].'%'" />
        <x-admin.stat-card :label="__('Open conversations')" :value="$stats['open_conversations']" />
        <x-admin.stat-card :label="__('Failed')" :value="$stats['failed_messages']" />
    </div>
</x-admin-layout>
