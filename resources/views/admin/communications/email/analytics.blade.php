<x-admin-layout :title="__('Email Operations')" :breadcrumbs="[['label' => __('Communications'), 'url' => route('admin.workspaces.communications')], ['label' => __('Email'), 'url' => route('admin.communications.email.dashboard')], ['label' => __('Operations')]]">
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-erp-primary">{{ __('Email Operations') }}</h1>
            <p class="mt-1 text-sm text-slate-500">{{ __('Infrastructure health, delivery volume, and send analytics for communications administrators.') }}</p>
        </div>
        <a href="{{ route('admin.communications.email.dashboard') }}" data-turbo-frame="erp-main" class="erp-btn erp-btn--secondary">{{ __('← Back to Email') }}</a>
    </div>

    <div class="mb-4 grid grid-cols-2 gap-2 sm:grid-cols-3 xl:grid-cols-6">
        <x-admin.stat-card :label="__('Sent today')" :value="$stats['today']['sent']" />
        <x-admin.stat-card :label="__('Failed today')" :value="$stats['today']['failed']" />
        <x-admin.stat-card :label="__('Queued today')" :value="$stats['today']['queued']" />
        <x-admin.stat-card :label="__('Sent this month')" :value="$stats['month']['sent']" />
        <x-admin.stat-card :label="__('Failed this month')" :value="$stats['month']['failed']" />
        <x-admin.stat-card :label="__('Health')" :value="$stats['health']['label']" />
    </div>

    <div class="mb-4 grid gap-4 lg:grid-cols-3">
        <div class="erp-card">
            <h2 class="erp-card-title">{{ __('Top senders') }}</h2>
            <ul class="mt-3 space-y-2 text-sm">
                @foreach ($stats['top_senders'] as $sender)
                    <li class="flex justify-between gap-4">
                        <span>{{ $sender['label'] }}</span>
                        <span class="font-semibold tabular-nums">{{ $sender['count'] }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
        <div class="erp-card">
            <h2 class="erp-card-title">{{ __('Top recipients') }}</h2>
            <ul class="mt-3 space-y-2 text-sm">
                @foreach ($stats['top_recipients'] as $recipient)
                    <li class="flex justify-between gap-4">
                        <span>{{ $recipient['label'] }}</span>
                        <span class="font-semibold tabular-nums">{{ $recipient['count'] }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
        <div class="erp-card">
            <h2 class="erp-card-title">{{ __('Communication health') }}</h2>
            <dl class="mt-3 space-y-2 text-sm">
                <div class="flex justify-between gap-4">
                    <dt class="text-slate-500">{{ __('Status') }}</dt>
                    <dd @class(['font-medium', 'text-emerald-700' => $stats['health']['level'] === 'healthy', 'text-amber-700' => $stats['health']['level'] === 'warning', 'text-red-700' => $stats['health']['level'] === 'critical'])>{{ $stats['health']['label'] }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-slate-500">{{ __('Failure rate (7d)') }}</dt>
                    <dd class="font-medium">{{ $stats['health']['failure_rate'] }}%</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-slate-500">{{ __('Queue backlog') }}</dt>
                    <dd class="font-medium">{{ $stats['health']['queue_backlog'] }}</dd>
                </div>
            </dl>
        </div>
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
            <h2 class="erp-card-title">{{ __('Top customers (this month)') }}</h2>
            <ul class="mt-2 space-y-2 text-sm">
                @forelse ($stats['top_customers'] as $customer)
                    <li class="flex justify-between gap-4">
                        @if ($customer['url'])
                            <a href="{{ $customer['url'] }}" class="text-erp-accent" data-turbo-frame="erp-main">{{ $customer['customer_name'] }}</a>
                        @else
                            <span>{{ $customer['customer_name'] }}</span>
                        @endif
                        <span class="font-semibold tabular-nums">{{ $customer['email_count'] }}</span>
                    </li>
                @empty
                    <li class="text-slate-500">{{ __('No customer emails this month.') }}</li>
                @endforelse
            </ul>
            <h3 class="mt-4 text-xs font-semibold uppercase text-slate-500">{{ __('Monthly activity') }}</h3>
            <ul class="mt-2 flex flex-wrap gap-2 text-sm">
                @foreach ($stats['monthly_activity'] as $month => $total)
                    <li class="rounded border px-2 py-1">{{ $month }}: <strong>{{ $total }}</strong></li>
                @endforeach
            </ul>
        </div>
    </div>

    @include('admin.communications.email.partials.nav')
</x-admin-layout>
