<x-admin-layout :title="__('Email analytics')">
    @include('admin.communications.email.partials.nav')
    <x-admin.page-header :title="__('Email analytics')" :description="__('Operational delivery metrics only — no open or click tracking.')" />
    <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 xl:grid-cols-6">
        <x-admin.stat-card :label="__('Sent today')" :value="$stats['today']['sent']" />
        <x-admin.stat-card :label="__('Failed today')" :value="$stats['today']['failed']" />
        <x-admin.stat-card :label="__('Queued today')" :value="$stats['today']['queued']" />
        <x-admin.stat-card :label="__('Sent this month')" :value="$stats['month']['sent']" />
        <x-admin.stat-card :label="__('Failed this month')" :value="$stats['month']['failed']" />
        <x-admin.stat-card :label="__('Health')" :value="$stats['health']['label']" />
    </div>
</x-admin-layout>
