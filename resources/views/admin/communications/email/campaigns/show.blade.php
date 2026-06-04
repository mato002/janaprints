<x-admin-layout :title="$campaign->name">
    @include('admin.communications.email.partials.nav')
    <x-admin.page-header :title="$campaign->name" :description="$campaign->campaign_code">
        @can('send', $campaign)
            <x-slot:actions>
                <form method="POST" action="{{ route('admin.communications.email.campaigns.send', $campaign) }}">@csrf
                    <button type="submit" class="erp-btn erp-btn--primary erp-btn--sm">{{ __('Send campaign') }}</button>
                </form>
            </x-slot:actions>
        @endcan
    </x-admin.page-header>
    <div class="grid gap-4 lg:grid-cols-3">
        <div class="erp-card lg:col-span-2">
            <h2 class="erp-card-title">{{ __('Message') }}</h2>
            <p class="font-medium">{{ $campaign->subject }}</p>
            <pre class="mt-2 text-sm whitespace-pre-wrap">{{ $campaign->body }}</pre>
        </div>
        <div class="erp-card text-sm space-y-2">
            <div class="flex justify-between"><span class="text-slate-500">{{ __('Status') }}</span><span>{{ $campaign->status->label() }}</span></div>
            <div class="flex justify-between"><span class="text-slate-500">{{ __('Recipients') }}</span><span>{{ $campaign->total_recipients }}</span></div>
            <div class="flex justify-between"><span class="text-slate-500">{{ __('Opened') }}</span><span>{{ $campaign->opened_count }}</span></div>
            <div class="flex justify-between"><span class="text-slate-500">{{ __('Bounced') }}</span><span>{{ $campaign->bounced_count }}</span></div>
        </div>
    </div>
</x-admin-layout>
