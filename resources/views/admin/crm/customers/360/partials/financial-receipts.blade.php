<div class="overflow-x-auto">
    <table class="min-w-full text-sm">
        <thead class="bg-slate-50">
            <tr>
                <th class="px-3 py-2 text-left">{{ __('Receipt') }}</th>
                <th class="px-3 py-2 text-left">{{ __('Payment') }}</th>
                <th class="px-3 py-2 text-left">{{ __('Date') }}</th>
                <th class="px-3 py-2 text-right">{{ __('Amount') }}</th>
                <th class="px-3 py-2 text-left">{{ __('Actions') }}</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse ($receipts as $payment)
                <tr>
                    <td class="px-3 py-2 font-mono">{{ $payment->receipt_number }}</td>
                    <td class="px-3 py-2"><a href="{{ route('admin.payments.show', $payment) }}" class="font-mono text-erp-accent">{{ $payment->payment_number }}</a></td>
                    <td class="px-3 py-2">{{ $payment->payment_date->format('M j, Y') }}</td>
                    <td class="px-3 py-2 text-right font-mono">{{ number_format($payment->amount, 2) }}</td>
                    <td class="px-3 py-2">
                        @can('viewReceipt', $payment)
                            <a href="{{ route('admin.payments.receipt', $payment) }}" class="text-erp-accent text-xs">{{ __('View receipt') }}</a>
                        @endcan
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-3 py-6 text-center text-slate-500">{{ __('No receipts issued') }}</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@if (method_exists($receipts, 'links'))
    <div class="mt-4"><x-admin.table-pagination :paginator="$receipts" /></div>
@endif
