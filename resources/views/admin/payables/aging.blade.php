<x-admin-layout :title="__('AP aging')">
    <x-admin.page-header :title="__('Accounts payable aging')" :description="__('As of :date', ['date' => $report['as_of_date']])" />

    <x-admin.card :padding="false" class="mb-4">
        <x-admin.index-toolbar :action="route('admin.payables.aging')" :reset-url="route('admin.payables.aging')">
            <input type="date" name="as_of_date" value="{{ $report['as_of_date'] }}" class="erp-toolbar-input" aria-label="{{ __('As of date') }}">
        </x-admin.index-toolbar>
    </x-admin.card>

    <x-admin.card>
        <table class="w-full text-sm">
            <thead><tr class="text-[11px] uppercase text-slate-400"><th>{{ __('Supplier') }}</th><th>{{ __('Total') }}</th></tr></thead>
            <tbody>
                @foreach ($report['rows'] as $row)
                    <tr class="border-t border-erp-border"><td class="py-2">{{ $row['vendor_name'] }}</td><td class="font-mono">{{ number_format($row['total'], 2) }}</td></tr>
                @endforeach
            </tbody>
            <tfoot><tr><td class="font-medium pt-2">{{ __('Grand total') }}</td><td class="font-mono font-medium">{{ number_format($report['grand_total'], 2) }}</td></tr></tfoot>
        </table>
    </x-admin.card>
</x-admin-layout>
