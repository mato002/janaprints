<x-admin-layout :title="__('Close POS Session')">
    <x-admin.page-header :title="__('Close POS Session')" :description="$session->session_number" />

    <div class="mb-6 grid grid-cols-2 gap-3 lg:grid-cols-4 xl:grid-cols-6">
        <x-admin.kpi-widget :label="__('Sales count')" :value="$metrics['sales_count']" icon="clipboard-list" />
        <x-admin.kpi-widget :label="__('Expected cash')" :value="number_format($expectedCash, 2)" icon="cash" />
        <x-admin.kpi-widget :label="__('Expected M-Pesa')" :value="number_format($metrics['expected_mpesa'], 2)" icon="device-mobile" />
        <x-admin.kpi-widget :label="__('Expected card')" :value="number_format($metrics['expected_card'], 2)" icon="credit-card" />
        <x-admin.kpi-widget :label="__('Expected bank')" :value="number_format($metrics['expected_bank'], 2)" icon="library" />
        <x-admin.kpi-widget :label="__('Expected total')" :value="number_format($metrics['expected_total'], 2)" icon="currency-dollar" />
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        @include('admin.commercial.pos.sessions.partials.closure-checklist', [
            'governance' => $governance,
            'showCashCountHint' => true,
        ])

        <x-admin.card class="max-w-xl">
            <p class="mb-3 text-xs text-slate-500">
                {{ __('Variance tolerance: :amount. Exceeding this requires manager approval.', ['amount' => number_format($varianceTolerance, 2)]) }}
            </p>
            <form method="POST" action="{{ route('admin.commercial.pos.sessions.close.store', $session) }}" class="space-y-4" x-data="{ expected: {{ $expectedCash }}, actual: {{ old('actual_cash', $expectedCash) }} }">
                @csrf
                <div>
                    <label class="text-[11px] text-slate-500" for="actual_cash">{{ __('Actual cash count') }}</label>
                    <input type="number" step="0.01" min="0" id="actual_cash" name="actual_cash" x-model.number="actual" class="erp-input mt-1 w-full" required @disabled(! $governance['can_close'])>
                </div>
                <div class="rounded-lg border border-erp-border bg-slate-50 px-3 py-2 text-sm">
                    <div class="flex justify-between"><span>{{ __('Variance') }}</span><span class="font-semibold tabular-nums" x-text="(actual - expected).toFixed(2)"></span></div>
                </div>
                <div>
                    <label class="text-[11px] text-slate-500" for="closing_notes">{{ __('Closing notes') }}</label>
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
