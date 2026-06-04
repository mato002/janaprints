<x-admin-layout :title="__('Communication Logs')" :breadcrumbs="[['label' => __('Communications'), 'url' => route('admin.workspaces.communications')], ['label' => __('Communication Logs')]]">
    @include('admin.communications.logs.partials.nav')

    <x-admin.page-header :title="__('Communication ledger')" :description="__('Single source of truth for every ERP communication.')" />

    <div class="mb-4 grid grid-cols-2 gap-2 sm:grid-cols-3 xl:grid-cols-6">
        <x-admin.stat-card :label="__('Total messages')" :value="$analytics['total']" />
        <x-admin.stat-card :label="__('Delivered rate')" :value="$analytics['delivery_rate'].'%'" />
        <x-admin.stat-card :label="__('Failed')" :value="$analytics['failed']" />
        <x-admin.stat-card :label="__('Sent today')" :value="$analytics['sent_today']" />
        <x-admin.stat-card :label="__('Sent this month')" :value="$analytics['sent_month']" />
        <x-admin.stat-card :label="__('Delivered')" :value="$analytics['delivered']" />
    </div>

    <div class="grid gap-4 lg:grid-cols-2">
        <div class="erp-card">
            <h2 class="erp-card-title">{{ __('Channels used') }}</h2>
            <ul class="mt-2 space-y-1 text-sm">
                @forelse ($analytics['by_channel'] as $channel => $count)
                    <li class="flex justify-between"><span>{{ str($channel)->headline() }}</span><span class="font-semibold tabular-nums">{{ $count }}</span></li>
                @empty
                    <li class="text-slate-500">{{ __('No data yet.') }}</li>
                @endforelse
            </ul>
        </div>
        <div class="erp-card">
            <h2 class="erp-card-title">{{ __('Branch activity') }}</h2>
            <ul class="mt-2 space-y-1 text-sm">
                @forelse ($analytics['by_branch'] as $row)
                    <li class="flex justify-between"><span>{{ $row['branch'] }}</span><span class="font-semibold tabular-nums">{{ $row['total'] }}</span></li>
                @empty
                    <li class="text-slate-500">{{ __('No branch data yet.') }}</li>
                @endforelse
            </ul>
        </div>
    </div>

    <div class="erp-card mt-4">
        <h2 class="erp-card-title">{{ __('Recent communications') }}</h2>
        <div class="mt-3">
            <x-admin.communication-timeline :logs="$recent" />
        </div>
    </div>
</x-admin-layout>
