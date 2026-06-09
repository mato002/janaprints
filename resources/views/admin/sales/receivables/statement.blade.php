<x-admin-layout :title="__('Customer statement')">
    <x-admin.page-header :title="__('Customer statement')" />

    <x-admin.card :padding="false" class="mb-4">
        <x-admin.index-toolbar :action="route('admin.sales.receivables.statement')" :reset-url="route('admin.sales.receivables.statement')">
            <select name="customer_id" class="erp-toolbar-select min-w-[12rem]" aria-label="{{ __('Customer') }}" required>
                @foreach ($customers as $c)
                    <option value="{{ $c->id }}" @selected(request('customer_id') == $c->id)>{{ $c->company_name }}</option>
                @endforeach
            </select>
            <input type="date" name="from_date" value="{{ request('from_date', now()->startOfMonth()->toDateString()) }}" class="erp-toolbar-input" aria-label="{{ __('From date') }}" required>
            <input type="date" name="to_date" value="{{ request('to_date', now()->toDateString()) }}" class="erp-toolbar-input" aria-label="{{ __('To date') }}" required>
        </x-admin.index-toolbar>
    </x-admin.card>

    @if ($report)
        <x-admin.card>
            <h2 class="font-semibold mb-1">{{ $report['customer']->company_name }}</h2>
            <p class="text-sm text-slate-500 mb-4">{{ $report['from_date'] }} — {{ $report['to_date'] }}</p>
            <p class="text-sm mb-4">{{ __('Opening balance') }}: <strong>{{ number_format($report['opening_balance'], 2) }}</strong> · {{ __('Closing') }}: <strong>{{ number_format($report['closing_balance'], 2) }}</strong></p>
            @include('admin.sales.receivables.partials.ledger-table', ['entries' => $report['entries']])
        </x-admin.card>
    @endif
</x-admin-layout>
