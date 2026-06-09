<div class="overflow-x-auto">
    <table class="min-w-full text-sm">
        <thead class="bg-slate-50">
            <tr>
                <th class="px-3 py-2 text-left">{{ __('Payment') }}</th>
                <th class="px-3 py-2 text-left">{{ __('Date') }}</th>
                <th class="px-3 py-2 text-right">{{ __('Amount') }}</th>
                <th class="px-3 py-2 text-right">{{ __('Allocated') }}</th>
                <th class="px-3 py-2 text-left">{{ __('Status') }}</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse ($payments as $payment)
                <tr>
                    <td class="px-3 py-2"><a href="{{ route('admin.payments.show', $payment) }}" class="font-mono text-erp-accent">{{ $payment->payment_number }}</a></td>
                    <td class="px-3 py-2">{{ $payment->payment_date->format('M j, Y') }}</td>
                    <td class="px-3 py-2 text-right font-mono">{{ number_format($payment->amount, 2) }}</td>
                    <td class="px-3 py-2 text-right font-mono">{{ number_format($payment->allocated_amount, 2) }}</td>
                    <td class="px-3 py-2"><x-admin.status-badge :variant="$payment->status->value === 'posted' ? 'success' : 'neutral'">{{ $payment->status->label() }}</x-admin.status-badge></td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-3 py-6 text-center text-slate-500">{{ __('No payments') }}</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@if (empty($compact) && method_exists($payments, 'links'))
    <div class="mt-4"><x-admin.table-pagination :paginator="$payments" /></div>
@endif
