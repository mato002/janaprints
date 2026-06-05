<x-admin-layout :title="__('Close POS Session')">
    <x-admin.page-header :title="__('Close POS Session')" :description="$session->session_number" />

    <div class="mb-6 grid grid-cols-2 gap-3 lg:grid-cols-4">
        <x-admin.kpi-widget :label="__('Sales Count')" :value="$metrics['sales_count']" icon="clipboard-list" />
        <x-admin.kpi-widget :label="__('Cash Sales')" :value="number_format($metrics['cash_sales'], 2)" icon="cash" />
        <x-admin.kpi-widget :label="__('M-Pesa Sales')" :value="number_format($metrics['mpesa_sales'], 2)" icon="device-mobile" />
        <x-admin.kpi-widget :label="__('Expected Closing Cash')" :value="number_format($expectedCash, 2)" icon="currency-dollar" />
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        @include('admin.commercial.pos.sessions.partials.closure-checklist', [
            'governance' => $governance,
            'showCashCountHint' => true,
        ])

        <x-admin.card class="max-w-xl">
            <form method="POST" action="{{ route('admin.commercial.pos.sessions.close.store', $session) }}" class="space-y-4">
                @csrf
                <div>
                    <label class="text-[11px] text-slate-500" for="actual_cash">{{ __('Actual Closing Cash') }}</label>
                    <input type="number" step="0.01" min="0" id="actual_cash" name="actual_cash" value="{{ old('actual_cash', $expectedCash) }}" class="erp-input mt-1 w-full" required @disabled(! $governance['can_close'])>
                </div>
                <div>
                    <label class="text-[11px] text-slate-500" for="closing_notes">{{ __('Closing Notes') }}</label>
                    <textarea id="closing_notes" name="closing_notes" rows="3" class="erp-input mt-1 w-full" @disabled(! $governance['can_close'])>{{ old('closing_notes') }}</textarea>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="erp-btn-primary" @disabled(! $governance['can_close'])>{{ __('Close session') }}</button>
                    <a href="{{ route('admin.commercial.pos.sessions.show', $session) }}" class="erp-btn-secondary">{{ __('Cancel') }}</a>
                </div>
            </form>
        </x-admin.card>
    </div>
</x-admin-layout>
