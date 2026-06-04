<x-admin-layout :title="__('Email Center')" :breadcrumbs="[['label' => __('Communications'), 'url' => route('admin.workspaces.communications')], ['label' => __('Email Center')]]">
    @include('admin.communications.email.partials.nav')
    <x-admin.page-header :title="__('Email Center')" :description="__('Enterprise email — templates, campaigns, delivery tracking, and COM-4 logs.')" />
    <div class="mb-4 grid grid-cols-2 gap-2 sm:grid-cols-3 xl:grid-cols-6">
        <x-admin.stat-card :label="__('Sent today')" :value="$stats['sent_today']" />
        <x-admin.stat-card :label="__('Sent this month')" :value="$stats['sent_month']" />
        <x-admin.stat-card :label="__('Open rate')" :value="$stats['open_rate'].'%'" />
        <x-admin.stat-card :label="__('Click rate')" :value="$stats['click_rate'].'%'" />
        <x-admin.stat-card :label="__('Bounce rate')" :value="$stats['bounce_rate'].'%'" />
        <x-admin.stat-card :label="__('Failed')" :value="$stats['failed_count']" />
    </div>
    <div class="grid gap-4 lg:grid-cols-2">
        <div class="erp-card">
            <h2 class="erp-card-title">{{ __('Daily activity (14 days)') }}</h2>
            <ul class="mt-2 space-y-1 text-sm">
                @forelse ($stats['daily_activity'] as $day => $total)
                    <li class="flex justify-between"><span>{{ $day }}</span><span class="font-semibold">{{ $total }}</span></li>
                @empty
                    <li class="text-slate-500">{{ __('No sends yet.') }}</li>
                @endforelse
            </ul>
        </div>
        <div class="erp-card">
            <h2 class="erp-card-title">{{ __('Delivery success rate') }}</h2>
            <p class="mt-2 text-3xl font-semibold text-emerald-700">{{ $stats['delivery_success_rate'] }}%</p>
            <h3 class="mt-4 text-xs font-semibold uppercase text-slate-500">{{ __('Monthly activity') }}</h3>
            <ul class="mt-2 flex flex-wrap gap-2 text-sm">
                @foreach ($stats['monthly_activity'] as $month => $total)
                    <li class="rounded border px-2 py-1">{{ $month }}: <strong>{{ $total }}</strong></li>
                @endforeach
            </ul>
        </div>
    </div>
</x-admin-layout>
