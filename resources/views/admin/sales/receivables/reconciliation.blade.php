<x-admin-layout :title="__('AR reconciliation')">
    <x-admin.page-header :title="__('Accounts receivable reconciliation')" :description="__('Accounting truth verification as of :date', ['date' => $report['as_of_date']])">
        <x-admin.status-badge :variant="$report['is_resolved'] ? 'success' : 'warning'">
            {{ $report['is_resolved'] ? __('Resolved') : __('Unresolved') }}
        </x-admin.status-badge>
    </x-admin.page-header>

    <form method="GET" class="mb-6 flex flex-wrap items-end gap-3">
        <div>
            <label class="erp-label">{{ __('As of date') }}</label>
            <input type="date" name="as_of_date" value="{{ $report['as_of_date'] }}" class="erp-input">
        </div>
        <button class="erp-btn-secondary">{{ __('Refresh') }}</button>
    </form>

    <x-admin.card class="mb-4">
        <h3 class="mb-3 font-medium">{{ __('Reconciliation checks') }}</h3>
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-[11px] uppercase text-slate-400">
                    <th>{{ __('Check') }}</th>
                    <th>{{ __('Expected') }}</th>
                    <th>{{ __('Actual') }}</th>
                    <th>{{ __('Difference') }}</th>
                    <th>{{ __('Status') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($report['checks'] as $check)
                    <tr class="border-t border-erp-border">
                        <td class="py-2">{{ $check['label'] }}</td>
                        <td class="py-2 font-mono">{{ number_format($check['expected'], 2) }}</td>
                        <td class="py-2 font-mono">{{ number_format($check['actual'], 2) }}</td>
                        <td class="py-2 font-mono">{{ number_format($check['difference'], 2) }}</td>
                        <td class="py-2">
                            <x-admin.status-badge :variant="$check['status'] === 'matched' ? 'success' : 'warning'">
                                {{ $check['status_label'] }}
                            </x-admin.status-badge>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </x-admin.card>

    <x-admin.card>
        <h3 class="mb-3 font-medium">{{ __('Exceptions') }}</h3>
        @if ($report['exceptions'] === [])
            <p class="text-sm text-slate-500">{{ __('No exceptions detected.') }}</p>
        @else
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-[11px] uppercase text-slate-400">
                        <th>{{ __('Type') }}</th>
                        <th>{{ __('Reference') }}</th>
                        <th>{{ __('Message') }}</th>
                        <th>{{ __('Amount') }}</th>
                        <th>{{ __('Severity') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($report['exceptions'] as $exception)
                        <tr class="border-t border-erp-border">
                            <td class="py-2">{{ $exception['type_label'] }}</td>
                            <td class="py-2 font-mono">{{ $exception['reference'] ?? '—' }}</td>
                            <td class="py-2">{{ $exception['message'] }}</td>
                            <td class="py-2 font-mono">{{ number_format($exception['amount'], 2) }}</td>
                            <td class="py-2">
                                <x-admin.status-badge :variant="$exception['severity'] === 'critical' ? 'warning' : 'neutral'">
                                    {{ ucfirst($exception['severity']) }}
                                </x-admin.status-badge>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </x-admin.card>
</x-admin-layout>
