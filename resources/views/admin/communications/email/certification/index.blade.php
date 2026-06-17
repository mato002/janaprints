<x-admin-layout :title="__('Communications certification')">
    @include('admin.communications.email.partials.nav')
    <x-admin.page-header
        :title="__('Communications Certification Report')"
        :description="__('Production readiness assessment for the email communications platform. Read-only.')"
    />

    @php
        $score = $report['readiness_score'];
        $scoreColor = $score >= 90 ? 'text-emerald-700' : 'text-red-700';
        $verdictColor = ($report['verdict'] ?? '') === 'certified' ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800';
    @endphp

    <div class="mb-6 grid gap-4 lg:grid-cols-3">
        <div class="erp-card lg:col-span-1">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Readiness score') }}</p>
            <p class="mt-2 text-4xl font-bold {{ $scoreColor }}">{{ $score }}/100</p>
            <p class="mt-3 inline-flex rounded-full px-3 py-1 text-sm font-semibold {{ $verdictColor }}">
                {{ $report['verdict_label'] }}
            </p>
            <p class="mt-3 text-sm text-slate-500">
                {{ __(':passed of :total checks passed', ['passed' => $report['checks_passed'], 'total' => $report['checks_total']]) }}
            </p>
        </div>

        <div class="erp-card lg:col-span-2">
            <h2 class="erp-card-title">{{ __('Certification checks') }}</h2>
            <ul class="mt-3 space-y-2">
                @foreach ($report['checks'] as $check)
                    <li class="flex items-start gap-2 text-sm">
                        <span @class([
                            'mt-0.5 inline-block h-2 w-2 rounded-full',
                            'bg-emerald-500' => $check['passed'],
                            'bg-red-500' => ! $check['passed'],
                        ])></span>
                        <span>{{ $check['label'] }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

    <div class="mb-6 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <div class="erp-card">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('SMTP readiness') }}</p>
            <p class="mt-2 text-lg font-semibold">{{ $report['smtp']['label'] }}</p>
            <p class="text-xs text-slate-500">{{ $report['smtp']['ready'] ? __('Ready') : __('Not ready') }}</p>
        </div>
        <div class="erp-card">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Queue readiness') }}</p>
            <p class="mt-2 text-lg font-semibold">{{ $report['queue']['ready'] ? __('Ready') : __('Attention required') }}</p>
            <p class="text-xs text-slate-500">{{ __('Depth: :depth · Stuck: :stuck', ['depth' => $report['queue']['depth'], 'stuck' => $report['queue']['stuck_sending']]) }}</p>
        </div>
        <div class="erp-card">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Failure rate (7d)') }}</p>
            <p class="mt-2 text-lg font-semibold">{{ $report['failure_rate'] }}%</p>
            <p class="text-xs text-slate-500">{{ $report['health']['label'] ?? '' }}</p>
        </div>
        <div class="erp-card">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Retention policy') }}</p>
            <p class="mt-2 text-lg font-semibold">{{ number_format($report['retention']['days']) }} {{ __('days') }}</p>
            <p class="text-xs text-slate-500">{{ __('No automatic deletion') }}</p>
        </div>
    </div>

    <div class="mb-6 grid gap-4 lg:grid-cols-2">
        <div class="erp-card">
            <h2 class="erp-card-title">{{ __('Department readiness') }}</h2>
            <table class="erp-table mt-3 w-full text-sm">
                <thead>
                    <tr>
                        <th>{{ __('Department') }}</th>
                        <th>{{ __('Sent') }}</th>
                        <th>{{ __('Failed') }}</th>
                        <th>{{ __('Queued') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach (['sales' => __('Sales'), 'accounts' => __('Accounts'), 'hr' => __('HR'), 'production' => __('Production')] as $key => $label)
                        <tr>
                            <td>{{ $label }}</td>
                            <td>{{ $report['departments'][$key]['sent'] ?? 0 }}</td>
                            <td>{{ $report['departments'][$key]['failed'] ?? 0 }}</td>
                            <td>{{ $report['departments'][$key]['queued'] ?? 0 }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="erp-card">
            <h2 class="erp-card-title">{{ __('Sender readiness (this month)') }}</h2>
            <ul class="mt-3 space-y-2 text-sm">
                @foreach (['hr' => __('HR'), 'sales' => __('Sales'), 'accounts' => __('Accounts'), 'production' => __('Production'), 'notifications' => __('Notifications')] as $key => $label)
                    <li class="flex justify-between gap-4 rounded border border-erp-border px-3 py-2">
                        <span>{{ $label }}</span>
                        <span class="font-medium">{{ $report['senders'][$key] ?? 0 }} {{ __('sent') }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

    <div class="erp-card">
        <h2 class="erp-card-title">{{ __('Queue diagnostics') }}</h2>
        <dl class="mt-3 grid gap-3 text-sm sm:grid-cols-2 lg:grid-cols-3">
            <div><dt class="text-slate-500">{{ __('Queue') }}</dt><dd class="font-medium">{{ $report['queue']['name'] }} ({{ $report['queue']['driver'] }})</dd></div>
            <div><dt class="text-slate-500">{{ __('Queued messages') }}</dt><dd class="font-medium">{{ $report['queue']['queued'] }}</dd></div>
            <div><dt class="text-slate-500">{{ __('Stuck sending') }}</dt><dd class="font-medium">{{ $report['queue']['stuck_sending'] }}</dd></div>
            <div><dt class="text-slate-500">{{ __('Failed (all time)') }}</dt><dd class="font-medium">{{ $report['queue']['failed'] }}</dd></div>
            <div><dt class="text-slate-500">{{ __('Cancelled (all time)') }}</dt><dd class="font-medium">{{ $report['queue']['cancelled'] }}</dd></div>
            <div><dt class="text-slate-500">{{ __('Attachments') }}</dt><dd class="font-medium">{{ $report['attachments']['healthy'] ? __('Healthy') : __('Issues detected') }}</dd></div>
        </dl>
    </div>
</x-admin-layout>
