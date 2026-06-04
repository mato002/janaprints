<section class="exec-panel">
    <div class="exec-panel__head"><h2 class="exec-panel__title">{{ __('Branch Performance') }}</h2></div>
    <div class="exec-table-scroll">
        <table class="exec-table">
            <thead>
                <tr>
                    <th>{{ __('Branch') }}</th>
                    <th class="text-right">{{ __('Sales') }}</th>
                    <th class="text-right">{{ __('Jobs') }}</th>
                    <th class="text-right">{{ __('Receivables') }}</th>
                    <th class="text-right">{{ __('Profit') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($dashboard['branches'] as $row)
                    <tr @class(['exec-table__top' => ! empty($row['top'])])>
                        <td class="font-medium text-erp-primary">
                            {{ $row['name'] }}
                            @if (! empty($row['top']))
                                <span class="exec-tag">{{ __('Top') }}</span>
                            @endif
                        </td>
                        <td class="text-right tabular-nums">{{ $row['sales'] }}</td>
                        <td class="text-right tabular-nums">{{ $row['jobs'] }}</td>
                        <td class="text-right tabular-nums text-slate-500">{{ $row['receivables'] }}</td>
                        <td class="text-right tabular-nums text-slate-500">{{ $row['profit'] }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="py-4 text-center text-xs text-slate-500">{{ __('No branch data for current scope.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
