<x-admin-layout :title="__('SMS Center')" :breadcrumbs="[['label' => __('Communications'), 'url' => route('admin.workspaces.communications')], ['label' => __('SMS Center')]]">
    @include('admin.communications.sms.partials.nav')

    <x-admin.page-header
        :title="__('SMS Center')"
        :description="__('Credits, delivery health, and what needs attention — campaigns and queue live on their own pages.')"
    >
        <x-slot:actions>
            @can('create', App\Models\Communications\SmsCampaign::class)
                <x-admin.crm-btn variant="primary" :href="route('admin.communications.sms.campaigns.create')" data-turbo-frame="erp-main">{{ __('Send SMS') }}</x-admin.crm-btn>
            @endcan
        </x-slot:actions>
    </x-admin.page-header>

    @if (session('status'))
        <x-admin.alert variant="success" class="mb-4">{{ session('status') }}</x-admin.alert>
    @endif

    <div class="mb-4 grid gap-4 xl:grid-cols-12">
        {{-- Credits + top up --}}
        <div id="sms-topup" class="erp-card xl:col-span-5 {{ $stats['low_credit'] ? 'ring-1 ring-amber-300' : '' }}">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Credits remaining') }}</p>
                    <p class="mt-1 text-3xl font-semibold tabular-nums text-erp-primary">{{ number_format($stats['credits_remaining'], 0) }}</p>
                    <p class="mt-1 text-xs text-slate-500">
                        {{ __('≈ :count segments left', ['count' => number_format($stats['approx_messages_left'])]) }}
                        · {{ __('Cost') }} {{ number_format($stats['cost_per_sms'], 2) }} {{ $stats['credit_currency'] ?? 'KES' }}/{{ __('segment') }}
                        · {{ ($stats['credit_source'] ?? 'local') === 'crm' ? __('CRM wallet') : __('Local ledger') }}
                    </p>
                </div>
                <a
                    href="{{ route('admin.communications.sms.credits.index') }}"
                    data-turbo-frame="erp-main"
                    class="text-xs font-medium text-erp-accent hover:underline"
                >{{ __('Full ledger') }}</a>
            </div>

            @can('audit', App\Models\Communications\SmsCampaign::class)
                <div class="mt-4 flex flex-wrap items-center gap-2 border-t border-erp-border pt-4">
                    <button type="button" class="erp-btn-primary" onclick="window.dispatchEvent(new CustomEvent('open-sms-crm-topup'))">{{ __('Top up with M-Pesa') }}</button>
                    <p class="text-xs text-slate-500">{{ __('Pays Pradytec CRM — credits appear after you approve the STK prompt.') }}</p>
                </div>
            @else
                <p class="mt-4 border-t border-erp-border pt-4 text-xs text-slate-500">
                    {{ __('Ask an admin with SMS audit permission to top up credits.') }}
                </p>
            @endcan
        </div>

        {{-- Health snapshot --}}
        <div class="grid gap-4 sm:grid-cols-2 xl:col-span-7 xl:grid-cols-2">
            <div class="erp-card">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Sent today') }}</p>
                <p class="mt-1 text-2xl font-semibold tabular-nums text-erp-primary">{{ number_format($stats['sent_today']) }}</p>
                <p class="mt-1 text-xs text-slate-500">{{ __('This month') }}: {{ number_format($stats['sent_month']) }}</p>
            </div>
            <div class="erp-card">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Delivery success') }}</p>
                <p class="mt-1 text-2xl font-semibold tabular-nums {{ ($stats['delivery_success_rate'] ?? 100) >= 90 ? 'text-emerald-700' : 'text-amber-700' }}">
                    {{ $stats['delivery_success_rate'] === null ? '—' : $stats['delivery_success_rate'].'%' }}
                </p>
                <p class="mt-1 text-xs text-slate-500">
                    {{ __('Queue') }}: {{ number_format($stats['queued_messages'] + $stats['processing_messages']) }}
                    · {{ __('Failed') }}: {{ number_format($stats['failed_messages']) }}
                </p>
            </div>
            <div class="erp-card sm:col-span-2">
                <div class="flex items-center justify-between gap-2">
                    <h2 class="erp-card-title">{{ __('Needs attention') }}</h2>
                    @if ($stats['attention'] === [])
                        <span class="text-xs font-medium text-emerald-700">{{ __('All clear') }}</span>
                    @endif
                </div>
                @if ($stats['attention'] === [])
                    <p class="mt-2 text-sm text-slate-500">{{ __('No credit, queue, or provider issues right now.') }}</p>
                @else
                    <ul class="mt-3 space-y-2">
                        @foreach ($stats['attention'] as $item)
                            <li @class([
                                'rounded-lg border px-3 py-2',
                                'border-red-200 bg-red-50' => ($item['tone'] ?? '') === 'danger',
                                'border-amber-200 bg-amber-50' => ($item['tone'] ?? '') !== 'danger',
                            ])>
                                <div class="flex flex-wrap items-start justify-between gap-2">
                                    <div>
                                        <p class="text-sm font-semibold text-erp-primary">{{ $item['title'] }}</p>
                                        <p class="mt-0.5 text-xs text-slate-600">{{ $item['detail'] }}</p>
                                    </div>
                                    @if (! empty($item['action_url']))
                                        <a href="{{ $item['action_url'] }}" data-turbo-frame="erp-main" class="shrink-0 text-xs font-semibold text-erp-accent hover:underline">{{ $item['action_label'] }}</a>
                                    @elseif (($item['action_anchor'] ?? null) === 'sms-topup')
                                        <button type="button" class="shrink-0 text-xs font-semibold text-erp-accent hover:underline" onclick="window.dispatchEvent(new CustomEvent('open-sms-crm-topup'))">{{ $item['action_label'] }}</button>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>

    <div class="mb-4 grid gap-4 lg:grid-cols-3">
        <a href="{{ route('admin.communications.sms.campaigns.index') }}" data-turbo-frame="erp-main" class="erp-card transition hover:border-erp-accent/40">
            <p class="text-sm font-semibold text-erp-primary">{{ __('Campaigns') }}</p>
            <p class="mt-1 text-xs text-slate-500">{{ __('Create, schedule, and track bulk sends.') }}</p>
        </a>
        <a href="{{ route('admin.communications.sms.queues.index') }}" data-turbo-frame="erp-main" class="erp-card transition hover:border-erp-accent/40">
            <p class="text-sm font-semibold text-erp-primary">{{ __('Message queue') }}</p>
            <p class="mt-1 text-xs text-slate-500">{{ __('Queued, processing, sent, and failed messages.') }}</p>
        </a>
        @can('audit', App\Models\Communications\SmsCampaign::class)
            <a href="{{ route('admin.communications.sms.provider-logs.index') }}" data-turbo-frame="erp-main" class="erp-card transition hover:border-erp-accent/40">
                <p class="text-sm font-semibold text-erp-primary">{{ __('Provider logs') }}</p>
                <p class="mt-1 text-xs text-slate-500">{{ __('Request/response audit for the SMS gateway.') }}</p>
            </a>
        @else
            <a href="{{ route('admin.communications.sms.credits.index') }}" data-turbo-frame="erp-main" class="erp-card transition hover:border-erp-accent/40">
                <p class="text-sm font-semibold text-erp-primary">{{ __('Credit ledger') }}</p>
                <p class="mt-1 text-xs text-slate-500">{{ __('Purchases, usage, and running balance.') }}</p>
            </a>
        @endcan
    </div>

    <div class="grid gap-4 lg:grid-cols-2">
        <div class="erp-card">
            <h2 class="erp-card-title">{{ __('Daily usage (14 days)') }}</h2>
            <ul class="mt-2 max-h-48 space-y-1 overflow-y-auto text-xs">
                @forelse ($stats['daily_usage'] as $day => $total)
                    <li class="flex justify-between gap-3">
                        <span class="text-slate-500">{{ $day }}</span>
                        <span class="font-semibold tabular-nums text-erp-primary">{{ $total }}</span>
                    </li>
                @empty
                    <li class="text-slate-500">{{ __('No sends yet.') }}</li>
                @endforelse
            </ul>
        </div>

        <div class="erp-card">
            <div class="flex items-center justify-between gap-2">
                <h2 class="erp-card-title">{{ __('Recent credit activity') }}</h2>
                <a href="{{ route('admin.communications.sms.credits.index') }}" data-turbo-frame="erp-main" class="text-xs font-medium text-erp-accent hover:underline">{{ __('Ledger') }}</a>
            </div>
            <ul class="mt-2 max-h-48 space-y-2 overflow-y-auto text-xs">
                @forelse ($stats['recent_transactions'] as $tx)
                    <li class="flex items-start justify-between gap-3 border-b border-erp-border/70 pb-2 last:border-0 last:pb-0">
                        <div>
                            <p class="font-medium text-erp-primary">{{ $tx->transaction_type->label() }}</p>
                            <p class="text-slate-500">
                                {{ $tx->created_at?->format('d M Y H:i') }}
                                @if ($tx->campaign)
                                    · {{ $tx->campaign->name }}
                                @endif
                            </p>
                        </div>
                        <span @class([
                            'font-semibold tabular-nums',
                            'text-red-700' => $tx->amount < 0,
                            'text-emerald-700' => $tx->amount >= 0,
                        ])>{{ number_format($tx->amount, 0) }}</span>
                    </li>
                @empty
                    <li class="text-slate-500">{{ __('No credit movements yet. Top up above to get started.') }}</li>
                @endforelse
            </ul>
        </div>
    </div>

    @include('admin.communications.sms.partials.topup-modal', ['topupConfig' => $topupConfig ?? []])
</x-admin-layout>
