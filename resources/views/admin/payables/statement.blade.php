<x-admin-layout :title="__('Supplier statement')">
    <x-admin.page-header :title="__('Supplier statement')" />

    <x-admin.card :padding="false" class="mb-4">
        <x-admin.index-toolbar :action="route('admin.payables.statement')" :reset-url="route('admin.payables.statement')">
            <select name="vendor_id" class="erp-toolbar-select min-w-[12rem]" aria-label="{{ __('Supplier') }}" required>
                @foreach ($vendors as $v)
                    <option value="{{ $v->id }}" @selected(request('vendor_id') == $v->id)>{{ $v->vendor_name }}</option>
                @endforeach
            </select>
            <input type="date" name="from_date" value="{{ request('from_date') }}" class="erp-toolbar-input" aria-label="{{ __('From date') }}" required>
            <input type="date" name="to_date" value="{{ request('to_date') }}" class="erp-toolbar-input" aria-label="{{ __('To date') }}" required>

            <x-slot name="export">
                @include('admin.accounting.partials.listing-export-dropdown', [
                    'listing' => 'ap-statement',
                    'exportQuery' => request()->query(),
                ])
            </x-slot>
        </x-admin.index-toolbar>
    </x-admin.card>

    @if ($report)
        <x-admin.card>
            <p class="font-medium mb-2">{{ $report['vendor']->vendor_name }}</p>
            <p class="text-sm">{{ __('Opening') }}: {{ number_format($report['opening_balance'], 2) }} · {{ __('Closing') }}: {{ number_format($report['closing_balance'], 2) }}</p>
        </x-admin.card>
    @endif
</x-admin-layout>
