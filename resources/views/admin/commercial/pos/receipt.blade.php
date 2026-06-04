<x-admin-layout :title="__('Receipt :number', ['number' => $sale->sale_number])">
    <x-admin.card class="mx-auto max-w-md print:shadow-none" id="pos-receipt">
        <div class="text-center border-b border-erp-border pb-4 mb-4">
            <h2 class="text-lg font-semibold">{{ $sale->branch?->name ?? config('app.name') }}</h2>
            <p class="text-sm text-slate-500">{{ __('POS Receipt') }}</p>
            <p class="font-mono text-xs mt-1">{{ $sale->sale_number }}</p>
            <p class="text-xs text-slate-400">{{ $sale->sale_date->format('Y-m-d H:i') }}</p>
        </div>
        <p class="text-sm mb-4">{{ __('Customer') }}: {{ $sale->is_walk_in ? __('Walk-in') : ($sale->customer?->company_name ?? '—') }}</p>
        <table class="w-full text-sm mb-4">
            @foreach ($sale->items as $item)
                <tr>
                    <td class="py-1">{{ $item->description }}</td>
                    <td class="py-1 text-right tabular-nums">{{ number_format($item->line_total, 2) }}</td>
                </tr>
            @endforeach
        </table>
        <div class="border-t border-erp-border pt-3 space-y-1 text-sm">
            <div class="flex justify-between"><span>{{ __('Subtotal') }}</span><span>{{ number_format($sale->subtotal, 2) }}</span></div>
            <div class="flex justify-between"><span>{{ __('Discount') }}</span><span>{{ number_format($sale->discount_amount, 2) }}</span></div>
            <div class="flex justify-between"><span>{{ __('Tax') }}</span><span>{{ number_format($sale->tax_amount, 2) }}</span></div>
            <div class="flex justify-between font-bold text-base"><span>{{ __('Total') }}</span><span>{{ number_format($sale->total_amount, 2) }}</span></div>
            <div class="flex justify-between"><span>{{ __('Paid') }}</span><span>{{ number_format($sale->amount_paid, 2) }}</span></div>
        </div>
        @foreach ($sale->payments as $payment)
            <p class="text-xs text-slate-500 mt-2">{{ ucfirst($payment->payment_method->value) }} — {{ number_format($payment->amount, 2) }}</p>
        @endforeach
        <p class="mt-6 text-center text-xs text-slate-400">{{ __('Thank you for your business.') }}</p>
    </x-admin.card>
    <div class="mt-4 text-center print:hidden">
        <button type="button" onclick="window.print()" class="erp-btn-primary">{{ __('Print receipt') }}</button>
        <a href="{{ route('admin.commercial.pos.dashboard') }}" class="erp-btn-secondary ml-2">{{ __('Back to POS') }}</a>
    </div>
</x-admin-layout>
