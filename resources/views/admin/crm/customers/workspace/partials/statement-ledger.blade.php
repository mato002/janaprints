@php
    $statement = $statement ?? [];
    $lines = $statement['lines'] ?? [];
@endphp

<form method="GET" action="{{ route('admin.crm.customers.show', $customer) }}" class="mb-4 flex flex-wrap items-end gap-3">
    <input type="hidden" name="tab" value="financial">
    <input type="hidden" name="financial_section" value="statement">
    <div>
        <label class="text-xs text-slate-600" for="statement_from">{{ __('From') }}</label>
        <input type="date" id="statement_from" name="statement_from" class="erp-input mt-1" value="{{ $from }}">
    </div>
    <div>
        <label class="text-xs text-slate-600" for="statement_to">{{ __('To') }}</label>
        <input type="date" id="statement_to" name="statement_to" class="erp-input mt-1" value="{{ $to }}">
    </div>
    <x-secondary-button type="submit">{{ __('Generate') }}</x-secondary-button>
    @can('statements.export')
        <a href="{{ route('admin.crm.customers.statement', ['customer' => $customer, 'statement_from' => $from, 'statement_to' => $to, 'export' => 'json']) }}" class="erp-btn-secondary">{{ __('Export JSON') }}</a>
    @endcan
</form>

<x-admin.card>
    <div class="mb-4 flex flex-wrap justify-between gap-2 text-sm">
        <div>
            <p class="font-semibold">{{ $statement['customer']['name'] ?? '' }}</p>
            <p class="text-slate-500">{{ $from }} — {{ $to }}</p>
        </div>
        <div class="text-right">
            <p>{{ __('Opening') }}: <span class="font-mono">{{ number_format($statement['opening_balance'] ?? 0, 2) }}</span></p>
            <p>{{ __('Closing') }}: <span class="font-mono font-semibold">{{ number_format($statement['closing_balance'] ?? 0, 2) }}</span></p>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full border-collapse text-sm">
            <thead>
                <tr class="border-b border-slate-200 bg-slate-50 text-left">
                    <th class="px-3 py-2">{{ __('Date') }}</th>
                    <th class="px-3 py-2">{{ __('Type') }}</th>
                    <th class="px-3 py-2">{{ __('Reference') }}</th>
                    <th class="px-3 py-2">{{ __('Description') }}</th>
                    <th class="px-3 py-2 text-right">{{ __('Debit') }}</th>
                    <th class="px-3 py-2 text-right">{{ __('Credit') }}</th>
                    <th class="px-3 py-2 text-right">{{ __('Balance') }}</th>
                </tr>
            </thead>
            <tbody>
                <tr class="border-b border-slate-100 font-medium">
                    <td class="px-3 py-2" colspan="6">{{ __('Opening balance') }}</td>
                    <td class="px-3 py-2 text-right font-mono">{{ number_format($statement['opening_balance'] ?? 0, 2) }}</td>
                </tr>
                @forelse ($lines as $line)
                    <tr class="border-b border-slate-50">
                        <td class="px-3 py-2 whitespace-nowrap">{{ $line['date'] }}</td>
                        <td class="px-3 py-2 capitalize">{{ str_replace('_', ' ', $line['type']) }}</td>
                        <td class="px-3 py-2 font-mono text-xs">{{ $line['reference'] ?? '—' }}</td>
                        <td class="px-3 py-2">{{ $line['description'] }}</td>
                        <td class="px-3 py-2 text-right font-mono">{{ ($line['debit'] ?? 0) > 0 ? number_format($line['debit'], 2) : '—' }}</td>
                        <td class="px-3 py-2 text-right font-mono">{{ ($line['credit'] ?? 0) > 0 ? number_format($line['credit'], 2) : '—' }}</td>
                        <td class="px-3 py-2 text-right font-mono">{{ number_format($line['balance'] ?? 0, 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-3 py-8 text-center text-slate-500">{{ __('No transactions in this period.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-admin.card>
