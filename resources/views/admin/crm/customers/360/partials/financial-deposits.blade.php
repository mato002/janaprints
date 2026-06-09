<div class="mb-4 grid grid-cols-3 gap-3">
    <x-admin.kpi-widget :label="__('Credit issued')" :value="number_format($wallet['available_credit'] ?? 0, 2)" icon="currency-dollar" />
    <x-admin.kpi-widget :label="__('Credit applied')" :value="number_format($wallet['used_credit'] ?? 0, 2)" icon="scale" />
    <x-admin.kpi-widget :label="__('Credit remaining')" :value="number_format($wallet['remaining_credit'] ?? 0, 2)" icon="cash" />
</div>

<div class="overflow-x-auto">
    <table class="min-w-full text-sm">
        <thead class="bg-slate-50">
            <tr>
                <th class="px-3 py-2 text-left">{{ __('Payment') }}</th>
                <th class="px-3 py-2 text-left">{{ __('Date') }}</th>
                <th class="px-3 py-2 text-right">{{ __('Issued') }}</th>
                <th class="px-3 py-2 text-right">{{ __('Applied') }}</th>
                <th class="px-3 py-2 text-right">{{ __('Remaining') }}</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse ($deposits as $deposit)
                <tr>
                    <td class="px-3 py-2"><a href="{{ route('admin.payments.show', $deposit['payment_id']) }}" class="font-mono text-erp-accent">{{ $deposit['payment_number'] }}</a></td>
                    <td class="px-3 py-2">{{ $deposit['payment_date'] }}</td>
                    <td class="px-3 py-2 text-right font-mono">{{ number_format($deposit['credit_issued'], 2) }}</td>
                    <td class="px-3 py-2 text-right font-mono">{{ number_format($deposit['credit_applied'], 2) }}</td>
                    <td class="px-3 py-2 text-right font-mono">{{ number_format($deposit['credit_remaining'], 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-3 py-6 text-center text-slate-500">{{ __('No deposit credits') }}</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
