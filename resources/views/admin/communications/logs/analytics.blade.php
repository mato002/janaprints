<x-admin-layout :title="__('Communication analytics')" :breadcrumbs="[['label' => __('Communication Logs'), 'url' => route('admin.communications.logs.dashboard')], ['label' => __('Analytics')]]">
    @include('admin.communications.logs.partials.nav')
    <x-admin.page-header :title="__('Communication analytics')" :description="__('Executive view of delivery performance and channel usage.')" />

    <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
        <x-admin.stat-card :label="__('Messages sent')" :value="$analytics['total']" />
        <x-admin.stat-card :label="__('Delivery rate')" :value="$analytics['delivery_rate'].'%'" />
        <x-admin.stat-card :label="__('Failures')" :value="$analytics['failed']" />
        <x-admin.stat-card :label="__('This month')" :value="$analytics['sent_month']" />
    </div>

    <div class="mt-4 grid gap-4 lg:grid-cols-2">
        <div class="erp-card">
            <h2 class="erp-card-title">{{ __('Channels used') }}</h2>
            <ul class="mt-2 space-y-1 text-sm">
                @foreach ($analytics['by_channel'] as $channel => $count)
                    <li class="flex justify-between"><span>{{ str($channel)->headline() }}</span><span class="font-semibold">{{ $count }}</span></li>
                @endforeach
            </ul>
        </div>
        <div class="erp-card">
            <h2 class="erp-card-title">{{ __('Branch activity') }}</h2>
            <ul class="mt-2 space-y-1 text-sm">
                @foreach ($analytics['by_branch'] as $row)
                    <li class="flex justify-between"><span>{{ $row['branch'] }}</span><span class="font-semibold">{{ $row['total'] }}</span></li>
                @endforeach
            </ul>
        </div>
    </div>
</x-admin-layout>
