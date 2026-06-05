<x-admin.card class="mx-auto max-w-2xl print:shadow-none" id="pos-session-summary">
    <div class="text-center border-b border-erp-border pb-4 mb-4">
        <h2 class="text-lg font-semibold">{{ __('Session summary') }}</h2>
        <p class="font-mono text-sm mt-1">{{ $session->session_number }}</p>
        <p class="text-xs text-slate-500">{{ $session->branch?->name }}</p>
    </div>

    <dl class="grid grid-cols-2 gap-3 text-sm mb-6">
        <div><dt class="text-slate-500">{{ __('Cashier') }}</dt><dd class="font-medium">{{ $session->cashier?->name }}</dd></div>
        <div><dt class="text-slate-500">{{ __('Terminal') }}</dt><dd class="font-medium">{{ $session->terminal ?? '—' }}</dd></div>
        <div><dt class="text-slate-500">{{ __('Opened') }}</dt><dd>{{ $session->opened_at?->format('Y-m-d H:i') }}</dd></div>
        <div><dt class="text-slate-500">{{ __('Closed') }}</dt><dd>{{ $session->closed_at?->format('Y-m-d H:i') ?? '—' }}</dd></div>
        <div><dt class="text-slate-500">{{ __('Opening float') }}</dt><dd class="tabular-nums">{{ number_format($session->opening_float, 2) }}</dd></div>
        <div><dt class="text-slate-500">{{ __('Status') }}</dt><dd class="font-medium">{{ ucfirst(str_replace('_', ' ', $session->status->value)) }}</dd></div>
    </dl>

    <h3 class="text-sm font-semibold mb-2">{{ __('Sales summary') }}</h3>
    <div class="grid grid-cols-2 gap-2 text-sm mb-6">
        <div class="flex justify-between border-b border-erp-border/60 py-1"><span>{{ __('Transactions') }}</span><span class="tabular-nums">{{ $metrics['transactions_count'] }}</span></div>
        <div class="flex justify-between border-b border-erp-border/60 py-1"><span>{{ __('Paid sales') }}</span><span class="tabular-nums">{{ $metrics['sales_count'] }}</span></div>
        <div class="flex justify-between border-b border-erp-border/60 py-1"><span>{{ __('Held sales') }}</span><span class="tabular-nums">{{ $metrics['held_sales'] }}</span></div>
        <div class="flex justify-between border-b border-erp-border/60 py-1"><span>{{ __('Cancelled sales') }}</span><span class="tabular-nums">{{ $metrics['cancelled_sales'] }}</span></div>
        <div class="flex justify-between border-b border-erp-border/60 py-1"><span>{{ __('Total sales value') }}</span><span class="tabular-nums font-medium">{{ number_format($metrics['total_sales_value'], 2) }}</span></div>
        <div class="flex justify-between border-b border-erp-border/60 py-1"><span>{{ __('Refunds') }}</span><span class="tabular-nums">{{ $metrics['refunds'] }}</span></div>
    </div>

    <h3 class="text-sm font-semibold mb-2">{{ __('Payment summary') }}</h3>
    <div class="space-y-1 text-sm mb-6">
        <div class="flex justify-between"><span>{{ __('Cash') }}</span><span class="tabular-nums">{{ number_format($metrics['cash_sales'], 2) }}</span></div>
        <div class="flex justify-between"><span>{{ __('M-Pesa') }}</span><span class="tabular-nums">{{ number_format($metrics['mpesa_sales'], 2) }}</span></div>
        <div class="flex justify-between"><span>{{ __('Card') }}</span><span class="tabular-nums">{{ number_format($metrics['card_sales'], 2) }}</span></div>
        <div class="flex justify-between"><span>{{ __('Bank') }}</span><span class="tabular-nums">{{ number_format($metrics['bank_sales'], 2) }}</span></div>
        <div class="flex justify-between font-semibold border-t border-erp-border pt-2"><span>{{ __('Expected total') }}</span><span class="tabular-nums">{{ number_format($metrics['expected_total'], 2) }}</span></div>
    </div>

    <h3 class="text-sm font-semibold mb-2">{{ __('Cash reconciliation') }}</h3>
    <div class="space-y-1 text-sm">
        <div class="flex justify-between"><span>{{ __('Expected cash') }}</span><span class="tabular-nums">{{ number_format($session->expected_cash ?? $metrics['expected_closing_cash'], 2) }}</span></div>
        <div class="flex justify-between"><span>{{ __('Actual cash') }}</span><span class="tabular-nums">{{ $session->actual_cash !== null ? number_format($session->actual_cash, 2) : '—' }}</span></div>
        <div class="flex justify-between font-semibold"><span>{{ __('Variance') }}</span><span class="tabular-nums">{{ $session->variance !== null ? number_format($session->variance, 2) : '—' }}</span></div>
        <p class="text-xs text-slate-500 pt-1">{{ __('Tolerance: :amount', ['amount' => number_format($varianceTolerance, 2)]) }}</p>
    </div>

    @if ($session->variance_requires_approval)
        <div class="mt-4 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-900">
            @if ($session->varianceApprover)
                {{ __('Approved by :name on :date', ['name' => $session->varianceApprover->name, 'date' => $session->variance_approved_at?->format('Y-m-d H:i')]) }}
            @else
                {{ __('Pending manager approval.') }}
            @endif
        </div>
    @endif
</x-admin.card>
