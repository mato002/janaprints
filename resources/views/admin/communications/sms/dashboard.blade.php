<x-admin-layout :title="__('SMS Center')" :breadcrumbs="[['label' => __('Communications'), 'url' => route('admin.workspaces.communications')], ['label' => __('SMS Center')]]">
    @include('admin.communications.sms.partials.nav')

    <x-admin.page-header :title="__('SMS Center')" :description="__('Bulk SMS dashboard — credits, delivery, and campaign activity.')" />

    <div class="mb-4 grid grid-cols-2 gap-2 sm:grid-cols-3 xl:grid-cols-6">
        <x-admin.stat-card :label="__('Credits remaining')" :value="number_format($stats['credits_remaining'], 0)" />
        <x-admin.stat-card :label="__('Sent today')" :value="$stats['sent_today']" />
        <x-admin.stat-card :label="__('Sent this month')" :value="$stats['sent_month']" />
        <x-admin.stat-card :label="__('Failed messages')" :value="$stats['failed_messages']" />
        <x-admin.stat-card :label="__('Queued messages')" :value="$stats['queued_messages']" />
        <x-admin.stat-card :label="__('Campaigns this month')" :value="$stats['campaigns_month']" />
    </div>

    <div class="grid gap-4 lg:grid-cols-2">
        <div class="erp-card">
            <h2 class="erp-card-title">{{ __('Delivery success rate') }}</h2>
            <p class="mt-2 text-3xl font-semibold tabular-nums text-emerald-700">{{ $stats['delivery_success_rate'] }}%</p>
            <p class="mt-1 text-xs text-slate-500">{{ __('Cost per SMS segment') }}: {{ number_format($stats['cost_per_sms'], 2) }}</p>
        </div>
        <div class="erp-card">
            <h2 class="erp-card-title">{{ __('Daily usage (14 days)') }}</h2>
            <ul class="mt-2 max-h-40 space-y-1 overflow-y-auto text-xs">
                @forelse ($stats['daily_usage'] as $day => $total)
                    <li class="flex justify-between"><span>{{ $day }}</span><span class="font-semibold tabular-nums">{{ $total }}</span></li>
                @empty
                    <li class="text-slate-500">{{ __('No sends yet.') }}</li>
                @endforelse
            </ul>
        </div>
        <div class="erp-card lg:col-span-2">
            <h2 class="erp-card-title">{{ __('Monthly usage') }}</h2>
            <ul class="mt-2 flex flex-wrap gap-3 text-sm">
                @forelse ($stats['monthly_usage'] as $month => $total)
                    <li class="rounded-lg border border-erp-border px-3 py-2"><span class="text-slate-500">{{ $month }}</span> <span class="font-semibold">{{ $total }}</span></li>
                @empty
                    <li class="text-slate-500">{{ __('No monthly data yet.') }}</li>
                @endforelse
            </ul>
        </div>
    </div>

    <div class="erp-card mt-4">
        <div class="flex items-center justify-between">
            <h2 class="erp-card-title">{{ __('Recent campaigns') }}</h2>
            @can('create', App\Models\Communications\SmsCampaign::class)
                <x-admin.crm-btn variant="primary" size="sm" :href="route('admin.communications.sms.campaigns.create')" data-turbo-frame="erp-main">{{ __('New campaign') }}</x-admin.crm-btn>
            @endcan
        </div>
        <div class="mt-3 overflow-x-auto">
            <table class="erp-table w-full">
                <thead>
                    <tr>
                        <th>{{ __('Campaign') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Sent') }}</th>
                        <th>{{ __('Failed') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($stats['recent_campaigns'] as $campaign)
                        <tr>
                            <td><a href="{{ route('admin.communications.sms.campaigns.show', $campaign) }}" class="font-medium text-erp-accent hover:underline" data-turbo-frame="erp-main">{{ $campaign->name }}</a></td>
                            <td>{{ $campaign->status->label() }}</td>
                            <td class="tabular-nums">{{ $campaign->sent_count }}</td>
                            <td class="tabular-nums">{{ $campaign->failed_count }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-slate-500 py-4">{{ __('No campaigns yet.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-admin-layout>
