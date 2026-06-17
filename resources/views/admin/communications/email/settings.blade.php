<x-admin-layout :title="__('Email settings')">
    @include('admin.communications.email.partials.nav')
    <x-admin.page-header :title="__('Email accounts')" :description="__('Company, branch, and department senders — SMTP/provider config stored per account (not sent until provider connected).')" />

    <div class="mb-4 grid gap-4 lg:grid-cols-3">
        <div class="erp-card">
            <h2 class="erp-card-title">{{ __('Delivery diagnostics') }}</h2>
            <dl class="mt-3 space-y-2 text-sm">
                <div class="flex justify-between gap-4">
                    <dt class="text-slate-500">{{ __('Delivery engine') }}</dt>
                    <dd @class(['font-medium', 'text-emerald-700' => $diagnostics['delivery_engine']['active'], 'text-amber-700' => ! $diagnostics['delivery_engine']['active']])>
                        {{ $diagnostics['delivery_engine']['label'] }}
                    </dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-slate-500">{{ __('SMTP') }}</dt>
                    <dd class="font-medium">{{ $diagnostics['smtp']['label'] }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-slate-500">{{ __('Queue') }} ({{ $diagnostics['queue']['name'] }})</dt>
                    <dd @class(['font-medium', 'text-emerald-700' => $diagnostics['queue']['active'], 'text-amber-700' => ! $diagnostics['queue']['active']])>
                        {{ $diagnostics['queue']['label'] }}
                    </dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-slate-500">{{ __('Integration') }}</dt>
                    <dd class="font-medium">{{ $diagnostics['integration']['label'] }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-slate-500">{{ __('Retention policy') }}</dt>
                    <dd class="font-medium">{{ $diagnostics['retention']['label'] ?? '—' }}</dd>
                </div>
            </dl>
        </div>

        <div class="erp-card">
            <h2 class="erp-card-title">{{ __('Queue diagnostics') }}</h2>
            <dl class="mt-3 space-y-2 text-sm">
                <div class="flex justify-between gap-4">
                    <dt class="text-slate-500">{{ __('Queue depth') }}</dt>
                    <dd class="font-medium">{{ $diagnostics['queue']['depth'] ?? 0 }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-slate-500">{{ __('Queued messages') }}</dt>
                    <dd class="font-medium">{{ $diagnostics['queue']['queued_count'] ?? 0 }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-slate-500">{{ __('Stuck sending') }}</dt>
                    <dd @class(['font-medium', 'text-amber-700' => ($diagnostics['queue']['stuck_sending'] ?? 0) > 0])>
                        {{ $diagnostics['queue']['stuck_sending'] ?? 0 }}
                        <span class="text-xs text-slate-500">({{ __('>:min min', ['min' => $diagnostics['queue']['stuck_threshold_minutes'] ?? 15]) }})</span>
                    </dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-slate-500">{{ __('Failed (all time)') }}</dt>
                    <dd class="font-medium">{{ $diagnostics['queue']['failed_count'] ?? 0 }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-slate-500">{{ __('Cancelled (all time)') }}</dt>
                    <dd class="font-medium">{{ $diagnostics['queue']['cancelled_count'] ?? 0 }}</dd>
                </div>
            </dl>
        </div>

        <div class="erp-card">
            <h2 class="erp-card-title">{{ __('Recent failures') }}</h2>
            <ul class="mt-3 space-y-2 text-sm">
                @forelse ($diagnostics['recent_failures'] as $item)
                    <li class="rounded border border-erp-border px-3 py-2">
                        <p class="font-medium">{{ Str::limit($item['subject'], 50) }}</p>
                        <p class="text-xs text-slate-500">{{ $item['recipient'] ?? '—' }} · {{ $item['failed_at'] ?? $item['created_at'] }}</p>
                        @if ($item['failure_reason'])
                            <p class="text-xs text-red-600">{{ Str::limit($item['failure_reason'], 80) }}</p>
                        @endif
                    </li>
                @empty
                    <li class="text-slate-500">{{ __('No recent failures.') }}</li>
                @endforelse
            </ul>
        </div>
    </div>

    <div class="mb-4 erp-card">
        <h2 class="erp-card-title">{{ __('Recent success') }}</h2>
        <ul class="mt-3 space-y-2 text-sm">
            @forelse ($diagnostics['recent_successes'] as $item)
                <li class="rounded border border-erp-border px-3 py-2">
                    <p class="font-medium">{{ Str::limit($item['subject'], 50) }}</p>
                    <p class="text-xs text-slate-500">{{ $item['sender'] ?? '—' }} → {{ $item['recipient'] ?? '—' }} · {{ $item['sent_at'] ?? $item['created_at'] }}</p>
                </li>
            @empty
                <li class="text-slate-500">{{ __('No recent deliveries.') }}</li>
            @endforelse
        </ul>
    </div>

    <div class="erp-card overflow-x-auto">
        <table class="erp-table w-full">
            <thead><tr><th>{{ __('Name') }}</th><th>{{ __('From') }}</th><th>{{ __('Reply-To') }}</th><th>{{ __('Provider') }}</th><th>{{ __('Status') }}</th></tr></thead>
            <tbody>
                @forelse ($accounts as $account)
                    <tr>
                        <td>{{ $account->name }} @if ($account->is_default)<span class="text-xs text-erp-accent">({{ __('Default') }})</span>@endif</td>
                        <td>{{ $account->from_email }}</td>
                        <td>{{ $account->reply_to_email ?? '—' }}</td>
                        <td>{{ $account->provider->label() }}</td>
                        <td>{{ $account->status->label() }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="py-6 text-center text-slate-500">{{ __('No accounts — a default account is created on first send.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-admin-layout>
