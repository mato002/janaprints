<x-admin-layout :title="$campaign->name" :breadcrumbs="[['label' => __('SMS Campaigns'), 'url' => route('admin.communications.sms.campaigns.index')], ['label' => $campaign->name]]">
    @include('admin.communications.sms.partials.nav')

    <x-admin.page-header :title="$campaign->name" :description="$campaign->campaign_code">
        <x-slot:actions>
            <div class="flex flex-wrap gap-2">
                @if ($campaign->status->canEdit())
                    @can('update', $campaign)
                        <a href="{{ route('admin.communications.sms.campaigns.edit', $campaign) }}" class="erp-btn erp-btn--ghost" data-turbo-frame="erp-main">{{ __('Edit') }}</a>
                    @endcan
                @endif
                @if ($campaign->status->canQueue())
                    @can('approve', $campaign)
                        @unless ($campaign->approved_at)
                            <form method="POST" action="{{ route('admin.communications.sms.campaigns.approve', $campaign) }}">@csrf
                                <button class="erp-btn erp-btn--secondary">{{ __('Approve') }}</button>
                            </form>
                        @endunless
                    @endcan
                    @can('send', $campaign)
                        <form method="POST" action="{{ route('admin.communications.sms.campaigns.send', $campaign) }}" onsubmit="return confirm(@js(__('Queue this campaign for background sending?')))">@csrf
                            <button class="erp-btn erp-btn--primary">{{ $campaign->send_mode === \App\Enums\SmsCampaignSendMode::Scheduled ? __('Schedule send') : __('Send now') }}</button>
                        </form>
                    @endcan
                @endif
                @if ($campaign->status->canCancel())
                    <form method="POST" action="{{ route('admin.communications.sms.campaigns.cancel', $campaign) }}">@csrf
                        <button class="erp-btn erp-btn--ghost text-red-700">{{ __('Cancel') }}</button>
                    </form>
                @endif
            </div>
        </x-slot:actions>
    </x-admin.page-header>

    <div class="mb-4 grid grid-cols-2 gap-2 sm:grid-cols-4">
        <x-admin.stat-card :label="__('Status')" :value="$campaign->status->label()" />
        <x-admin.stat-card :label="__('Recipients')" :value="$campaign->total_recipients" />
        <x-admin.stat-card :label="__('Est. segments')" :value="$campaign->estimated_segments" />
        <x-admin.stat-card :label="__('Est. cost')" :value="number_format($campaign->estimated_cost, 2)" />
    </div>

    <div class="grid gap-4 lg:grid-cols-2">
        <div class="erp-card">
            <h2 class="erp-card-title">{{ __('Message') }}</h2>
            <pre class="mt-2 whitespace-pre-wrap rounded border border-erp-border bg-slate-50 p-3 text-sm">{{ $campaign->message_template }}</pre>
            @if ($campaign->template)
                <p class="mt-2 text-xs text-slate-500">{{ __('Template') }}: {{ $campaign->template->name }}</p>
            @endif
        </div>
        <div class="erp-card">
            <h2 class="erp-card-title">{{ __('Campaign audit') }}</h2>
            <dl class="mt-2 space-y-1 text-sm">
                <div class="flex justify-between"><dt class="text-slate-500">{{ __('Created by') }}</dt><dd>{{ $campaign->creator?->name }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">{{ __('Approved by') }}</dt><dd>{{ $campaign->approver?->name ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">{{ __('Sent by') }}</dt><dd>{{ $campaign->sender?->name ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">{{ __('Branch') }}</dt><dd>{{ $campaign->branch?->name ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">{{ __('Department') }}</dt><dd>{{ $campaign->department?->name ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">{{ __('Queued') }}</dt><dd>{{ $campaign->queued_at?->format('d M Y H:i') ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">{{ __('Completed') }}</dt><dd>{{ $campaign->completed_at?->format('d M Y H:i') ?? '—' }}</dd></div>
            </dl>
        </div>
    </div>

    <div class="erp-card mt-4">
        <h2 class="erp-card-title">{{ __('Recipients') }} ({{ $campaign->recipients->count() }})</h2>
        <div class="mt-2 max-h-64 overflow-y-auto">
            <table class="erp-table w-full text-sm">
                <thead><tr><th>{{ __('Name') }}</th><th>{{ __('Phone') }}</th><th>{{ __('Source') }}</th></tr></thead>
                <tbody>
                    @foreach ($campaign->recipients->take(100) as $recipient)
                        <tr>
                            <td>{{ $recipient->display_name }}</td>
                            <td class="font-mono text-xs">{{ $recipient->phone_number }}</td>
                            <td>{{ $recipient->source_type }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-admin-layout>
