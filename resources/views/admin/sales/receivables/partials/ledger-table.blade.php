<table class="w-full text-sm">
    <thead><tr class="text-left text-[11px] uppercase text-slate-400"><th>{{ __('Date') }}</th><th>{{ __('Reference') }}</th><th>{{ __('Description') }}</th><th>{{ __('Debit') }}</th><th>{{ __('Credit') }}</th><th>{{ __('Balance') }}</th></tr></thead>
    <tbody>
        @foreach ($entries as $entry)
            <tr class="border-t border-erp-border">
                <td class="py-2">{{ $entry->date }}</td>
                <td class="font-mono">{{ $entry->reference }}</td>
                <td>{{ $entry->description }}</td>
                <td class="font-mono">{{ $entry->debit > 0 ? number_format($entry->debit, 2) : '—' }}</td>
                <td class="font-mono">{{ $entry->credit > 0 ? number_format($entry->credit, 2) : '—' }}</td>
                <td class="font-mono">{{ number_format($entry->balance, 2) }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
