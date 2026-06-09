<x-admin-layout :title="__('Supplier ledger')">
    <x-admin.page-header :title="__('Supplier ledger')" />

    <x-admin.card :padding="false" class="mb-4">
        <x-admin.index-toolbar :action="route('admin.payables.ledger')" :reset-url="route('admin.payables.ledger')">
            <select name="vendor_id" class="erp-toolbar-select min-w-[12rem]" aria-label="{{ __('Supplier') }}" required>
                @foreach ($vendors as $v)
                    <option value="{{ $v->id }}" @selected($vendorId == $v->id)>{{ $v->vendor_name }}</option>
                @endforeach
            </select>
        </x-admin.index-toolbar>
    </x-admin.card>

    @if ($report)
        <x-admin.card>
            <p class="text-sm mb-2">{{ __('Closing balance') }}: <strong class="font-mono">{{ number_format($report['closing_balance'], 2) }}</strong></p>
            <table class="w-full text-sm">
                @foreach ($report['entries'] as $entry)
                    <tr class="border-t border-erp-border">
                        <td class="py-1">{{ $entry->date }}</td>
                        <td>{{ $entry->reference }}</td>
                        <td class="font-mono text-right">{{ number_format($entry->running_balance, 2) }}</td>
                    </tr>
                @endforeach
            </table>
        </x-admin.card>
    @endif
</x-admin-layout>
