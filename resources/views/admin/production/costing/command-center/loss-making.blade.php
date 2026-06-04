<x-admin.card :padding="false">
    <div class="border-b border-erp-border px-4 py-3">
        <h2 class="text-sm font-semibold uppercase tracking-wide text-red-700">{{ __('Loss-Making Jobs') }}</h2>
        <p class="mt-0.5 text-xs text-slate-500">{{ __('Top 10 worst losses — requires immediate review') }}</p>
    </div>
    <div class="overflow-x-auto">
        <table class="erp-table w-full text-sm">
            <thead>
                <tr>
                    <th>{{ __('Job Card') }}</th>
                    <th>{{ __('Customer') }}</th>
                    <th class="text-right">{{ __('Revenue') }}</th>
                    <th class="text-right">{{ __('Cost') }}</th>
                    <th class="text-right">{{ __('Loss') }}</th>
                    <th>{{ __('Margin %') }}</th>
                    <th>{{ __('Likely Reason') }}</th>
                    <th class="erp-table-actions-col">{{ __('Action') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($dashboard['loss_making'] as $row)
                    <tr class="bg-red-50/30">
                        <td class="font-mono font-medium">{{ $row['job_card_number'] }}</td>
                        <td>{{ $row['customer_name'] }}</td>
                        <td class="text-right tabular-nums">KES {{ number_format($row['revenue'], 0) }}</td>
                        <td class="text-right tabular-nums">KES {{ number_format($row['cost'], 0) }}</td>
                        <td class="text-right tabular-nums font-medium text-red-700">KES {{ number_format($row['loss'], 0) }}</td>
                        <td>
                            <x-admin.status-badge variant="danger">{{ number_format($row['margin_percent'], 1) }}%</x-admin.status-badge>
                        </td>
                        <td class="text-xs text-slate-600">{{ $row['likely_reason'] }}</td>
                        <td class="erp-table-actions-col">
                            @if ($row['job_360_url'])
                                <a href="{{ $row['job_360_url'] }}" class="text-xs font-medium text-erp-accent hover:underline" data-turbo-frame="erp-main">{{ __('Open Job 360') }}</a>
                            @elseif ($row['costing_url'])
                                <a href="{{ $row['costing_url'] }}" class="text-xs font-medium text-erp-accent hover:underline" data-turbo-frame="erp-main">{{ __('View costing') }}</a>
                            @else
                                <span class="text-xs text-slate-400">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="py-8 text-center text-slate-500">{{ __('No loss-making jobs found.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-admin.card>
