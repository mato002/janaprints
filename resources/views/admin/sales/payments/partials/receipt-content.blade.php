<div class="text-center border-b border-erp-border pb-4 mb-4">
    <h2 class="text-lg font-semibold">{{ $receipt['company_name'] }}</h2>
    @if ($receipt['branch_name'])
        <p class="text-sm text-slate-500">{{ $receipt['branch_name'] }}</p>
    @endif
    <p class="text-sm text-slate-500 mt-2">{{ __('Payment Receipt') }}</p>
    <p class="font-mono text-sm mt-1">{{ __('Receipt') }}: {{ $receipt['receipt_number'] }}</p>
    <p class="text-xs text-slate-400">{{ $receipt['payment_date'] }}</p>
</div>

<dl class="text-sm space-y-2 mb-4">
    <div class="flex justify-between gap-4"><dt class="text-slate-500">{{ __('Customer') }}</dt><dd class="font-medium text-right">{{ $receipt['customer_name'] }}</dd></div>
    @if ($receipt['customer_code'])
        <div class="flex justify-between gap-4"><dt class="text-slate-500">{{ __('Customer code') }}</dt><dd class="font-mono text-right">{{ $receipt['customer_code'] }}</dd></div>
    @endif
    <div class="flex justify-between gap-4"><dt class="text-slate-500">{{ __('Payment method') }}</dt><dd class="text-right">{{ $receipt['payment_method'] }}</dd></div>
    @if ($receipt['reference'])
        <div class="flex justify-between gap-4"><dt class="text-slate-500">{{ __('Reference') }}</dt><dd class="font-mono text-right">{{ $receipt['reference'] }}</dd></div>
    @endif
</dl>

@if (! empty($receipt['invoices_settled']))
    <h3 class="font-medium text-sm mb-2">{{ __('Invoices settled') }}</h3>
    <table class="w-full text-sm mb-4">
        <thead>
            <tr class="text-xs text-slate-500">
                <th class="py-1 text-left">{{ __('Invoice') }}</th>
                <th class="py-1 text-right">{{ __('Applied') }}</th>
                <th class="py-1 text-right">{{ __('Balance') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($receipt['invoices_settled'] as $row)
                <tr>
                    <td class="py-1 font-mono">{{ $row['invoice_number'] }}</td>
                    <td class="py-1 text-right font-mono">{{ number_format($row['amount_applied'], 2) }}</td>
                    <td class="py-1 text-right font-mono">{{ number_format($row['balance_remaining'], 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif

<div class="border-t border-erp-border pt-3 space-y-1 text-sm">
    <div class="flex justify-between"><span>{{ __('Amount received') }}</span><span class="font-mono font-semibold">{{ number_format($receipt['amount'], 2) }} {{ $receipt['currency'] }}</span></div>
    @if ($receipt['is_deposit'])
        <div class="flex justify-between text-slate-600"><span>{{ __('Unallocated deposit') }}</span><span class="font-mono">{{ number_format($receipt['unallocated_amount'], 2) }}</span></div>
    @endif
    <div class="flex justify-between font-bold text-base border-t border-erp-border pt-2 mt-2">
        <span>{{ __('Customer balance remaining') }}</span>
        <span class="font-mono">{{ number_format($receipt['balance_remaining'], 2) }} {{ $receipt['currency'] }}</span>
    </div>
</div>

<p class="mt-6 text-center text-xs text-slate-400">{{ __('Thank you for your payment.') }}</p>
