<x-admin.card>
    <h3 class="mb-3 text-sm font-semibold text-slate-900">{{ $title }}</h3>
    <div class="overflow-x-auto">
        <table class="erp-table w-full text-sm">
            <thead>
                <tr>
                    <th>{{ __('Item') }}</th>
                    <th>{{ __('Warehouse') }}</th>
                    <th>{{ __('Balance') }}</th>
                    <th>{{ __('Est. value') }}</th>
                    <th>{{ __('Days inactive') }}</th>
                    <th>{{ __('Suggested action') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $row)
                    <tr>
                        <td>
                            <div class="font-medium">{{ $row['item']->item_name }}</div>
                            <div class="font-mono text-[11px] text-slate-500">{{ $row['item']->sku }}</div>
                        </td>
                        <td>{{ $row['warehouse']?->name ?? '—' }}</td>
                        <td class="tabular-nums">{{ number_format($row['balance'], 3) }}</td>
                        <td class="tabular-nums">{{ number_format($row['estimated_value'], 2) }}</td>
                        <td class="tabular-nums">{{ $row['days_inactive'] }}</td>
                        <td>{{ $row['suggested_action']->label() }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-slate-500">{{ $empty }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-admin.card>
