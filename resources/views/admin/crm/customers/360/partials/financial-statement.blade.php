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
    <button type="submit" class="erp-btn-secondary">{{ __('Generate') }}</button>
</form>

<x-admin.card>
    <div class="mb-4 flex flex-wrap justify-between gap-2 text-sm">
        <div>
            <p class="font-semibold">{{ $statement['customer']->company_name }}</p>
            <p class="text-slate-500">{{ $from }} — {{ $to }}</p>
        </div>
        <div class="text-right">
            <p>{{ __('Opening') }}: <span class="font-mono">{{ number_format($statement['opening_balance'] ?? 0, 2) }}</span></p>
            <p>{{ __('Closing') }}: <span class="font-mono font-semibold">{{ number_format($statement['closing_balance'] ?? 0, 2) }}</span></p>
        </div>
    </div>

    @include('admin.sales.receivables.partials.ledger-table', ['entries' => $statement['entries']])
</x-admin.card>
