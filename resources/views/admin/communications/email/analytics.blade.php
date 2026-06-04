<x-admin-layout :title="__('Email analytics')">
    @include('admin.communications.email.partials.nav')
    <x-admin.page-header :title="__('Email analytics')" />
    <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
        <x-admin.stat-card :label="__('Open rate trend')" :value="$stats['open_rate'].'%'" />
        <x-admin.stat-card :label="__('Click rate')" :value="$stats['click_rate'].'%'" />
        <x-admin.stat-card :label="__('Bounce rate')" :value="$stats['bounce_rate'].'%'" />
        <x-admin.stat-card :label="__('Delivery success')" :value="$stats['delivery_success_rate'].'%'" />
    </div>
</x-admin-layout>
