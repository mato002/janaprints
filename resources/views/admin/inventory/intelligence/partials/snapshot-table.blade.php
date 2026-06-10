<x-admin.card>
    <h3 class="mb-3 text-sm font-semibold text-slate-900">{{ $title }}</h3>
    <div class="overflow-x-auto">
        <table class="erp-table w-full text-sm">
            <thead>
                <tr>
                    <th>{{ __('Item') }}</th>
                    <th>{{ __('Warehouse') }}</th>
                    <th>{{ __('Balance') }}</th>
                    <th>{{ __('Daily consumption') }}</th>
                    <th>{{ __('Days to depletion') }}</th>
                    <th>{{ __('Risk') }}</th>
                    <th>{{ __('Class') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($snapshots as $snapshot)
                    <tr>
                        <td>
                            <div class="font-medium">{{ $snapshot->inventoryItem?->item_name }}</div>
                            <div class="font-mono text-[11px] text-slate-500">{{ $snapshot->inventoryItem?->sku }}</div>
                        </td>
                        <td>{{ $snapshot->warehouse?->name ?? '—' }}</td>
                        <td class="tabular-nums">{{ number_format((float) $snapshot->closing_balance, 3) }}</td>
                        <td class="tabular-nums">{{ number_format((float) $snapshot->average_daily_consumption, 4) }}</td>
                        <td class="tabular-nums">{{ $snapshot->days_to_depletion !== null ? number_format((float) $snapshot->days_to_depletion, 1) : '—' }}</td>
                        <td>{{ $snapshot->risk_level?->label() }}</td>
                        <td>{{ $snapshot->velocity_class?->label() }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-slate-500">{{ $empty }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-admin.card>
